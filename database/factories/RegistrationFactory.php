<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Registration>
 */
class RegistrationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'data' => [],
            'registration_group_id' => (string) Str::uuid(),
            'notes' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function data(array $data): static
    {
        return $this->state(fn (): array => ['data' => $data]);
    }
}
