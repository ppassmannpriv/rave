<?php

namespace App\Models\Event\Ticket;

use Database\Factories\Event\Ticket\EventTicketCodeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @method static \Database\Factories\Event\Ticket\EventTicketCodeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventTicketCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventTicketCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventTicketCode query()
 * @mixin \Eloquent
 */
class EventTicketCode extends Model
{
    /** @use HasFactory<EventTicketCodeFactory> */
    use HasFactory;
}
