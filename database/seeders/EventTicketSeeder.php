<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Event\EventTicket;
use Illuminate\Database\Seeder;

class EventTicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
            EventTicket::factory()->count(10)->create();
    }
}
