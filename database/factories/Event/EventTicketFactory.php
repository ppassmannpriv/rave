<?php

namespace Database\Factories\Event;

use App\Models\Event\EventTicket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventTicket>
 */
class EventTicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => $this->faker->numberBetween(1, 9),
            'price' => $this->faker->numberBetween(1000, 50000),
            'ticket_type' => EventTicket::DEFAULT_EVENT_TICKET_TYPE,
            'from' => $this->faker->dateTimeBetween('now', '+1 months'),
            'to' => $this->faker->dateTimeBetween('+1 months', '+3 months'),
        ];
    }
}
