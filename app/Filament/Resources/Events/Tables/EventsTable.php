<?php

namespace App\Filament\Resources\Events\Tables;

use App\Models\Event;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label(__('Code'))
                    ->copyable()
                    ->badge(),
                TextColumn::make('form.name')
                    ->label(__('Form'))
                    ->placeholder(__('No form')),
                TextColumn::make('registrations_count')
                    ->counts('registrations')
                    ->label(__('Registrations')),
                IconColumn::make('is_open')
                    ->label(__('Status'))
                    ->state(fn (Event $record): bool => $record->isRegistrationOpen())
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('registration_opens_at')
                    ->label(__('Opens At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('registration_closes_at')
                    ->label(__('Closes At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->recordActions([
                Action::make('qr_code')
                    ->label(__('QR Code'))
                    ->icon('heroicon-o-qr-code')
                    ->modalContent(fn (Event $record) => view('filament.events.qr-code-modal', ['event' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('Close')),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
