<?php

namespace App\Actions\EventTicket;

use App\Models\EventTicket;
use App\Models\EventTicketCode;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateCodesForStockUpdateOnEventAction
{
    use AsAction;

    public function handle(EventTicket $eventTicket): void
    {
        if ($eventTicket->eventTicketCodes->count() < $eventTicket->stock) {
            for ($i = $eventTicket->eventTicketCodes->count(); $i < $eventTicket->stock; $i++) {
                $eventTicketCode = new EventTicketCode(['code' => $this->generateUniqueCode()]);
                $eventTicketCode->eventTicket()->associate($eventTicket);
                $eventTicketCode->save();
            }
        }
    }

    private function generateUniqueCode(): string
    {
        $uniqueCode = Str::upper(Str::random(6));
        if (EventTicketCode::where('code', '=', $uniqueCode)->exists()) {
            return $this->generateUniqueCode();
        }
        return $uniqueCode;
    }
}
