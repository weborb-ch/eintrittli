<?php

namespace App\Filament\Exports;

use App\Enums\ExportFormat;
use App\Enums\FormFieldType;
use App\Models\Form;
use App\Models\FormField;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Builds and streams registration exports in the format and shape the user picked.
 */
class RegistrationExporter
{
    private const CHUNK_SIZE = 500;

    /**
     * Every column the user may pick, keyed by its column key.
     *
     * @param  list<int>|null  $formIds  Limits the form groups; null offers every form.
     * @return Collection<string, RegistrationExportColumn>
     */
    public static function resolveColumns(?array $formIds = null): Collection
    {
        return self::flattenColumnGroups(self::resolveColumnGroups($formIds));
    }

    /**
     * The pickable columns, split into the general registration columns and one group per form.
     *
     * The form groups follow the table's event and form filters, not the registrations that happen
     * to exist, so a form without any registrations still gets its group. A form whose fields are
     * all description blocks gets an empty group rather than being dropped. A field name shared by
     * several forms is listed in each of their groups but still maps to a single export column.
     *
     * @param  list<int>|null  $formIds  Limits the form groups; null offers every form.
     * @return Collection<string, RegistrationExportColumnGroup>
     */
    public static function resolveColumnGroups(?array $formIds = null): Collection
    {
        /** @var Collection<string, RegistrationExportColumn> $generalColumns */
        $generalColumns = collect([
            RegistrationExportColumn::make(
                'confirmation_code',
                __('Confirmation code'),
                fn (Registration $registration): string => $registration->confirmation_code,
            ),
            RegistrationExportColumn::make(
                'event',
                __('Event'),
                fn (Registration $registration): string => $registration->event->name,
            ),
            RegistrationExportColumn::make(
                'form',
                __('Form'),
                fn (Registration $registration): string => $registration->event->form->name ?? '',
            ),
            RegistrationExportColumn::make(
                'created_at',
                __('Registered At'),
                fn (Registration $registration): string => $registration->created_at->format('d.m.Y H:i:s'),
            ),
            RegistrationExportColumn::make(
                'notes',
                __('Notes'),
                fn (Registration $registration): string => $registration->notes ?? '',
            ),
        ])->keyBy(fn (RegistrationExportColumn $column): string => $column->key);

        /** @var Collection<string, RegistrationExportColumnGroup> $groups */
        $groups = collect([
            RegistrationExportColumnGroup::GeneralKey => new RegistrationExportColumnGroup(
                RegistrationExportColumnGroup::GeneralKey,
                __('General'),
                $generalColumns,
            ),
        ]);

        $formGroups = self::resolveForms($formIds)
            ->map(function (Form $form): RegistrationExportColumnGroup {
                /** @var Collection<string, RegistrationExportColumn> $columns */
                $columns = $form->fields
                    ->mapWithKeys(function (FormField $field): array {
                        $column = RegistrationExportColumn::formField($field);

                        return [$column->key => $column];
                    });

                return new RegistrationExportColumnGroup(
                    'form_'.$form->getKey(),
                    $form->name,
                    $columns,
                );
            })
            ->sortBy(fn (RegistrationExportColumnGroup $group): string => mb_strtolower($group->label))
            ->keyBy(fn (RegistrationExportColumnGroup $group): string => $group->key);

        return $groups->merge($formGroups);
    }

    /**
     * Collapses the groups into the ordered, de-duplicated columns that end up in the file.
     *
     * @param  Collection<string, RegistrationExportColumnGroup>  $groups
     * @return Collection<string, RegistrationExportColumn>
     */
    public static function flattenColumnGroups(Collection $groups): Collection
    {
        /** @var Collection<string, RegistrationExportColumn> $columns */
        $columns = collect();

        foreach ($groups as $group) {
            foreach ($group->columns as $key => $column) {
                if ($columns->has($key)) {
                    continue;
                }

                $columns->put($key, $column);
            }
        }

        return $columns;
    }

