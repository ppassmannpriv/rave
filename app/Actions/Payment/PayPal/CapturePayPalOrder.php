<?php

namespace App\Actions\Payment\PayPal;

use App\Models\Transaction;
use App\Services\PayPal\PayPalClient;
use Lorisleiva\Actions\Concerns\AsAction;

class CapturePayPalOrder
{
    use AsAction;

    public function __construct(private PayPalClient $client)
    {
    }

    public function handle(Transaction $transaction): void
    {
        $order = $this->client->captureOrder($transaction->external_id);
        foreach ($order['purchase_units'][0]['payments']['captures'] ?? [] as $capture) {
            ApplyPayPalCapture::run($transaction, $capture);
        }
    }
}
