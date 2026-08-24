<?php

namespace App\Filament\Actions\Concerns;

use App\Enums\ExportFormat;
use App\Filament\Exports\RegistrationExportColumn;
use App\Filament\Exports\RegistrationExportColumnGroup;
use App\Filament\Exports\RegistrationExporter;
use App\Models\Event;
use App\Models\Registration;
use Closure;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\GridDirection;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Shared configuration for the registration export header and bulk actions.
 */
trait CanExportRegistrations
{
    /**
     * @var Collection<string, RegistrationExportColumnGroup>|null
     */
    protected ?Collection $exportColumnGroups = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('Export'))
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('gray')
            ->modalHeading(__('Export registrations'))
            ->modalDescription(function (): string {
                $count = $this->getExportQuery()->count();

                return trans_choice(':count registration will be exported|:count registrations will be exported', $count, ['count' => $count]);
            })
            ->modalIcon(Heroicon::OutlinedArrowDownTray)
            ->modalWidth(Width::SevenExtraLarge)
            ->modalSubmitActionLabel(__('Download'))
            ->schema(fn (): array => $this->getExportFormSchema())
            ->action(fn (array $data): ?StreamedResponse => $this->export($data))
            ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false);
    }

    /**
     * @return array<int, Component>
     */
    protected function getExportFormSchema(): array
    {
        return [
            Grid::make(3)
                ->schema([
                    TextInput::make('fileName')
                        ->label(__('File name'))
                        ->default(fn (): string => RegistrationExporter::defaultFileName())
                        ->required()
                        ->maxLength(120)
                        ->suffix(fn (Get $get): string => '.'.$this->resolveFormat($get('format'))->extension()),
                    Radio::make('format')
                        ->label(__('Format'))
                        ->options(ExportFormat::options())
                        ->default(ExportFormat::Csv->value)
                        ->required()
                        ->inline()
                        ->inlineLabel(false)
                        ->live(),
                    Select::make('csvDelimiter')
                        ->label(__('CSV separator'))
                        ->options([
                            ',' => __('Comma'),
                            ';' => __('Semicolon'),
                            "\t" => __('Tab'),
                        ])
                        ->default(',')
                        ->selectablePlaceholder(false)
                        ->native(false)
                        ->required()
                        ->visible(fn (Get $get): bool => $this->resolveFormat($get('format')) === ExportFormat::Csv),
                ]),
            Section::make(__('Columns'))
                ->description(__('Uncheck what the file should leave out.'))
                ->contained(false)
                ->columns(2)
                ->schema($this->getColumnGroupComponents()),
        ];
    }

    /**
     * One block of checkboxes per group, so it is obvious which form a column comes from.
     *
     * @return array<int, Component>
     */
    protected function getColumnGroupComponents(): array
    {
        $groups = $this->getExportColumnGroups();
        $sharedKeys = $this->getSharedColumnKeys();

        return $groups
            ->map(function (RegistrationExportColumnGroup $group) use ($sharedKeys): Fieldset {
                if ($group->columns->isEmpty()) {
                    return Fieldset::make($group->label)
                        ->schema([
                            Text::make(__('This form has no fields that can be exported.'))
                                ->key("emptyColumns.{$group->key}")
                                ->columnSpanFull(),
                        ]);
                }

                $checkboxes = CheckboxList::make("columns.{$group->key}")
                    ->hiddenLabel()
                    ->options($group->options())
                    ->default(array_keys($group->options()))
                    ->columns(2)
                    ->gridDirection(GridDirection::Row)
                    ->bulkToggleable()
                    ->columnSpanFull();

                // A field name can appear in several forms while mapping to one export column, so
                // those checkboxes have to stay in step. Groups without shared names stay static.
                if (array_intersect($group->keys(), $sharedKeys) !== []) {
                    $checkboxes
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => $this->syncSharedColumns($get, $set, $group->key));
                }

                if ($group->key === RegistrationExportColumnGroup::GeneralKey) {
                    $checkboxes->rules([$this->atLeastOneColumnRule()]);
                }

                return Fieldset::make($group->label)
                    ->schema([$checkboxes]);
            })
            ->values()
            ->all();
    }

    /**
     * Column keys that more than one group offers.
     *
     * @return list<string>
     */
    protected function getSharedColumnKeys(): array
    {
        $counts = [];

        foreach ($this->getExportColumnGroups() as $group) {
            foreach ($group->keys() as $key) {
                $counts[$key] = ($counts[$key] ?? 0) + 1;
            }
        }

        return array_keys(array_filter($counts, fn (int $count): bool => $count > 1));
    }

    /**
     * Mirrors the toggled group's shared column keys into every other group that offers them.
     */
    protected function syncSharedColumns(Get $get, Set $set, string $changedGroupKey): void
    {
        $groups = $this->getExportColumnGroups();
        $changedGroup = $groups->get($changedGroupKey);

        if (! $changedGroup instanceof RegistrationExportColumnGroup) {
            return;
        }

        $selected = array_values((array) $get("columns.{$changedGroupKey}"));

        foreach ($groups as $key => $group) {
            if ($key === $changedGroupKey) {
                continue;
            }

            $shared = array_values(array_intersect($group->keys(), $changedGroup->keys()));

            if ($shared === []) {
                continue;
            }

            $current = array_values((array) $get("columns.{$key}"));

            $next = array_values(array_unique([
                ...array_diff($current, $shared),
                ...array_intersect($shared, $selected),
            ]));

            if (array_diff($next, $current) !== [] || array_diff($current, $next) !== []) {
                $set("columns.{$key}", $next);
            }
        }
    }

    /**
     * The groups are separate fields, so emptiness has to be validated across all of them.
     */
    protected function atLeastOneColumnRule(): Closure
    {
        return fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
            if (Arr::flatten((array) $get('columns')) === []) {
                $fail(__('Select at least one column.'));
            }
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function export(array $data): ?StreamedResponse
    {
        $query = $this->getExportQuery();
        $count = (clone $query)->count();

        if ($count === 0) {
            Notification::make()
                ->title(__('Nothing to export'))
                ->body(__('No registrations match the current selection.'))
                ->warning()
                ->send();

            return null;
        }

        /** @var list<string> $selectedColumnKeys */
        $selectedColumnKeys = array_values(array_unique(Arr::flatten((array) ($data['columns'] ?? []))));
        $columns = $this->getExportColumns()->only($selectedColumnKeys);

        if ($columns->isEmpty()) {
            Notification::make()
                ->title(__('Nothing to export'))
                ->body(__('Select at least one column.'))
                ->warning()
                ->send();

            return null;
        }

        Notification::make()
            ->title(trans_choice(':count registration exported|:count registrations exported', $count, ['count' => $count]))
            ->success()
            ->send();

        return RegistrationExporter::stream(
            query: $query,
            columns: $columns,
            fileName: RegistrationExporter::sanitizeFileName($data['fileName'] ?? null),
            format: $this->resolveFormat($data['format'] ?? null),
            csvDelimiter: is_string($data['csvDelimiter'] ?? null) ? $data['csvDelimiter'] : ',',
        );
    }

    /**
     * The registrations this action exports. Overridden by the bulk action.
     */
    protected function getExportQuery(): Builder
    {
        $livewire = $this->getLivewire();

        if ($livewire instanceof HasTable) {
            return $livewire->getFilteredTableQuery() ?? Registration::query();
        }

        return Registration::query();
    }

    /**
     * @return Collection<string, RegistrationExportColumnGroup>
     */
    protected function getExportColumnGroups(): Collection
    {
        return $this->exportColumnGroups ??= RegistrationExporter::resolveColumnGroups($this->getExportFormIds());
    }

    /**
     * The forms the table's event and form filters narrow down to, or null when neither is active.
     *
     * This follows the filters rather than the matching registrations, so picking a form that has
     * no registrations yet still shows its columns.
     *
     * @return list<int>|null
     */
    protected function getExportFormIds(): ?array
    {
        $livewire = $this->getLivewire();

        if (! $livewire instanceof HasTable) {
            return null;
        }

        $toIds = fn (mixed $values): array => array_values(array_filter(array_map(
            fn (mixed $value): int => (int) $value,
            is_array($values) ? $values : [],
        )));

        $formIds = $toIds(data_get($livewire->getTableFilterState('form'), 'values'));
        $eventIds = $toIds(data_get($livewire->getTableFilterState('event'), 'values'));

        if ($eventIds !== []) {
            $eventFormIds = Event::query()
                ->whereKey($eventIds)
                ->whereNotNull('form_id')
                ->pluck('form_id')
                ->map(fn (mixed $formId): int => (int) $formId)
                ->all();

            $formIds = $formIds === [] ? $eventFormIds : array_intersect($formIds, $eventFormIds);
        }

        if ($formIds === []) {
            return null;
        }

        return array_values(array_unique($formIds));
    }

    /**
     * @return Collection<string, RegistrationExportColumn>
     */
    protected function getExportColumns(): Collection
    {
        return RegistrationExporter::flattenColumnGroups($this->getExportColumnGroups());
    }

    protected function resolveFormat(mixed $format): ExportFormat
    {
        return (is_string($format) ? ExportFormat::tryFrom($format) : null) ?? ExportFormat::Csv;
    }
}
