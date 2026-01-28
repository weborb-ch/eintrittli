<?php

namespace App\Livewire;

use App\Enums\FormFieldType;
use App\Models\Event;
use App\Models\Form;
use App\Models\FormField;
use App\Models\Registration;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component as LivewireComponent;

class RegistrationForm extends LivewireComponent implements HasSchemas
{
    use InteractsWithSchemas;

    #[Locked]
    public Event $event;

    /** @var array<string, mixed> */
    public array $formData = [];

    public ?Registration $registration = null;

    public function mount(Event $event): void
    {
        $this->event = $event->load('form.fields');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components($this->getFormSchema())
            ->statePath('formData');
    }

    /** @return array<int, Component> */
    protected function getFormSchema(): array
    {
        $components = [];

        $eventForm = $this->event->form;
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

    public function submit(): void
    {
        if (! $this->event->isRegistrationOpen()) {
            $this->addError('form', 'Registration is not open.');

            return;
        }

        $key = 'registration:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->addError('form', 'Too many attempts. Please try again later.');

            return;
        }
        RateLimiter::hit($key, 60);

        $this->getSchema('form')->validate();

        $this->registration = Registration::create([
            'event_id' => $this->event->id,
            'data' => $this->formData,
        ]);
    }

    public function render(): View
    {
        return view('livewire.registration-form');
    }
}
