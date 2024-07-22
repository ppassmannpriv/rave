<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Order;
use League\Csv\Writer;
use Illuminate\Console\Command;
use SplTempFileObject;

class ExportOrderTotals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:order-totals {eventId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Order totals for PayPal Express by Event ID.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Looking for orders.');
        $event = Event::find($this->argument('eventId'));
        $orders = [];
        foreach ($event?->eventTickets as $eventTicket) {
            foreach ($eventTicket->eventTicketCodes()->whereNotNull('order_item_id')->get() as $eventTicketCode) {
                /**
                 * @var ?Order $order
                 */
                $order = $eventTicketCode->orderItem?->order ?? null;
                if ($order !== null && $order === Order::STATUS_CLOSED) {
                    $orders[$order->id] = $order;
                }
            }
        }
        $this->info('Fetched ' . count($orders) . ' orders.');
        $orders = $this->formatOrders($orders);
        $fileName = 'event-ticket-codes-' . \Str::slug($event->name) . '.csv';
        $filePath = storage_path('app/exports/' . $fileName);
        touch($filePath);
        $writer = Writer::createFromPath($filePath);
        $writer->insertAll($orders);
        // $writer->output($fileName);
        $finishedFile = \File::get($filePath);
        \Storage::put($filePath, $finishedFile);
        $this->info('Done.');

        return 0;
    }

    private function formatOrders(array $orders): array
    {
        $csvOrders = array_map(fn(Order $order) => [
            'id' => $order->id,
            'total' => $order->price,
            'created_at' => $order->created_at,
            'user' => $order->user?->name,
            'email' => $order->user?->email,
        ], $orders);
        return $csvOrders;
    }
}
