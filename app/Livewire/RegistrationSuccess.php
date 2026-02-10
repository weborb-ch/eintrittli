<?php

namespace App\Livewire;

use App\Models\Registration;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\SimplePage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property-read Schema $form
 */
class RegistrationSuccess extends SimplePage
{
    protected string $view = 'livewire.registration-success';

    public string $registrationGroupId;

    public string $eventName;

    public function mount(string $code, string $registrationGroupId): void
    {
        $this->registrationGroupId = $registrationGroupId;

        $registrations = $this->getRegistrations();

        if ($registrations->isEmpty()) {
            abort(404);
        }

        $this->eventName = $registrations->first()->event->name;
    }

    /** @return Collection<int, Registration> */
    protected function getRegistrations(): Collection
    {
        return Registration::where('registration_group_id', $this->registrationGroupId)
            ->with('event')
            ->get();
    }

    public function getTitle(): string|Htmlable
    {
        return $this->eventName;
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('Registration Successful');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getSuccessSection(),
            ]);
    }

    protected function getSuccessSection(): Section
    {
        return Section::make()
            ->schema(function (): array {
                $registrations = $this->getRegistrations();
                $schema = [];

                foreach ($registrations as $index => $registration) {
                    $schema[] = TextEntry::make("confirmation_code_{$index}")
                        ->label(__('Registration :number', ['number' => $index + 1]))
                        ->state($registration->confirmation_code)
                        ->size('lg')
                        ->weight('bold')
                        ->copyable()
                        ->icon('heroicon-o-check-circle')
                        ->iconColor('success');
                }

                if ($registrations->isNotEmpty()) {
                    $schema[] = TextEntry::make('registered_at')
                        ->label(__('Registered'))
                        ->state($registrations->first()->created_at->format('d.m.Y H:i'));
                }

                return $schema;
            })
            ->contained(false)
            ->key('success-section');
    }

    public function hasLogo(): bool
    {
        return false;
    }
}
