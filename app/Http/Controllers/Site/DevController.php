<?php

namespace App\Http\Controllers\Site;

use App\Exceptions\Cart\SoldOutException;
use App\Mail\OrderCreatedNotification;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderCreated;
use App\Actions\Cart\AddTicketToCart;
use App\Actions\Cart\CreateOrderFromCart;
use App\Actions\Cart\RemoveTicketFromCart;
use App\Http\Requests\Site\AddToCartRequest;
use App\Http\Requests\Site\RemoveFromCartRequest;
use App\Http\Requests\Site\OrderCartRequest;
use App\Models\EventTicket;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use App\Services\CartService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Omnipay\Omnipay;
use Omnipay\PayPal\ExpressGateway;
use Omnipay\Common\Message\ResponseInterface;

class DevController extends WebController
{
    private function initGateway(): ExpressGateway
    {
        /**
         * @var ExpressGateway $gateway
         */
        $gateway = Omnipay::create('PayPal_Express');
        $gateway->setTestMode(config('paypal.paypal_is_test'));
        $gateway->setBrandName(config('paypal.paypal_brand_name'));
        $gateway->setBorderColor('CCCCCC');
        $gateway->setSellerPaypalAccountId(config('paypal.paypal_seller_account_id'));
        $gateway->setUsername(config('paypal.paypal_username'));
        $gateway->setPassword(config('paypal.paypal_password'));
        $gateway->setSignature(config('paypal.paypal_signature'));
        $gateway->setLandingPage(['billing', 'login']);

        return $gateway;
    }

    public function index() {
        $orderArr = [
            'items' => [
                ['name' => 'Test ticket 1', 'price' => '12.00', 'description' => 'Test ticket 1', 'quantity' => 1],
                ['name' => 'Test ticket 2', 'price' => '20.00', 'description' => 'Test ticket 2', 'quantity' => 1]
            ],
            'amount' => '32.00',
            'currency' => config('paypal.currency'),
            'returnUrl' => 'https://schleuse.eu/payment/return',
            'cancelUrl' => 'https://schleuse.eu/payment/cancel',
            'transactionReference' => 'asdf1234'
        ];
        $gateway = $this->initGateway();
        /**
         * @var ResponseInterface $response
         */
        $response = $gateway->purchase($orderArr)->send();

        if ($response->isSuccessful()) {
            // Payment was successful
            dd($response);
        } elseif ($response->isRedirect()) {
            Payment::create([
                'amount' => 32.00,
                'state' => 'processing',
                'reference' => 'TEST-PIETER-1',
                'payment_id' => $response->getData()['TOKEN'],
                'payer_id' => null,
                'payer_email' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null,
            ]);
            // Redirect to offsite payment gateway
            return $response->redirect();
        } else {
            // Payment failed
            dd($response->getMessage());
        }
        return $this->respond('dev.index', ['paymentMethods' => PaymentMethod::where('active', '=', true)->get(), 'siteType' => 'checkout']);
    }

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

    public function returnPayment(Request $request)
    {
        $gateway = $this->initGateway();
        //dd($request->input());
        $response = $gateway->fetchCheckout(['transactionReference' => $request->query('token'), $request->query('PayerID')])->send();
        // dd($response->getData(), $response->getData()['PAYMENTREQUEST_0_AMT']);
        $transaction = $gateway->completePurchase(array(
            'payer_id'             => $request->input('PayerID'),
            'transactionReference' => $request->input('token'),
            'amount' => $response->getData()['PAYMENTREQUEST_0_AMT']
        ));
        $response = $transaction->send();
        dd($response);
        // $response = $gateway->completeAuthorize($response->getData())->send();
        dd($request->query('token'), $request->query('PayerID'), $response);
    }

    public function cancelPayment(Request $request)
    {
        dd($request);
    }
}
