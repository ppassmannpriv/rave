<?php

namespace App\Actions\Payment;

use App\Models\Order;
use App\Models\Transaction;
use App\ValueObjects\Money;
use Lorisleiva\Actions\Concerns\AsAction;

class ApplyPaymentToTransaction
{
    use AsAction;

    /**
     * Records a received payment as a PartialTransaction and moves the transaction/order state
     * based on the cumulative amount paid. Shared by every payment method so there is exactly
     * one "mark paid" implementation.
     *
     * When $externalId is given (the provider's own payment id), applying the same id twice is a
     * no-op, which makes retried webhooks and a webhook racing the browser return safe.
     */
    public function handle(Transaction $transaction, Money $paymentAmount, array $raw, ?string $externalId = null): void
    {
        if ($externalId !== null && $transaction->partialTransactions()->where('external_id', $externalId)->exists()) {
            return;
        }

        $partialTransaction = Transaction\PartialTransaction::create([
            'amount' => $paymentAmount,
            'raw' => json_encode($raw),
        ] + ($externalId !== null ? ['external_id' => $externalId] : []));
        $transaction->partialTransactions()->save($partialTransaction);

        $totalPaid = $transaction->partialTransactions()->get()->reduce(
            fn (Money $carry, Transaction\PartialTransaction $partial) => $carry->add($partial->amount),
            Money::fromCents(0)
        );

        if ($totalPaid->equals($transaction->amount)) {
            $transaction->state = Transaction::STATE_PAID;
            if ($transaction->order !== null) {
                $transaction->order->status = Order::STATUS_PAID;
                $transaction->order->save();
            }
        } elseif ($totalPaid->lessThan($transaction->amount)) {
            // KI-02 fix: a real, visible trace instead of silence. The transaction's state is
            // left open (initialized/ordered) so a follow-up top-up payment with the same
            // reference is still found by the lookup and re-evaluated against the new total.
            $remaining = $transaction->amount->subtract($totalPaid);
            $transaction->comment = $this->appendComment($transaction->comment, "Partial payment received, {$remaining->toDecimalString()} still outstanding.");
        } else {
            $transaction->state = Transaction::STATE_PAID;
            if ($transaction->order !== null) {
                $transaction->order->status = Order::STATUS_PROCESSING;
                $transaction->comment = $this->appendComment($transaction->comment, 'Customer paid too much!');
                $transaction->order->save();
            }
        }
        $transaction->save();
    }

    private function appendComment(?string $existing, string $addition): string
    {
        return $existing !== null ? $existing . ' - ' . $addition : $addition;
    }
}
