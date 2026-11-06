<?php

namespace App\Actions\Payment\PayPal;

use App\Models\PaymentMethod\PayPal;
use App\Models\Transaction;
use Lorisleiva\Actions\Concerns\AsAction;

class ProcessPayPalWebhookEvent
{
    use AsAction;

    public function handle(array $event): void
    {
        $resource = $event['resource'] ?? [];

        switch ($event['event_type'] ?? null) {
            case 'CHECKOUT.ORDER.APPROVED':
                $transaction = $this->findByExternalId($resource['id'] ?? null);
                if ($transaction !== null) {
                    CapturePayPalOrder::run($transaction);
                }
                break;
            case 'PAYMENT.CAPTURE.COMPLETED':
                $transaction = $this->findByReference($resource['custom_id'] ?? null);
                if ($transaction !== null) {
                    ApplyPayPalCapture::run($transaction, $resource);
                }
                break;
            case 'PAYMENT.CAPTURE.DENIED':
            case 'PAYMENT.CAPTURE.REFUNDED':
                $transaction = $this->findByReference($resource['custom_id'] ?? null);
                if ($transaction !== null) {
                    $transaction->comment = trim(($transaction->comment ?? '').' - PayPal '.$event['event_type'].' for capture '.($resource['id'] ?? '?').', needs staff review.', ' -');
                    $transaction->save();
                }
                break;
        }
    }

    private function findByExternalId(?string $externalId): ?Transaction
    {
        return $externalId === null ? null : $this->onlyPayPal(Transaction::where('external_id', $externalId)->first());
    }

    private function findByReference(?string $reference): ?Transaction
    {
        return $reference === null ? null : $this->onlyPayPal(Transaction::where('reference', $reference)->first());
    }

    private function onlyPayPal(?Transaction $transaction): ?Transaction
    {
        return $transaction?->paymentMethod?->alias === PayPal::ALIAS ? $transaction : null;
    }
}
