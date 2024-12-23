<?php

namespace App\Actions\EventTicket;

use App\Models\EventTicket;
use App\Models\EventTicketCode;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateCodesForNewEventTicket
{
    use AsAction;

    public function handle(EventTicket $eventTicket): void
    {
        if ($eventTicket->wasRecentlyCreated === false) {
            throw new \Exception('This is not a new EventTicket. We cannot regenerate EventTicketCodes this way.');
        }
        if ($eventTicket->eventTicketCodes->count() > 0) {
            throw new \Exception('There already exist EventTicketCodes for this EventTicket.');
        }
        for ($i = 0; $i < $eventTicket->stock; $i++) {
            $eventTicketCode = new EventTicketCode(['code' => $this->generateUniqueCode()]);
            $eventTicketCode->eventTicket()->associate($eventTicket);
            $eventTicketCode->save();
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
