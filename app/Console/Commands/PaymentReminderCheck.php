<?php

namespace App\Console\Commands;

use App\Actions\Order\CancelOrderAction;
use App\Actions\Payment\PaymentReminderAction;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PaymentReminderCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:payment-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remind users of outstanding payments by mail.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Looking for outstanding payments.');
        $orderTrunk = Order::where('status', '=', Order::STATUS_INITIALIZED)
            ->where('created_at', '<=', Carbon::now()->subDays(5)->toDateTimeString())
            ->whereRelation('transaction', 'state', '=', Transaction::STATE_INIT)->get();
        $outstandingOrders = $orderTrunk->where('created_at', '>=', Carbon::now()->subDays(6)->toDateTimeString());
        $cancelOrders = $orderTrunk->where('created_at', '<=', Carbon::now()->subDays(6)->toDateTimeString());
        $this->info('Reminding ' . $outstandingOrders->count() . ' outstanding payments.');
        foreach ($outstandingOrders as $outstandingOrder) {
            PaymentReminderAction::make()->handle($outstandingOrder);
        }
        $this->info('Cancelling ' . $cancelOrders->count() . ' orders.');
        foreach ($cancelOrders as $cancelOrder) {
            CancelOrderAction::make()->handle($cancelOrder);
        }
        $this->info('Done.');

        return 0;
    }
}
