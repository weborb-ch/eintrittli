<?php

namespace App\Filament\Resources\Forms\Schemas;

use App\Enums\FormFieldType;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class FormForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(255),
                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                Repeater::make('fields')
                    ->label(__('Fields'))
                    ->relationship()
                    ->orderColumn('sort_order')
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                    ->schema([
                        TextInput::make('label')
                            ->label(__('Label'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set, $get) => $set('name', $get('name') ?: Str::snake($state))),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->rules(['alpha_dash']),
                        Select::make('type')
                            ->options(collect(FormFieldType::cases())->mapWithKeys(fn ($type) => [$type->value => $type->label()]))
                            ->required()
                            ->live(),
                        TagsInput::make('options')
                            ->visible(fn ($get) => $get('type') === FormFieldType::Select->value)
                            ->placeholder(__('Add option')),
                        Toggle::make('is_required')
                            ->label(__('Is required'))
                            ->default(true),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
