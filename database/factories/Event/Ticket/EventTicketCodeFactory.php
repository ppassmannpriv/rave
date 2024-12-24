<?php

namespace Database\Factories\Event\Ticket;

use App\Models\Event\Ticket\EventTicketCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventTicketCode>
 */
class EventTicketCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_ticket_id' => $this->faker->numberBetween(1, 10),
            'code' => $this->faker->sha256(),
        ];
    }
}
