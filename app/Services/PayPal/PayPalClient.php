<?php

namespace App\Services\PayPal;

use App\ValueObjects\Money;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PayPalClient
{
    private const TOKEN_CACHE_KEY = 'paypal_business_access_token';

    /**
     * @return array{id: string, approve_url: string}
     *
     * @throws PayPalApiException
     */
    public function createOrder(string $reference, Money $amount, string $returnUrl, string $cancelUrl): array
    {
        $response = $this->request()
            ->withHeaders(['PayPal-Request-Id' => 'create-'.$reference])
            ->post('/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'custom_id' => $reference,
                    'invoice_id' => $reference,
                    'amount' => [
                        'currency_code' => config('paypal.currency'),
                        'value' => $amount->toDecimalString(),
                    ],
                ]],
                'payment_source' => ['paypal' => ['experience_context' => [
                    'user_action' => 'PAY_NOW',
                    'return_url' => $returnUrl,
                    'cancel_url' => $cancelUrl,
                ]]],
            ]);
        $order = $this->json($response, 'create order');

        $approveUrl = collect($order['links'] ?? [])->firstWhere(fn ($link) => in_array($link['rel'] ?? null, ['payer-action', 'approve'], true))['href'] ?? null;
        if (! isset($order['id']) || $approveUrl === null) {
            throw new PayPalApiException('PayPal create order response has no id or approval link: '.$response->body());
        }

        return ['id' => $order['id'], 'approve_url' => $approveUrl];
    }

    /**
     * Captures an approved order. Capturing an order that is already captured is not an error:
     * the existing order is returned instead, so retries and the webhook/return race are safe.
     *
     * @throws PayPalApiException
     */
    public function captureOrder(string $orderId): array
    {
        $response = $this->request()
            ->withHeaders(['PayPal-Request-Id' => 'capture-'.$orderId])
            ->withBody('{}', 'application/json')
            ->post('/v2/checkout/orders/'.$orderId.'/capture');

        if ($response->status() === 422 && str_contains($response->body(), 'ORDER_ALREADY_CAPTURED')) {
            return $this->getOrder($orderId);
        }

        return $this->json($response, 'capture order');
    }

    public function getOrder(string $orderId): array
    {
        return $this->json($this->request()->get('/v2/checkout/orders/'.$orderId), 'get order');
    }

    /**
     * @param  array<string, string|null>  $headers lower-cased PayPal transmission headers
     *
     * @throws PayPalApiException
     */
    public function verifyWebhookSignature(array $headers, array $event): bool
    {
        $response = $this->request()->post('/v1/notifications/verify-webhook-signature', [
            'auth_algo' => $headers['paypal-auth-algo'] ?? null,
            'cert_url' => $headers['paypal-cert-url'] ?? null,
            'transmission_id' => $headers['paypal-transmission-id'] ?? null,
            'transmission_sig' => $headers['paypal-transmission-sig'] ?? null,
            'transmission_time' => $headers['paypal-transmission-time'] ?? null,
            'webhook_id' => config('paypal.webhook_id'),
            'webhook_event' => $event,
        ]);

        return ($this->json($response, 'verify webhook')['verification_status'] ?? null) === 'SUCCESS';
    }

    private function request(): PendingRequest
    {
        $baseUrl = config('paypal.base_urls.'.config('paypal.mode'));
        if ($baseUrl === null) {
            throw new PayPalApiException('Unknown PAYPAL_BUSINESS_MODE "'.config('paypal.mode').'", expected sandbox or live.');
        }

        return Http::baseUrl($baseUrl)->acceptJson()->timeout(15)->withToken($this->accessToken());
    }

    private function accessToken(): string
    {
        $cached = Cache::get(self::TOKEN_CACHE_KEY);
        if ($cached !== null) {
            return $cached;
        }
        if (config('paypal.client_id') === null || config('paypal.client_secret') === null) {
            throw new PayPalApiException('PAYPAL_BUSINESS_CLIENT_ID / PAYPAL_BUSINESS_CLIENT_SECRET are not configured.');
        }

        $response = Http::baseUrl(config('paypal.base_urls.'.config('paypal.mode')))
            ->withBasicAuth(config('paypal.client_id'), config('paypal.client_secret'))
            ->asForm()->acceptJson()->timeout(15)
            ->post('/v1/oauth2/token', ['grant_type' => 'client_credentials']);
        $token = $this->json($response, 'obtain access token');
        if (! isset($token['access_token'])) {
            throw new PayPalApiException('PayPal token response has no access_token.');
        }
        Cache::put(self::TOKEN_CACHE_KEY, $token['access_token'], max(60, ($token['expires_in'] ?? 300) - 60));

        return $token['access_token'];
    }

    private function json(Response $response, string $what): array
    {
        if ($response->failed()) {
            throw new PayPalApiException('PayPal '.$what.' failed with HTTP '.$response->status().': '.$response->body());
        }

        return $response->json() ?? [];
    }
}