    /**
     * Forms in scope, each carrying only the fields that can hold an answer.
     *
     * @param  list<int>|null  $formIds
     * @return EloquentCollection<int, Form>
     */
    private static function resolveForms(?array $formIds): EloquentCollection
    {
        return Form::query()
            ->when($formIds !== null, fn (Builder $query): Builder => $query->whereKey($formIds))
            ->with(['fields' => function (Relation $fields): void {
                $fields
                    ->where('type', '!=', FormFieldType::Description->value)
                    ->whereNotNull('name');
            }])
            ->get();
    }

    /**
     * @param  Collection<string, RegistrationExportColumn>  $columns
     */
    public static function stream(
        Builder $query,
        Collection $columns,
        string $fileName,
        ExportFormat $format,
        string $csvDelimiter = ',',
    ): StreamedResponse {
        $query = (clone $query)
            ->with('event.form')
            ->orderBy('registrations.id');

        return response()->streamDownload(
            function () use ($query, $columns, $format, $csvDelimiter): void {
                match ($format) {
                    ExportFormat::Csv => self::writeCsv($query, $columns, $csvDelimiter),
                    ExportFormat::Xlsx => self::writeXlsx($query, $columns),
                };
            },
            $fileName.'.'.$format->extension(),
            ['Content-Type' => $format->contentType()],
        );
    }

    /**
     * @param  Collection<string, RegistrationExportColumn>  $columns
     */
    private static function writeCsv(Builder $query, Collection $columns, string $delimiter): void
    {
        $handle = fopen('php://output', 'w');

        if ($handle === false) {
            return;
        }

        // Byte order mark, so spreadsheet apps read the file as UTF-8.
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, self::headerRow($columns), $delimiter, '"', '');

        $query->chunk(self::CHUNK_SIZE, function (EloquentCollection $registrations) use ($handle, $columns, $delimiter): void {
            /** @var Registration $registration */
            foreach ($registrations as $registration) {
                fputcsv($handle, self::dataRow($registration, $columns), $delimiter, '"', '');
            }
        });

        fclose($handle);
    }

    /**
     * @param  Collection<string, RegistrationExportColumn>  $columns
     */
    private static function writeXlsx(Builder $query, Collection $columns): void
    {
        $writer = new XlsxWriter;
        $writer->openToFile('php://output');

        $writer->addRow(Row::fromValues(self::headerRow($columns)));

        $query->chunk(self::CHUNK_SIZE, function (EloquentCollection $registrations) use ($writer, $columns): void {
            /** @var Registration $registration */
            foreach ($registrations as $registration) {
                $writer->addRow(Row::fromValues(self::dataRow($registration, $columns)));
            }
        });

        $writer->close();
    }

    /**
     * @param  Collection<string, RegistrationExportColumn>  $columns
     * @return list<string>
     */
    private static function headerRow(Collection $columns): array
    {
        return $columns->map(fn (RegistrationExportColumn $column): string => $column->label)->values()->all();
    }

    /**
     * @param  Collection<string, RegistrationExportColumn>  $columns
     * @return list<string>
     */
    private static function dataRow(Registration $registration, Collection $columns): array
    {
        return $columns->map(fn (RegistrationExportColumn $column): string => $column->getValue($registration))->values()->all();
    }

    public static function defaultFileName(): string
    {
        return Str::slug(__('Registrations')).'-'.now()->format('Y-m-d');
    }

    /**
     * Strips path separators and characters that operating systems reject in file names.
     */
    public static function sanitizeFileName(?string $fileName): string
    {
        $sanitized = preg_replace('/[\x00-\x1f\/\\\\:*?"<>|]+/u', '', $fileName ?? '') ?? '';
        $sanitized = trim(preg_replace('/\s+/u', ' ', $sanitized) ?? '', " .\t\n\r\0\x0B");

        return $sanitized === '' ? self::defaultFileName() : $sanitized;
    }
}
