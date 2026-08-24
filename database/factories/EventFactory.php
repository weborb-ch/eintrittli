<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Form;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'form_id' => Form::factory(),
            'name' => fake()->unique()->sentence(3),
            'registration_opens_at' => null,
            'registration_closes_at' => null,
        ];
    }
}
