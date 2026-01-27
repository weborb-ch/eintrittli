<?php

namespace App\Livewire;

use App\Enums\FormFieldType;
use App\Models\Event;
use App\Models\Form;
use App\Models\FormField;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RegistrationForm extends Component
{
    #[Locked]
    public Event $event;

    /** @var array<string, mixed> */
    public array $formData = [];

    public ?Registration $registration = null;

    public function mount(Event $event): void
    {
        $this->event = $event->load('form.fields');
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        $rules = [];

        $form = $this->event->form;
        if (! $form instanceof Form) {
            return $rules;
        }

        /** @var Collection<int, FormField> $fields */
        $fields = $form->fields;

        foreach ($fields as $field) {
            $fieldRules = [];

            if ($field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            $fieldRules[] = match ($field->type) {
                FormFieldType::Email => 'email',
                FormFieldType::Number => 'numeric',
                FormFieldType::Date => 'date',
                FormFieldType::Boolean => 'boolean',
                FormFieldType::Select => 'in:' . implode(',', $field->options ?? []),
                FormFieldType::Text => 'string|max:1000',
            };

            $rules["formData.{$field->name}"] = $fieldRules;
        }

        return $rules;
    }

    /** @return array<string, string> */
    public function validationAttributes(): array
    {
        $attributes = [];

        $form = $this->event->form;
        if ($form instanceof Form) {
            /** @var Collection<int, FormField> $fields */
            $fields = $form->fields;

            foreach ($fields as $field) {
                $attributes["formData.{$field->name}"] = $field->label;
            }
        }

        return $attributes;
    }

    public function submit(): void
    {
        if (! $this->event->isRegistrationOpen()) {
            $this->addError('form', 'Registration is not open.');

            return;
        }

        $key = 'registration:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->addError('form', 'Too many attempts. Please try again later.');

            return;
        }
        RateLimiter::hit($key, 60);

        $this->validate();

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
