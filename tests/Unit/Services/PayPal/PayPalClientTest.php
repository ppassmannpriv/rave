<?php

namespace Tests\Unit\Services\PayPal;

use App\Services\PayPal\PayPalApiException;
use App\Services\PayPal\PayPalClient;
use App\ValueObjects\Money;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayPalClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'paypal.mode' => 'sandbox',
            'paypal.client_id' => 'client',
            'paypal.client_secret' => 'secret',
            'paypal.webhook_id' => 'WH-1',
            'paypal.currency' => 'EUR',
        ]);
    }

    public function test_create_order_sends_reference_as_custom_id_and_returns_approval_link(): void
    {
        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            '*/v2/checkout/orders' => Http::response([
                'id' => 'ORDER1',
                'links' => [
                    ['rel' => 'self', 'href' => 'https://api-m.sandbox.paypal.com/v2/checkout/orders/ORDER1'],
                    ['rel' => 'payer-action', 'href' => 'https://paypal.test/approve'],
                ],
            ], 201),
        ]);

        $order = (new PayPalClient())->createOrder('ABCDEFGH12', Money::fromDecimalString('25.50'), 'https://shop.test/return', 'https://shop.test/cancel');

        $this->assertSame(['id' => 'ORDER1', 'approve_url' => 'https://paypal.test/approve'], $order);
        Http::assertSent(function (Request $request) {
            if (! str_ends_with($request->url(), '/v2/checkout/orders')) {
                return false;
            }

            return $request->hasHeader('Authorization', 'Bearer tok')
                && $request['intent'] === 'CAPTURE'
                && $request['purchase_units'][0]['custom_id'] === 'ABCDEFGH12'
                && $request['purchase_units'][0]['amount'] === ['currency_code' => 'EUR', 'value' => '25.50']
                && $request['payment_source']['paypal']['experience_context']['return_url'] === 'https://shop.test/return';
        });
    }

    public function test_access_token_is_requested_once_and_reused(): void
    {
        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            '*/v2/checkout/orders/ORDER1' => Http::response(['id' => 'ORDER1']),
        ]);

        $client = new PayPalClient();
        $client->getOrder('ORDER1');
        $client->getOrder('ORDER1');

        Http::assertSentCount(3);
    }

    public function test_capturing_an_already_captured_order_returns_the_existing_order(): void
    {
        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            '*/v2/checkout/orders/ORDER1/capture' => Http::response(['name' => 'UNPROCESSABLE_ENTITY', 'details' => [['issue' => 'ORDER_ALREADY_CAPTURED']]], 422),
            '*/v2/checkout/orders/ORDER1' => Http::response(['id' => 'ORDER1', 'status' => 'COMPLETED']),
        ]);

        $this->assertSame('COMPLETED', (new PayPalClient())->captureOrder('ORDER1')['status']);
    }

    public function test_other_api_failures_throw(): void
    {
        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            '*/v2/checkout/orders/ORDER1/capture' => Http::response(['name' => 'INTERNAL_SERVER_ERROR'], 500),
        ]);

        $this->expectException(PayPalApiException::class);
        (new PayPalClient())->captureOrder('ORDER1');
    }

    public function test_webhook_signature_verification_reflects_paypals_verdict(): void
    {
        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            '*/v1/notifications/verify-webhook-signature' => Http::sequence()
                ->push(['verification_status' => 'SUCCESS'])
                ->push(['verification_status' => 'FAILURE']),
        ]);
        $headers = ['paypal-transmission-id' => 'T1', 'paypal-transmission-sig' => 'SIG', 'paypal-transmission-time' => 'now', 'paypal-cert-url' => 'https://paypal.test/cert', 'paypal-auth-algo' => 'SHA256withRSA'];
        $client = new PayPalClient();

        $this->assertTrue($client->verifyWebhookSignature($headers, ['id' => 'EVT1']));
        $this->assertFalse($client->verifyWebhookSignature($headers, ['id' => 'EVT1']));
        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), 'verify-webhook-signature') && $request['webhook_id'] === 'WH-1' && $request['transmission_id'] === 'T1');
    }

    public function test_missing_credentials_fail_loudly_without_calling_paypal(): void
    {
        config(['paypal.client_id' => null]);
        Http::fake();

        $this->expectException(PayPalApiException::class);
        try {
            (new PayPalClient())->getOrder('ORDER1');
        } finally {
            Http::assertNothingSent();
        }
    }
}
