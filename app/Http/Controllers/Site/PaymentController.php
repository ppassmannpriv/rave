<?php

namespace App\Http\Controllers\Site;

use App\Actions\Order\AbortOrderAction;
use App\Actions\Payment\PayPalExpress\FinalizeTransaction;
use App\Http\Requests\Site\Payment\PaymentProviderReturnRequest;
use App\Models\Transaction;
use App\Actions\Cart\CreateOrderFromCart;
use App\Http\Requests\Site\OrderCartRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Omnipay\Common\Message\ResponseInterface;
use Sentry\ErrorHandler;

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
            $response->redirect();
        }
    }

    public function returnPayment(PaymentProviderReturnRequest $request)
    {
        try {
            return FinalizeTransaction::make()->handle($request);
        } catch (\Throwable $throwable) {
            \Sentry\captureException($throwable);
            return redirect('/cart')->with('message', 'An error occured! Please try again or contact an administrator!');
        }

    }

    public function cancelPayment(Request $request)
    {
        $transaction = Transaction::where('token', $request->input('token'))->first();
        $order = $transaction->order;
        AbortOrderAction::make()->handle($order);

        return redirect('/cart')->with('message', 'Order aborted due to payment cancellation.');
    }
}
