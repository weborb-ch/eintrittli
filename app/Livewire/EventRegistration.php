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
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form as FormComponent;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\RateLimiter;

/**
 * @property-read Schema $form
 */
class EventRegistration extends SimplePage
{
    protected string $view = 'livewire.event-registration';

    public ?Event $event = null;

    public ?Registration $registration = null;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(string $code): void
    {
        $key = 'register-page:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 30)) {
            abort(429, 'Too many requests. Please try again later.');
        }
        RateLimiter::hit($key, 60);

        $this->event = Event::where('code', $code)->firstOrFail();
        $this->event->load('form.fields');

        $this->form->fill();
    }

    public function getTitle(): string|Htmlable
    {
        return $this->event->name ?? 'Registration';
    }

    public function getHeading(): string|Htmlable|null
    {
        if ($this->registration) {
            return 'Registration Successful';
        }

        return $this->event->name ?? 'Registration';
    }

    public function getSubheading(): string|Htmlable|null
    {
        if ($this->registration) {
            return 'Your confirmation code: '.$this->registration->confirmation_code;
        }

        if (! $this->event->isRegistrationOpen()) {
            return $this->getClosedSubheading();
        }

        if ($this->event->registration_closes_at !== null) {
            return 'Registration closes '.$this->event->registration_closes_at->format('F j, Y \a\t H:i');
        }

        return null;
    }

    protected function getClosedSubheading(): string
    {
        if ($this->event->registration_opens_at?->isFuture()) {
            return 'Opens '.$this->event->registration_opens_at->format('F j, Y \a\t H:i');
        }

        return 'Registration for this event has ended.';
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
        return Section::make()
            ->schema([
                TextEntry::make('registered_at')
                    ->label('Registered')
                    ->state(fn () => $this->registration?->created_at?->format('F j, Y \a\t H:i'))
                    ->icon('heroicon-o-check-circle')
                    ->iconColor('success'),
            ])
            ->contained(false)
            ->visible(fn () => $this->registration !== null);
    }

    protected function getClosedSection(): Section
    {
        return Section::make()
            ->schema([
                IconEntry::make('status')
                    ->label('Status')
                    ->state('closed')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->hiddenLabel(),
            ])
            ->contained(false)
            ->visible(fn () => $this->registration === null && ! $this->event->isRegistrationOpen());
    }

    protected function getNoFormSection(): Section
    {
        return Section::make()
            ->schema([
                TextEntry::make('no_form')
                    ->label('No registration form configured for this event.')
                    ->state('')
                    ->hiddenLabel(),
            ])
            ->contained(false)
            ->visible(fn () => $this->registration === null && $this->event->isRegistrationOpen() && $this->event->form === null);
    }

    protected function getRegistrationFormSection(): Section
    {
        return Section::make()
            ->schema([
                FormComponent::make($this->getFormSchema())
                    ->livewireSubmitHandler('register')
                    ->footer([
                        Actions::make([
                            Action::make('register')
                                ->label('Register')
                                ->color(Color::hex('#74B1FF'))
                                ->submit('register'),
                        ])->fullWidth(),
                    ]),
            ])
            ->contained(false)
            ->visible(fn () => $this->registration === null && $this->event->isRegistrationOpen() && $this->event->form !== null);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data');
    }

    /** @return array<int, \Filament\Schemas\Components\Component> */
    protected function getFormSchema(): array
    {
        $components = [];

        $eventForm = $this->event?->form;
        if (! $eventForm instanceof Form) {
            return $components;
        }

        /** @var Collection<int, FormField> $fields */
        $fields = $eventForm->fields;

        foreach ($fields as $field) {
            $component = match ($field->type) {
                FormFieldType::Text => TextInput::make($field->name)
                    ->label($field->label)
                    ->maxLength(1000),
                FormFieldType::Email => TextInput::make($field->name)
                    ->label($field->label)
                    ->email(),
                FormFieldType::Number => TextInput::make($field->name)
                    ->label($field->label)
                    ->numeric(),
                FormFieldType::Date => DatePicker::make($field->name)
                    ->native(false)
                    ->label($field->label),
                FormFieldType::Boolean => Checkbox::make($field->name)
                    ->label($field->label),
                FormFieldType::Select => Select::make($field->name)
                    ->label($field->label)
                    ->options(array_combine($field->options ?? [], $field->options ?? [])),
            };

            if ($field->is_required) {
                $component->required();
            }

            $components[] = $component;
        }

        return $components;
    }

    public function register(): void
    {
        if (! $this->event->isRegistrationOpen()) {
            Notification::make()
                ->title('Registration is not open')
                ->danger()
                ->send();

            return;
        }

        $key = 'registration:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            Notification::make()
                ->title('Too many attempts')
                ->body('Please try again later.')
                ->danger()
                ->send();

            return;
        }
        RateLimiter::hit($key, 60);

        $data = $this->form->getState();

        $this->registration = Registration::create([
            'event_id' => $this->event->id,
            'data' => $data,
        ]);

        Notification::make()
            ->title('Registration successful!')
            ->success()
            ->send();
    }

    public function hasLogo(): bool
    {
        return false;
    }
}
