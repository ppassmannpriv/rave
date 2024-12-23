<?php

namespace App\Actions\Payment\PayPalExpress;

use Omnipay\Omnipay;
use Omnipay\PayPal\ExpressGateway;

class PayPalExpressBase
{
    public ExpressGateway $gateway;
    public function __construct ()
    {
        $this->gateway = $this->initGateway();
    }

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
}
