<?php

namespace App\Filament\Resources\Registrations\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class RegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_id')
                    ->relationship('event', 'name')
                    ->required()
                    ->preload()
                    ->searchable()
                    ->disabled(fn ($record) => $record !== null),
                Placeholder::make('confirmation_code')
                    ->content(fn ($record) => $record->confirmation_code ?? 'Will be generated')
                    ->visibleOn('edit'),
                KeyValue::make('data')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
