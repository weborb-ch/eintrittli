<?php

namespace App\Filament\Resources\Forms\Schemas;

use App\Enums\FormFieldType;
use App\Models\Form;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FormForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('in_use_hint')
                    ->label(__('Notice'))
                    ->content(__('This form is used by an event and cannot be edited.'))
                    ->visible(fn (?Form $record) => $record?->isInUse() ?? false)
                    ->color('danger')
                    ->columnSpanFull(),
                TextInput::make('name')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(255)
                    ->disabled(fn (?Form $record) => $record?->isInUse() ?? false),
                Textarea::make('description')
                    ->label(__('Description'))
                    ->rows(3)
                    ->columnSpanFull()
                    ->disabled(fn (?Form $record) => $record?->isInUse() ?? false),
                Repeater::make('fields')
                    ->label(__('Fields'))
                    ->relationship()
                    ->orderColumn('sort_order')
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required(fn ($get) => $get('type') !== FormFieldType::Description->value)
                            ->hidden(fn ($get) => $get('type') === FormFieldType::Description->value)
                            ->maxLength(255),
                        Select::make('type')
                            ->options(collect(FormFieldType::cases())->mapWithKeys(fn ($type) => [$type->value => $type->label()]))
                            ->required()
                            ->live(),
                        TagsInput::make('options')
                            ->visible(fn ($get) => $get('type') === FormFieldType::Select->value)
                            ->placeholder(__('Add option')),
                        RichEditor::make('content')
                            ->label(__('Content'))
                            ->visible(fn ($get) => $get('type') === FormFieldType::Description->value)
                            ->required(fn ($get) => $get('type') === FormFieldType::Description->value)
                            ->columnSpanFull(),
                        Toggle::make('is_required')
                            ->label(__('Is required'))
                            ->default(true)
                            ->hidden(fn ($get) => $get('type') === FormFieldType::Description->value),
                        Toggle::make('must_be_true')
                            ->label(__('Must be accepted?'))
                            ->visible(fn ($get) => $get('type') === FormFieldType::Boolean->value),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->disabled(fn (?Form $record) => $record?->isInUse() ?? false)
                    ->addable(fn (?Form $record) => ! ($record?->isInUse() ?? false))
                    ->deletable(fn (?Form $record) => ! ($record?->isInUse() ?? false))
                    ->reorderable(fn (?Form $record) => ! ($record?->isInUse() ?? false)),
            ]);
    }
}
