<?php

namespace App\Filament\Actions;

use App\Filament\Actions\Concerns\CanExportRegistrations;
use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;

class ExportRegistrationsBulkAction extends BulkAction
{
    use CanExportRegistrations {
        setUp as exportSetUp;
    }

    public static function getDefaultName(): ?string
    {
        return 'exportSelected';
    }

    protected function setUp(): void
    {
        $this->exportSetUp();

        $this
            ->fetchSelectedRecords(false)
            ->modalHeading(__('Export selected registrations'));
    }

    protected function getExportQuery(): Builder
    {
        return $this->getSelectedRecordsQuery();
    }
}
