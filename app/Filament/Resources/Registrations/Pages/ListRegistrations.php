<?php

namespace App\Filament\Resources\Registrations\Pages;

use App\Enums\FormFieldType;
use App\Filament\Resources\Registrations\RegistrationResource;
use App\Models\FormField;
use App\Models\Registration;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListRegistrations extends ListRecords
{
    protected static string $resource = RegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label(__('Export CSV'))
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => $this->exportCsv())
                ->visible(fn () => auth()->user()?->isAdmin() ?? false),
        ];
    }

    public function exportCsv(): StreamedResponse
    {
        $registrations = $this->getFilteredTableQuery()->with('event.form.fields')->get();

        // Collect all form fields across all events (preserving field definitions)
        $allFields = $registrations
            ->flatMap(fn (Registration $r) => $r->event->form->fields ?? collect())
            ->unique('name')
            ->keyBy('name');

        /** @var Collection<string, FormField> $allFields */

        return response()->streamDownload(function () use ($registrations, $allFields) {
            $handle = fopen('php://output', 'w');

            // Header row using field names
            $fieldLabels = $allFields->map(fn (FormField $f) => $f->name)->values()->toArray();
            $headers = [__('Confirmation Code'), __('Event'), __('Registered At'), ...$fieldLabels, __('Notes')];
            fputcsv($handle, $headers);

            foreach ($registrations as $registration) {
                $row = [
                    $registration->confirmation_code,
                    $registration->event->name ?? '',
                    $registration->created_at->format('d.m.Y H:i:s'),
                ];

                // Add each field value in order of form fields
                foreach ($allFields as $fieldName => $field) {
                    $value = $registration->data[$fieldName] ?? null;
                    $row[] = $this->formatValue($value, $field->type);
                }

                $row[] = $registration->notes ?? '';

                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 'registrations-'.now()->format('Y-m-d').'.csv');
    }

    private function formatValue(mixed $value, FormFieldType $type): string
    {
        if ($value === '' || $value === null) {
            return '';
        }

        return match ($type) {
            FormFieldType::Boolean => $value ? __('Yes') : __('No'),
            FormFieldType::Date => $this->formatDate($value),
            default => (string) $value,
        };
    }

    private function formatDate(mixed $value): string
    {
        try {
            return Carbon::parse($value)->format('d.m.Y');
        } catch (\Exception) {
            return (string) $value;
        }
    }
}
