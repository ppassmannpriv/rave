<?php

namespace App\Actions\Payment\PayPalExpress;

use App\Exceptions\PaymentMethods\PayPalFriendsFamily\ReferenceValidationException;
use App\Exceptions\PaymentMethods\PayPalFriendsFamily\ValidationException;
use App\Exceptions\PaymentMethods\TransactionNotFoundException;
use App\Models\Order;
use App\Models\PaymentMethod\PayPalFriendsFamily;
use App\Models\Transaction;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;
use Omnipay\Common\Message\ResponseInterface;

class InitTransaction extends PayPalExpressBase
{
    use AsAction;

    public function handle(Transaction $transaction)
    {
        $payPalData = $transaction->order->toPayPalExpressArray();
        /**
         * @var ResponseInterface $response
         */
        $response = $this->gateway->purchase($payPalData)->send();
        $transaction->token = $response->getData()['TOKEN'];
        $transaction->state = Transaction::STATE_PROCESSING;
        $transaction->save();

        $response->redirect();
    }
}
