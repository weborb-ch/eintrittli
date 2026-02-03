<?php

namespace App\Livewire;

use App\Enums\FormFieldType;
use App\Models\Event;
use App\Models\Form;
use App\Models\FormField;
use App\Models\Registration;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form as FormComponent;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;

/**
 * @property-read Schema $form
 */
class EventRegistration extends SimplePage
{
    protected string $view = 'livewire.event-registration';

    public ?Event $event = null;

    /** @var Collection<int, Registration> */
    public Collection $registrations;

    /** @var array<int, array<string, mixed>> */
    public array $entries = [];

    public function mount(string $code): void
    {
        $key = 'register-page:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 30)) {
            abort(429, __('Too many requests. Please try again later.'));
        }
        RateLimiter::hit($key, 60);

        $this->event = Event::where('code', $code)->firstOrFail();
        $this->event->load('form.fields');
        $this->registrations = collect();

        $this->addEntry();
    }

    public function getTitle(): string|Htmlable
    {
        return $this->event->name ?? __('Registration');
    }

    public function getHeading(): string|Htmlable|null
    {
        if ($this->registrations->isNotEmpty()) {
            return __('Registration Successful');
        }

        return $this->event->name ?? __('Registration');
    }

    protected function getClosedSubheading(): string
    {
        if ($this->event->registration_opens_at?->isFuture()) {
            return __('Opens :date', ['date' => $this->event->registration_opens_at->format('d.m.Y H:i')]);
        }

        return __('Registration for this event has ended.');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getSuccessSection(),
                $this->getClosedSection(),
                $this->getNoFormSection(),
                $this->getRegistrationFormSection(),
            ]);
    }

    protected function getSuccessSection(): Section
    {
        $schema = [];

        foreach ($this->registrations as $index => $registration) {
            $label = $this->registrations->count() > 1
                ? __('Registration :number', ['number' => $index + 1])
                : __('Confirmation Code');

            $schema[] = TextEntry::make("confirmation_code_{$index}")
                ->label($label)
                ->state($registration->confirmation_code)
                ->size('lg')
                ->weight('bold')
                ->copyable()
                ->icon('heroicon-o-check-circle')
                ->iconColor('success');
        }

        if ($this->registrations->isNotEmpty()) {
            $schema[] = TextEntry::make('registered_at')
                ->label(__('Registered'))
                ->state($this->registrations->first()->created_at->format('d.m.Y H:i'));
        }

        return Section::make()
            ->schema($schema)
            ->contained(false)
            ->visible(fn () => $this->registrations->isNotEmpty());
    }

    protected function getClosedSection(): Section
    {
        return Section::make()
            ->schema([
                TextEntry::make('closed_status')
                    ->label(__('Status'))
                    ->state(fn () => $this->getClosedSubheading())
                    ->icon('heroicon-o-clock')
                    ->iconColor('warning')
                    ->hiddenLabel(),
            ])
            ->contained(false)
            ->visible(fn () => $this->registrations->isEmpty() && ! $this->event->isRegistrationOpen());
    }

    protected function getNoFormSection(): Section
    {
        return Section::make()
            ->schema([
                TextEntry::make('no_form')
                    ->label(__('No registration form configured for this event.'))
                    ->state('')
                    ->hiddenLabel(),
            ])
            ->contained(false)
            ->visible(fn () => $this->registrations->isEmpty() && $this->event->isRegistrationOpen() && $this->event->form === null);
    }

    protected function getRegistrationFormSection(): Section
    {
        return Section::make()
            ->schema($this->getMultiEntryFormSchema())
            ->contained(false)
            ->visible(fn () => $this->registrations->isEmpty() && $this->event->isRegistrationOpen() && $this->event->form !== null);
    }

    /** @return array<int, \Filament\Schemas\Components\Component> */
    protected function getMultiEntryFormSchema(): array
    {
        $sections = [];

        foreach ($this->entries as $index => $entry) {
            $sections[] = Section::make(count($this->entries) > 1 ? __('Registration :number', ['number' => $index + 1]) : null)
                ->schema($this->getFieldsForEntry($index))
                ->headerActions(
                    count($this->entries) > 1
                        ? [
                            Action::make("remove_{$index}")
                                ->label(__('Remove'))
                                ->icon('heroicon-o-trash')
                                ->color('danger')
                                ->size('sm')
                                ->action(fn () => $this->removeEntry($index)),
                        ]
                        : []
                )
                ->collapsible(count($this->entries) > 1);
        }

        $sections[] = Actions::make([
            Action::make('add_entry')
                ->label(__('Add another registration'))
                ->icon('heroicon-o-plus')
                ->color('gray')
                ->action(fn () => $this->addEntry()),
        ]);

        $sections[] = FormComponent::make([])
            ->livewireSubmitHandler('register')
            ->footer([
                Actions::make([
                    Action::make('register')
                        ->label(count($this->entries) > 1 ? __('Register all') : __('Register'))
                        ->color(Color::hex('#74B1FF'))
                        ->submit('register'),
                ])->fullWidth(),
            ]);

        return $sections;
    }

    /** @return array<int, \Filament\Schemas\Components\Component> */
    protected function getFieldsForEntry(int $index): array
    {
        $components = [];

        $eventForm = $this->event?->form;
        if (! $eventForm instanceof Form) {
            return $components;
        }

        /** @var EloquentCollection<int, FormField> $fields */
        $fields = $eventForm->fields;

        foreach ($fields as $field) {
            $component = match ($field->type) {
                FormFieldType::Text => TextInput::make("entries.{$index}.{$field->name}")
                    ->label($field->name)
                    ->maxLength(1000),
                FormFieldType::Email => TextInput::make("entries.{$index}.{$field->name}")
                    ->label($field->name)
                    ->email(),
                FormFieldType::Number => TextInput::make("entries.{$index}.{$field->name}")
                    ->label($field->name)
                    ->numeric(),
                FormFieldType::Date => DatePicker::make("entries.{$index}.{$field->name}")
                    ->native(false)
                    ->label($field->name)
                    ->displayFormat('d.m.Y'),
                FormFieldType::Boolean => Checkbox::make("entries.{$index}.{$field->name}")
                    ->label($field->name),
                FormFieldType::Select => Select::make("entries.{$index}.{$field->name}")
                    ->label($field->name)
                    ->options(array_combine($field->options ?? [], $field->options ?? [])),
            };

            if ($field->is_required) {
                $component->required();
                if ($field->type === FormFieldType::Date) {
                    $component->rule('date');
                }
            }

            $components[] = $component;
        }

        return $components;
    }

    public function addEntry(): void
    {
        $this->entries[] = [];
    }

    public function removeEntry(int $index): void
    {
        unset($this->entries[$index]);
        $this->entries = array_values($this->entries);
    }

    public function register(): void
    {
        if (! $this->event->isRegistrationOpen()) {
            Notification::make()
                ->title(__('Registration is not open'))
                ->danger()
                ->send();

            return;
        }

        $key = 'registration:'.request()->ip();
        $allowedAttempts = max(10, count($this->entries));
        if (RateLimiter::tooManyAttempts($key, $allowedAttempts)) {
            Notification::make()
                ->title(__('Too many attempts'))
                ->body(__('Please try again in an hour.'))
                ->danger()
                ->send();

            return;
        }

        $createdRegistrations = collect();

        foreach ($this->entries as $entryData) {
            RateLimiter::hit($key, 3600);

            $registration = Registration::create([
                'event_id' => $this->event->id,
                'data' => $entryData,
            ]);

            $createdRegistrations->push($registration);
        }

        $this->registrations = $createdRegistrations;

        $message = $createdRegistrations->count() > 1
            ? __(':count registrations successful!', ['count' => $createdRegistrations->count()])
            : __('Registration successful!');

        Notification::make()
            ->title($message)
            ->success()
            ->send();
    }

    public function hasLogo(): bool
    {
        return false;
    }
}
