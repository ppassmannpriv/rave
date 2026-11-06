<?php

namespace App\Http\Controllers\Site;

use App\Actions\Payment\PayPal\CapturePayPalOrder;
use App\Models\PaymentMethod\PayPal;
use App\Models\Transaction;
use App\Services\PayPal\PayPalApiException;
use Illuminate\Support\Facades\Log;

class PayPalController extends WebController
{
    public function paymentReturn(string $reference)
    {
        $transaction = $this->payPalTransaction($reference);
        if ($transaction->state !== Transaction::STATE_PAID && $transaction->external_id !== null) {
            try {
                CapturePayPalOrder::run($transaction);
            } catch (PayPalApiException $exception) {
                Log::error($exception, ['reference' => $reference]);

                return \Redirect::to('/cart/success')->with('warning', 'We could not confirm your PayPal payment yet. It will be confirmed automatically; you will receive an email once it is.');
            }
        }

        return \Redirect::to('/cart/success');
    }

    public function paymentCancel(string $reference)
    {
        $this->payPalTransaction($reference);

        return \Redirect::to('/cart')->with('warning', 'The PayPal payment was cancelled. Your order is kept; contact us if you want to pay another way.');
    }

    private function payPalTransaction(string $reference): Transaction
    {
        $transaction = Transaction::where('reference', $reference)->firstOrFail();
        abort_unless($transaction->paymentMethod?->alias === PayPal::ALIAS, 404);

        return $transaction;
    }
}
