<?php

namespace App\Filament\Resources\Registrations\Schemas;

use App\Models\Registration;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('event')
                    ->content(fn (Registration $record) => $record->event?->name ?? '-'),
                Placeholder::make('confirmation_code')
                    ->content(fn (Registration $record) => $record->confirmation_code),
                Section::make('Registration Data')
                    ->schema(fn (Registration $record) => self::getDataFields($record))
                    ->columnSpanFull()
                    ->columns(2),
                Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    /** @return array<int, TextInput> */
    private static function getDataFields(Registration $record): array
    {
        $fields = [];

        foreach ($record->data ?? [] as $key => $value) {
            $fields[] = TextInput::make("data.{$key}")
                ->label(ucfirst(str_replace('_', ' ', $key)))
                ->default(is_bool($value) ? ($value ? 'Yes' : 'No') : $value);
        }

        return $fields;
    }
}
