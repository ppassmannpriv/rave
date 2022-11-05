<?php

namespace App\Actions\EventTicketCode;

use App\Models\Event;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class CleanUpOrphans
{
    use AsAction;

    public function handle(): void
    {
        $events = Event::where('start', '>=', Carbon::now()->toDateTimeString())->get();
        foreach ($events as $event) {
            foreach ($event->eventTickets as $eventTicket) {
                foreach ($eventTicket->eventTicketCodes as $eventTicketCode) {
                    if ($eventTicketCode->orderItem->order->status === Order::STATUS_CANCELLED) {
                        $eventTicketCode->orderItem()->disassociate();
                        $eventTicketCode->save();
                        Log::info('Cleared Event Ticket Code: ' . $eventTicketCode->code);
                        $eventTicket->stock++;
                    }
                }
                if ($eventTicket->isDirty()) {
                    $eventTicket->save();
                }
            }
        }
    }
}
