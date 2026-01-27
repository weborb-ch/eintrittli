<?php

namespace App\Filament\Resources\Registrations\Tables;

use App\Models\Registration;
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
                    ->fontFamily('mono'),
                TextColumn::make('event.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('data')
                    ->label('Data')
                    ->formatStateUsing(fn (Registration $record): string => collect((array) $record->data)->map(fn ($v, $k) => "$k: $v")->take(3)->join(', '))
                    ->limit(50)
                    ->tooltip(fn (Registration $record): string => collect((array) $record->data)->map(fn ($v, $k) => "$k: $v")->join("\n")),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Registered at'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('event')
                    ->relationship('event', 'name')
                    ->preload()
                    ->searchable(),
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
