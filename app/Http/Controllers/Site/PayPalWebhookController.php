<?php

namespace App\Http\Controllers\Site;

use App\Actions\Payment\PayPal\ProcessPayPalWebhookEvent;
use App\Http\Controllers\Controller;
use App\Models\PayPalWebhookEvent;
use App\Services\PayPal\PayPalApiException;
use App\Services\PayPal\PayPalClient;
use Illuminate\Http\Request;

class PayPalWebhookController extends Controller
{
    public function handle(Request $request, PayPalClient $client)
    {
        $event = $request->json()->all();
        if (!isset($event['id'], $event['event_type'])) {
            return response('Invalid event', 400);
        }

        try {
            $headers = collect($request->headers->all())->map(fn ($values) => $values[0] ?? null)->all();
            if ($client->verifyWebhookSignature($headers, $event) === false) {
                return response('Invalid signature', 400);
            }
        } catch (PayPalApiException $exception) {
            report($exception);

            return response('Verification unavailable', 500);
        }

        $stored = PayPalWebhookEvent::firstOrCreate(
            ['event_id' => $event['id']],
            ['event_type' => $event['event_type'], 'payload' => $event]
        );
        if ($stored->processed_at !== null) {
            return response('Already processed', 200);
        }

        try {
            ProcessPayPalWebhookEvent::run($event);
        } catch (\Throwable $throwable) {
            report($throwable);

            return response('Processing failed', 500);
        }
        $stored->processed_at = now();
        $stored->save();

        return response('OK', 200);
    }
}
