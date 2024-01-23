<?php

namespace App\Console\Commands;

use App\Actions\EventTicketCode\CleanUpOrphans;
use App\Models\Event;
use App\Services\TwilioService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckEventSales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:check-sales';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check sales and notify by sms or whatsapp.';

    /**
     * Execute the console command.
     *
     * @param TwilioService $twilioService
     * @return int
     */
    public function handle(TwilioService $twilioService)
    {
        $this->info('Checking...');
        $events = Event::all()->filter(fn ($event) => !$event->isOver());
        $messages = [];
        foreach ($events as $event) {
            $messages[] = sprintf("%s\nReserved: %d\nSold: %d\nEstimated income: %s",
                $event->name,
                $event->eventTicketsReserved(),
                $event->eventTicketsSold(),
                env('CURRENCY_SYMBOL') . number_format($event->eventTicketsSoldPrice(), 2),
            );
        }
        $message = null;
        if (count($messages) > 0) {
            $message = "Ticket update for " . Carbon::now()->toRfc822String() . "\n\n";
            $message .= implode("\n\n", $messages);
        }

        if ($message !== null) {
            $this->info($message);
            try {
                $twilioService->sendSMS(env('TWILIO_TARGET_NUMBER'), $message);
            } catch (\Throwable $throwable) {
                Log::error($throwable->getMessage(), ['error' => $throwable]);
                $this->error($throwable->getMessage());
            }
        }

        $this->info('Done.');

        return 0;
    }
}
