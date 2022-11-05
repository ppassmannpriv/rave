<?php

namespace App\Actions\Payment\PayPalFriendsFamily;

use App\Exceptions\PaymentMethods\PayPalFriendsFamily\ReferenceValidationException;
use App\Exceptions\PaymentMethods\PayPalFriendsFamily\ValidationException;
use App\Exceptions\PaymentMethods\TransactionNotFoundException;
use App\Models\Order;
use App\Models\PaymentMethod\PayPalFriendsFamily;
use App\Models\Transaction;
use App\Models\User;
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

        $transactionAmount = $this->formatTransactionAmount($transactionData[PayPalFriendsFamily::AMOUNT_COLUMN]);
        $transaction = $likelyTransaction ?? Transaction::where('reference', '=', $transactionData[PayPalFriendsFamily::NOTE_COLUMN])
            ->whereIn('state', [
                Transaction::STATE_INIT,
                Transaction::STATE_ORDERED
            ])->where('amount', '<=', $transactionAmount)->orderBy('created_at', 'ASC')->first();

        if ($transaction === null && app()->isLocal()) {
            throw new TransactionNotFoundException('Transaction not found. ' . json_encode([
                'reference' => $transactionData[PayPalFriendsFamily::NOTE_COLUMN],
                'amount' => $transactionAmount ?? null,
            ]));
        }
        if ($transaction === null) {
            return;
        }
        $partialTransaction = Transaction\PartialTransaction::create([
            'amount' => $transactionAmount,
            'raw' => json_encode($transactionData)
        ]);
        $transaction->partialTransactions()->save($partialTransaction);
        if ($transactionAmount === $transaction->amount) {
            $transaction->state = Transaction::STATE_PAID;
            if ($transaction->order !== null) {
                $transaction->order->status = Order::STATUS_PAID;
                $transaction->order->save();
            }
        } else if ($transactionAmount >= $transaction->amount) {
            $transaction->state = Transaction::STATE_PAID;
            if ($transaction->order !== null) {
                $transaction->order->status = Order::STATUS_PROCESSING;
                if ($transaction->comment !== null) {
                    $transaction->comment = $transaction->comment . ' - Customer paid too much!';
                } else {
                    $transaction->comment = 'Customer paid too much!';
                }
                $transaction->save();
                $transaction->order->save();
            }
        }
        $transaction->save();
    }

    private function validateTransactionData(array $transactionData): void
    {
        if (array_key_exists(PayPalFriendsFamily::AMOUNT_COLUMN, $transactionData) === false) {
            throw new ValidationException(PayPalFriendsFamily::AMOUNT_COLUMN . ' is missing. ' . json_encode(['row' => $transactionData]));
        }
        if (is_numeric(str_replace(',', '.', $transactionData[PayPalFriendsFamily::AMOUNT_COLUMN])) === false) {
            throw new ValidationException(PayPalFriendsFamily::AMOUNT_COLUMN . ' is not numeric. ' . json_encode(['row' => $transactionData]));
        }
        if (array_key_exists(PayPalFriendsFamily::NOTE_COLUMN, $transactionData) === false) {
            throw new ValidationException(PayPalFriendsFamily::NOTE_COLUMN . ' is missing. ' . json_encode(['row' => $transactionData]));
        }
        if (strlen($transactionData[PayPalFriendsFamily::NOTE_COLUMN]) !== Transaction::REFERENCE_LENGTH) {
            throw new ReferenceValidationException(PayPalFriendsFamily::NOTE_COLUMN . ' is not 10 chars long. ' . json_encode(['row' => $transactionData]));
        }
    }

    private function guessTransactionBasedOnNote($transactionData)
    {
        $user = User::where('email', '=', $transactionData[PayPalFriendsFamily::USER_EMAIL_COLUMN])->first();
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
        return \Str::contains($transactionData[PayPalFriendsFamily::NOTE_COLUMN], $transaction->reference);
    }

    private function formatTransactionAmount($transactionAmount)
    {
        return str_replace(',', '.', $transactionAmount);
    }
}
