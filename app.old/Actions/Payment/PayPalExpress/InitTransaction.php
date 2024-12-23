<?php

namespace App\Actions\Payment\PayPalExpress;


use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;
use Omnipay\Common\Message\ResponseInterface;

class InitTransaction extends PayPalExpressBase
{
    use AsAction;

    public function handle(Transaction $transaction)
    {
        try {
            $payPalData = $transaction->order->toPayPalExpressArray();

            /**
             * @var ResponseInterface $response
             */
            $response = $this->gateway->purchase($payPalData)->send();

            if ($response->isSuccessful()) {
                $transaction->token = $response->getData()['TOKEN'];
                $transaction->state = Transaction::STATE_PROCESSING;
                $transaction->save();
            }
            if ($response->isRedirect()) {
                $transaction->token = $response->getData()['TOKEN'];
                $transaction->state = Transaction::STATE_PROCESSING;
                $transaction->save();
                $response->redirect();
            }
            Log::error($response->getMessage(), ['response' => $response]);
            return redirect('/site/error')->with('error', $response->getMessage());

        } catch (\Throwable $throwable) {
            Log::error($throwable->getMessage(), ['exception' => $throwable]);
            return redirect('/site/error')->with('error', $throwable->getMessage());

        }
    }
}
