<?php

namespace App\Actions\Payment\PayPal;

use App\Models\Transaction;
use App\Services\PayPal\PayPalClient;
use Lorisleiva\Actions\Concerns\AsAction;

class StartPayPalPayment
{
    use AsAction;

    public function __construct(private PayPalClient $client)
    {
    }

    public function handle(Transaction $transaction): string
    {
        $order = $this->client->createOrder(
            $transaction->reference,
            $transaction->amount,
            route('paypal.return', $transaction->reference),
            route('paypal.cancel', $transaction->reference),
        );
        $transaction->external_id = $order['id'];
        $transaction->save();

        return $order['approve_url'];
    }
}
