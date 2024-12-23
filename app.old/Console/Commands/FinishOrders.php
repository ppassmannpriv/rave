<?php

namespace App\Console\Commands;

use App\Actions\Order\FinishOrderEmailUserAction;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FinishOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:finish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finish the outstanding orders and email Users.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Looking for outstanding orders.');
        $outstandingOrders = Order::whereRelation('transaction', 'state', '=', Transaction::STATE_PAID)->get();
        $this->info('Finishing ' . $outstandingOrders->count() . ' outstanding orders.');
        foreach ($outstandingOrders as $outstandingOrder) {
            FinishOrderEmailUserAction::make()->handle($outstandingOrder);
        }
        $this->info('Done.');

        return 0;
    }
}
