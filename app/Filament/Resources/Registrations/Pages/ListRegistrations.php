<?php

namespace App\Filament\Resources\Registrations\Pages;

use App\Filament\Resources\Registrations\RegistrationResource;
use App\Models\Registration;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListRegistrations extends ListRecords
{
    protected static string $resource = RegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => $this->exportCsv()),
        ];
    }

    public function exportCsv(): StreamedResponse
    {
        $registrations = Registration::with('event.form.fields')->get();

        // Collect all unique field names across all registrations
        $allFieldNames = $registrations
            ->flatMap(fn ($r) => array_keys($r->data ?? []))
            ->unique()
            ->values()
            ->toArray();

        return response()->streamDownload(function () use ($registrations, $allFieldNames) {
            $handle = fopen('php://output', 'w');

            // Header row
            $headers = ['Confirmation Code', 'Event', 'Registered At', ...$allFieldNames, 'Notes'];
            fputcsv($handle, $headers);

            foreach ($registrations as $registration) {
                $row = [
                    $registration->confirmation_code,
                    $registration->event?->name ?? '',
                    $registration->created_at->toDateTimeString(),
                ];

                // Add each field value in order
                foreach ($allFieldNames as $fieldName) {
                    $value = $registration->data[$fieldName] ?? '';
                    // Convert booleans to readable text
                    if (is_bool($value)) {
                        $value = $value ? 'Yes' : 'No';
                    }
                    $row[] = $value;
                }

                $row[] = $registration->notes ?? '';

                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 'registrations-'.now()->format('Y-m-d').'.csv');
    }
}
