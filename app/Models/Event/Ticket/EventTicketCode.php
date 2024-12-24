<?php

namespace App\Models\Event\Ticket;

use Database\Factories\Event\Ticket\EventTicketCodeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventTicketCode extends Model
{
    /** @use HasFactory<EventTicketCodeFactory> */
    use HasFactory;
}
