<?php

namespace App\Filament\Resources\Registrations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->columns([
                TextColumn::make('confirmation_code')
                    ->searchable()
                    ->copyable()
                    ->label(__('Confirmation code'))
                    ->fontFamily('mono'),
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
                    ->options(fn () => \App\Models\Form::pluck('name', 'id'))
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
}
