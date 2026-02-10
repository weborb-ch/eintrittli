<?php

namespace App\Filament\Resources\Registrations\Schemas;

use App\Enums\FormFieldType;
use App\Models\FormField;
use App\Models\Registration;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Collection;

class RegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('event')
                    ->label(__('Event'))
                    ->content(fn (Registration $record) => $record->event->name ?? '-'),
                Placeholder::make('confirmation_code')
                    ->label(__('Confirmation code'))
                    ->content(fn (Registration $record) => $record->confirmation_code),
                Section::make(__('Registration Data'))
                    ->schema(fn (Registration $record) => self::getDataFields($record))
                    ->columnSpanFull()
                    ->columns(2),
                Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    /** @return array<int, Component> */
    private static function getDataFields(Registration $record): array
    {
        $components = [];

        $formFields = $record->event->form?->fields;

        /** @var Collection<int, FormField>|null $formFields */
        if (! $formFields) {
            return $components;
        }

        foreach ($formFields as $field) {
            if ($field->type === FormFieldType::Description) {
                continue;
            }

            $component = match ($field->type) {
                FormFieldType::Text => TextInput::make("data.{$field->name}")
                    ->label($field->name)
                    ->maxLength(1000),
                FormFieldType::Email => TextInput::make("data.{$field->name}")
                    ->label($field->name)
                    ->email(),
                FormFieldType::Number => TextInput::make("data.{$field->name}")
                    ->label($field->name)
                    ->numeric(),
                FormFieldType::Date => DatePicker::make("data.{$field->name}")
                    ->label($field->name)
                    ->displayFormat('d.m.Y')
                    ->native(false),
                FormFieldType::Boolean => Checkbox::make("data.{$field->name}")
                    ->label($field->name),
                FormFieldType::Select => Select::make("data.{$field->name}")
                    ->label($field->name)
                    ->options(array_combine($field->options ?? [], $field->options ?? []))
                    ->native(false),
            };

            if ($field->is_required) {
                $component->required();
                if ($field->type == FormFieldType::Date) {
                    $component->rule('date');
                }
            }

            if ($field->type === FormFieldType::Boolean && $field->must_be_true) {
                $component->accepted();
            }

            $components[] = $component;
        }

        return $components;
    }
}
