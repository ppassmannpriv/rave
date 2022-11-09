<?php

namespace App\Console\Commands;

use App\Models\Event;
use League\Csv\Writer;
use Illuminate\Console\Command;
use SplTempFileObject;

class ExportEventTicketCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:event-ticket-codes {eventId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Event Ticket Codes export to have list of guests.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Looking for ordered and paid event ticket codes.');
        $event = Event::find($this->argument('eventId'));
        $results = [];
        foreach ($event?->eventTickets as $eventTicket) {
            foreach ($eventTicket->eventTicketCodes()->whereNotNull('order_item_id')->get() as $eventTicketCode) {
                $user = $eventTicketCode->orderItem->order->user;
                $results[] = [
                    'Code' => $eventTicketCode->code,
                    'Ticket Type' => $eventTicket->ticket_type,
                    'Guest' => $user?->name,
                ];
            }
        }
        $this->info('Fetched ' . count($results) . ' event ticket codes.');

        $fileName = 'event-ticket-codes-' . \Str::slug($event->name) . '.csv';
        $filePath = storage_path('app/exports/' . $fileName);
        touch($filePath);
        $writer = Writer::createFromPath($filePath);
        $writer->insertAll($results);
        // $writer->output($fileName);
        \Storage::putFile($filePath, file_get_contents($filePath));
        $this->info('Done.');

        return 0;
    }
}
