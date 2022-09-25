<?php

namespace App\Console\Commands;

use App\Actions\Payment\PayPalFriendsFamily\PayTransaction;
use App\Models\PaymentMethod\PayPalFriendsFamily;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;

class PayPalFriendsFamilyScanReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment_methods:paypal_ff:scan-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan reports from PayPal to see if transactions can be marked as paid.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $files = Storage::allFiles(PayPalFriendsFamily::REPORT_DIR);
        foreach ($files as $file) {
            if (Storage::exists($file) === false) {
                continue;
            }
            $csv = Reader::createFromString(Storage::get($file));
            $csv->setHeaderOffset(0);
            foreach ($csv->getRecords($csv->getHeader()) as $record) {
                $transaction = PayTransaction::make()->handle($record);
                dd($transaction);
            }
        }
        dd($files);
        return 0;
    }
}
