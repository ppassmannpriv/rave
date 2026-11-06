<?php

namespace App\Actions\Payment\PayPal;

use App\Actions\Payment\ApplyPaymentToTransaction;
use App\Models\Transaction;
use App\ValueObjects\Money;
use Lorisleiva\Actions\Concerns\AsAction;

class ApplyPayPalCapture
{
    use AsAction;

    /**
     * Applies one PayPal capture object (from a capture response or a PAYMENT.CAPTURE.* webhook
     * resource). Only COMPLETED captures in the configured currency count as money received.
     */
    public function handle(Transaction $transaction, array $capture): void
    {
        if (($capture['status'] ?? null) !== 'COMPLETED') {
            return;
        }
        if (($capture['amount']['currency_code'] ?? null) !== config('paypal.currency')) {
            $transaction->comment = trim(($transaction->comment ?? '').' - PayPal capture '.($capture['id'] ?? '?').' ignored: unexpected currency.', ' -');
            $transaction->save();

            return;
        }

        ApplyPaymentToTransaction::run(
            $transaction,
            Money::fromDecimalString($capture['amount']['value']),
            $capture,
            $capture['id']
        );
    }
}
