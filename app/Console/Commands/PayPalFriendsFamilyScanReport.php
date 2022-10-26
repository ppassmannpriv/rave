<?php

namespace App\Console\Commands;

use App\Exceptions\PaymentMethodException;
use App\Actions\Payment\PayPalFriendsFamily\PayTransaction;
use App\Models\PaymentMethod\PayPalFriendsFamily;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use Illuminate\Support\Facades\Log;

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
	$this->info('Scanning files');
        $files = Storage::files(PayPalFriendsFamily::REPORT_DIR);
        foreach ($files as $file) {
            if (Storage::exists($file) === false) {
                continue;
            }
            $csv = Reader::createFromString(Storage::get($file));
            $csv->setHeaderOffset(0);
            foreach ($csv->getRecords($csv->getHeader()) as $record) {
                try {
                    PayTransaction::make()->handle($record);
                } catch (PaymentMethodException $exception) {
                    Log::error($exception, ['record' => $record]);
                    $this->info($exception->getMessage());
                } catch (\Exception $exception) {
                    Log::error($exception, ['record' => $record]);
                    $this->info($exception->getMessage());

                    return 1;
                }
            }

            Storage::move($file, str_replace(PayPalFriendsFamily::REPORT_DIR, PayPalFriendsFamily::REPORT_DIR . '/processed', $file));
	}
	$this->info('Done.');

        return 0;
    }
}
