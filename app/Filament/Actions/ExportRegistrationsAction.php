<?php

namespace App\Filament\Actions;

use App\Filament\Actions\Concerns\CanExportRegistrations;
use Filament\Actions\Action;

class ExportRegistrationsAction extends Action
{
    use CanExportRegistrations;

    public static function getDefaultName(): ?string
    {
        return 'export';
    }
}
