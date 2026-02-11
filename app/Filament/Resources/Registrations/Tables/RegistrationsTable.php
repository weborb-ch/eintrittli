<?php

namespace App\Filament\Resources\Registrations\Tables;

use App\Enums\FormFieldType;
use App\Models\Registration;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class RegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('event.form.fields'))
            ->columns([
                TextColumn::make('confirmation_code')
                    ->searchable()
                    ->copyable()
                    ->label(__('Confirmation code'))
                    ->fontFamily('mono')
                    ->description(function (Registration $record): ?HtmlString {
                        $html = self::formatFormData($record);
                        if ($html === null) {
                            return null;
                        }

                        $plainText = strip_tags($html->toHtml());
                        $maxLength = 60;

                        if (mb_strlen($plainText) <= $maxLength) {
                            return $html;
                        }

                        return new HtmlString(mb_substr($plainText, 0, $maxLength).'...');
                    }),
                TextColumn::make('event.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label(__('Registered At')),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('event')
                    ->label(__('Event'))
                    ->relationship('event', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
                SelectFilter::make('form')
                    ->label(__('Form'))
                    ->options(fn () => \App\Models\Form::orderBy('name')->pluck('name', 'id'))
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['values'],
                        fn (Builder $q, array $formIds) => $q->whereHas(
                            'event',
                            fn (Builder $eq) => $eq->whereIn('form_id', $formIds),
                        ),
                    )),
                Filter::make('registered_from')
                    ->label(__('Registered from'))
                    ->form([
                        DatePicker::make('registered_from')
                            ->native(false)
                            ->label(__('Registered from')),
                    ])
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['registered_from'],
                        fn (Builder $q, string $date) => $q->whereDate('created_at', '>=', $date),
                    )),
                Filter::make('registered_until')
                    ->label(__('Registered until'))
                    ->form([
                        DatePicker::make('registered_until')
                            ->native(false)
                            ->label(__('Registered until')),
                    ])
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['registered_until'],
                        fn (Builder $q, string $date) => $q->whereDate('created_at', '<=', $date),
                    )),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    private static function formatFormData(Registration $record): ?HtmlString
    {
        $fields = $record->event->form?->fields;
        $data = $record->data ?? [];

        if (! $fields || $fields->isEmpty() || empty($data)) {
            return null;
        }

        $parts = [];

        foreach ($fields as $field) {
            if ($field->type === FormFieldType::Description) {
                continue;
            }

            $value = $data[$field->name] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            $formatted = match ($field->type) {
                FormFieldType::Boolean => $value ? __('Yes') : __('No'),
                FormFieldType::Date => self::formatDate($value),
                default => e((string) $value),
            };

            $parts[] = '<span class="text-gray-500 dark:text-gray-400">'.e($field->name).':</span> '.$formatted;
        }

        if (empty($parts)) {
            return null;
        }

        return new HtmlString(implode(' · ', $parts));
    }

    private static function formatDate(mixed $value): string
    {
        try {
            return Carbon::parse($value)->format('d.m.Y');
        } catch (\Exception) {
            return e((string) $value);
        }
    }
}
