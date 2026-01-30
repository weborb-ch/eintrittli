<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(255),
                Select::make('form_id')
                    ->relationship('form', 'name')
                    ->preload()
                    ->searchable()
                    ->required()
                    ->columnSpanFull()
                    ->native(false),
                Section::make(__('Registration Window'))
                    ->schema([
                        DateTimePicker::make('registration_opens_at')
                            ->label(__('Opens at'))
                            ->native(false),
                        DateTimePicker::make('registration_closes_at')
                            ->label(__('Closes at'))
                            ->native(false),
                    ])
                    ->columnSpanFull()
                    ->columns(2),
            ]);
    }
}
