<?php

namespace App\Filament\Resources\Registrations\Pages;

use App\Filament\Actions\ExportRegistrationsAction;
use App\Filament\Resources\Registrations\RegistrationResource;
use Filament\Resources\Pages\ListRecords;

class ListRegistrations extends ListRecords
{
    protected static string $resource = RegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportRegistrationsAction::make(),
        ];
    }
}
