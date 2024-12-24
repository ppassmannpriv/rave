<?php

namespace App\Models\Event;

use Database\Factories\Event\EventTicketFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventTicket extends Model
{
    /** @use HasFactory<EventTicketFactory> */
    use HasFactory;

    public const string REGULAR_EVENT_TICKET_TYPE = 'regular';
    public const string EARLY_BIRD_EVENT_TICKET_TYPE = 'early_bird';
    public const string PHASE_1_EVENT_TICKET_TYPE = 'phase_1';
    public const string PHASE_2_EVENT_TICKET_TYPE = 'phase_2';
    public const string LAST_MINUTE_EVENT_TICKET_TYPE = 'last_minute';
    public const string VIP_EVENT_TICKET_TYPE = 'vip';
    public const string GUEST_LIST_EVENT_TICKET_TYPE = 'guest_list';
    public const string DEFAULT_EVENT_TICKET_TYPE = self::REGULAR_EVENT_TICKET_TYPE;
    public const array EVENT_TICKET_TYPES = [
        self::REGULAR_EVENT_TICKET_TYPE,
        self::EARLY_BIRD_EVENT_TICKET_TYPE,
        self::PHASE_1_EVENT_TICKET_TYPE,
        self::PHASE_2_EVENT_TICKET_TYPE,
        self::LAST_MINUTE_EVENT_TICKET_TYPE,
        self::VIP_EVENT_TICKET_TYPE,
        self::GUEST_LIST_EVENT_TICKET_TYPE,
    ];
}
