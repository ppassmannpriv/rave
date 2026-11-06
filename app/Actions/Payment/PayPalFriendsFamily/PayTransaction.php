<?php

namespace App\Actions\Payment\PayPalFriendsFamily;

use App\Actions\Payment\ApplyPaymentToTransaction;
use App\Exceptions\PaymentMethods\PayPalFriendsFamily\ReferenceValidationException;
use App\Exceptions\PaymentMethods\PayPalFriendsFamily\ValidationException;
use App\Exceptions\PaymentMethods\TransactionNotFoundException;
use App\Models\Order;
use App\Models\PaymentMethod\PayPalFriendsFamily;
use App\Models\Transaction;
use App\Models\User;
use App\ValueObjects\Money;
use Lorisleiva\Actions\Concerns\AsAction;

class PayTransaction
{
    use AsAction;

    /**
     * @throws TransactionNotFoundException
     * @throws ValidationException
     */
    public function handle(array $transactionData): void
    {
        try {
            $this->validateTransactionData($transactionData);
        } catch (ReferenceValidationException $referenceValidationException) {
            $likelyTransaction = $this->guessTransactionBasedOnNote($transactionData);
        }

        $paymentAmount = Money::fromDecimalString($transactionData[PayPalFriendsFamily::amountColumn()]);
        // KI-02 fix: the lookup no longer filters by amount - a genuine underpayment can never
        // satisfy "stored amount <= just-paid amount", so that filter structurally excluded
        // underpayment from ever being found at all. Matching is by reference + open state only;
        // the payment amount is classified against the transaction's required amount afterwards.
        $transaction = $likelyTransaction ?? Transaction::where('reference', '=', $transactionData[PayPalFriendsFamily::noteColumn()])
            ->whereIn('state', [
                Transaction::STATE_INIT,
                Transaction::STATE_ORDERED
            ])->orderBy('created_at', 'ASC')->first();

        if ($transaction === null && app()->isLocal()) {
            throw new TransactionNotFoundException('Transaction not found. ' . json_encode([
                'reference' => $transactionData[PayPalFriendsFamily::noteColumn()],
                'amount' => $paymentAmount->toDecimalString(),
            ]));
        }
        if ($transaction === null) {
            return;
        }
        // The same CSV row can reach us again through an overlapping PayPal export; the row hash
        // makes re-applying it a no-op instead of counting the same payment twice.
        ApplyPaymentToTransaction::run($transaction, $paymentAmount, $transactionData, 'ff-' . sha1(json_encode($transactionData)));
    }

    private function validateTransactionData(array $transactionData): void
    {
        if (array_key_exists(PayPalFriendsFamily::amountColumn(), $transactionData) === false) {
            throw new ValidationException(PayPalFriendsFamily::amountColumn() . ' is missing. ' . json_encode(['row' => $transactionData]));
        }
        if (is_numeric(str_replace(',', '.', $transactionData[PayPalFriendsFamily::amountColumn()])) === false) {
            throw new ValidationException(PayPalFriendsFamily::amountColumn() . ' is not numeric. ' . json_encode(['row' => $transactionData]));
        }
        if (array_key_exists(PayPalFriendsFamily::noteColumn(), $transactionData) === false) {
            throw new ValidationException(PayPalFriendsFamily::noteColumn() . ' is missing. ' . json_encode(['row' => $transactionData]));
        }
        if (strlen($transactionData[PayPalFriendsFamily::noteColumn()]) !== Transaction::REFERENCE_LENGTH) {
            throw new ReferenceValidationException(PayPalFriendsFamily::noteColumn() . ' is not 10 chars long. ' . json_encode(['row' => $transactionData]));
        }
    }

    private function guessTransactionBasedOnNote($transactionData)
    {
        $user = User::where('email', '=', $transactionData[PayPalFriendsFamily::userEmailColumn()])->first();
        if ($user === null) {
            return null;
        }
        $orders = $user->userOrders()->whereIn('status', [Order::STATUS_INITIALIZED, Order::STATUS_PROCESSING])->get();
        $likelyTransaction = null;
        foreach ($orders as $order) {
            if ($this->scanReferences($order->transaction, $transactionData) === true) {
                $likelyTransaction = $order->transaction;
                $likelyTransaction->comment = 'Matched based on note';
                break;
            }
        }
        if ($likelyTransaction !== null) {
            if (in_array($likelyTransaction->state, [Transaction::STATE_INIT, Transaction::STATE_ORDERED]) === false) {
                throw new ValidationException('Most likely Transaction is in wrong state!');
            }
        }

        return $likelyTransaction;
    }

    private function scanReferences($transaction, $transactionData): bool
    {
        return \Str::contains($transactionData[PayPalFriendsFamily::noteColumn()], $transaction->reference);
    }
}
