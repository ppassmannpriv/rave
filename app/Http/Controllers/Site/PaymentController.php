<?php

namespace App\Http\Controllers\Site;

use App\Actions\Payment\PayPalExpress\FinalizeTransaction;
use App\Http\Requests\Site\Payment\PaymentProviderReturnRequest;
use App\Models\Transaction;
use App\Actions\Cart\CreateOrderFromCart;
use App\Http\Requests\Site\OrderCartRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Omnipay\Common\Message\ResponseInterface;

class PaymentController extends WebController
{
    public function initPayment(OrderCartRequest $request)
    {
        $order = CreateOrderFromCart::make()->handle($request->all());
        $gateway = $this->initGateway();
        $orderArray = $order->toPayPalExpressArray();

        /**
         * @var ResponseInterface $response
         */
        $response = $gateway->completeAuthorize($orderArray)->send();

        if ($response->isRedirect()) {
            return $response->redirect();
        }
    }

    public function returnPayment(PaymentProviderReturnRequest $request)
    {
        try {
            return FinalizeTransaction::make()->handle($request);
        } catch (\Throwable $throwable) {
            dd($throwable);
        }

    }

    public function cancelPayment(Request $request)
    {
        dd($request);
    }
}
