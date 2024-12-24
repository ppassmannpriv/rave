<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use DateInterval;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = $this->faker->dateTime();
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->paragraphs(2, true),
            'start' => $start,
            'end' => $start->add(new DateInterval('P' . $this->faker->numberBetween(8, 16) . 'H')),
            'location' => $this->faker->address(),
        ];
    }
}
