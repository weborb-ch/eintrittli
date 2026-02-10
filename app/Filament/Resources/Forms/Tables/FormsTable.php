<?php

namespace App\Filament\Resources\Forms\Tables;

use App\Filament\Resources\Forms\FormResource;
use App\Models\Form;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FormsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fields_count')
                    ->counts('fields')
                    ->label(__('Fields')),
                TextColumn::make('events_count')
                    ->counts('events')
                    ->label(__('Events')),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('duplicate')
                    ->label(__('Duplicate'))
                    ->icon(Heroicon::OutlinedDocumentDuplicate)
                    ->url(fn (Form $record) => FormResource::getUrl('create', [
                        'duplicate' => $record->getKey(),
                    ]))
                    ->visible(fn () => auth()->user()?->isAdmin() ?? false),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
