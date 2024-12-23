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
            $messages[] = sprintf("%s\nRSVP: %d\nSold: %d\nCash: %s",
                $this->removeEmoji($event->name),
                $event->eventTicketsReserved(),
                $event->eventTicketsSold(),
                round($event->eventTicketsSoldPrice()),
            );
        }
        $message = null;
        if (count($messages) > 0) {
            $message = "Tickets " . Carbon::now()->format('d-m-Y H:i') . "\n\n";
            $message .= implode("\n\n", $messages);
        }

        if ($message !== null) {
            $this->info($message);
            try {
                $numbers = explode(',', env('TWILIO_TARGET_NUMBERS'));
                foreach ($numbers as $number) {
                    $twilioService->sendSMS($number, $message);
                }
            } catch (\Throwable $throwable) {
                Log::error($throwable->getMessage(), ['error' => $throwable]);
                $this->error($throwable->getMessage());
            }
        }

        $this->info('Done.');

        return 0;
    }

    private function removeEmoji(string $string): string
    {
        // Match Enclosed Alphanumeric Supplement
        $regex_alphanumeric = '/[\x{1F100}-\x{1F1FF}]/u';
        $clear_string = preg_replace($regex_alphanumeric, '', $string);

        // Match Miscellaneous Symbols and Pictographs
        $regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clear_string = preg_replace($regex_symbols, '', $clear_string);

        // Match Emoticons
        $regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clear_string = preg_replace($regex_emoticons, '', $clear_string);

        // Match Transport And Map Symbols
        $regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clear_string = preg_replace($regex_transport, '', $clear_string);

        // Match Supplemental Symbols and Pictographs
        $regex_supplemental = '/[\x{1F900}-\x{1F9FF}]/u';
        $clear_string = preg_replace($regex_supplemental, '', $clear_string);

        // Match Miscellaneous Symbols
        $regex_misc = '/[\x{2600}-\x{26FF}]/u';
        $clear_string = preg_replace($regex_misc, '', $clear_string);

        // Match Dingbats
        $regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
        $clear_string = preg_replace($regex_dingbats, '', $clear_string);

        return $clear_string;
    }
}
