<?php

namespace Database\Factories;

use App\Enums\FormFieldType;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormField>
 */
class FormFieldFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'form_id' => Form::factory(),
            'type' => FormFieldType::Text,
            'name' => fake()->unique()->word(),
            'options' => null,
            'content' => null,
            'is_required' => false,
            'must_be_true' => false,
            'sort_order' => 0,
        ];
    }

    public function type(FormFieldType $type): static
    {
        return $this->state(fn (): array => ['type' => $type]);
    }

    public function boolean(): static
    {
        return $this->type(FormFieldType::Boolean);
    }

    public function date(): static
    {
        return $this->type(FormFieldType::Date);
    }

    /**
     * @param  array<int, string>  $options
     */
    public function select(array $options): static
    {
        return $this->state(fn (): array => [
            'type' => FormFieldType::Select,
            'options' => $options,
        ]);
    }

    public function description(string $content): static
    {
        return $this->state(fn (): array => [
            'type' => FormFieldType::Description,
            'name' => null,
            'content' => $content,
        ]);
    }
}
