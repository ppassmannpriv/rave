<?php

namespace App\Actions\Payment\PayPalExpress;

use App\Exceptions\PaymentMethods\PayPalFriendsFamily\ReferenceValidationException;
use App\Exceptions\PaymentMethods\PayPalFriendsFamily\ValidationException;
use App\Exceptions\PaymentMethods\TransactionNotFoundException;
use App\Http\Requests\Site\Payment\PaymentProviderReturnRequest;
use App\Models\Order;
use App\Models\PaymentMethod\PayPalFriendsFamily;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;
use Omnipay\Common\Message\ResponseInterface;

class FinalizeTransaction extends PayPalExpressBase
{
    use AsAction;

    public function handle(PaymentProviderReturnRequest $request)
    {
        $transaction = Transaction::where('token', $request->query('token'))
            ->where('state', 'processing')
            ->first();
        if ($transaction === null) {
            $transaction = Transaction::where('token', $request->query('token'))->first();
            if ($transaction->state === Transaction::STATE_PAID) {
                Log::info("Transaction already paid!");
                return \Redirect::to('/cart/success');
            }
        }
        $payPalData = $transaction->order->toPayPalExpressArray();
        $payPalData['payer_id'] = $request->input('PayerID');
        $payPalData['transactionReference'] = $request->input('token');

        $response = $this->gateway->completePurchase($payPalData)->send();
        if ($response->isSuccessful()) {
            $transaction->state = Transaction::STATE_PAID;
            $transaction->save();
            Log::info('Transaction state changed to ' . Transaction::STATE_PAID);

            $transaction->order->status = Order::STATUS_PAID;
            $transaction->order->save();
            Log::info('Order state changed to ' . Transaction::STATE_PAID);

            return \Redirect::to('/cart/success');
        }
    }
}
