<?php

namespace App\Actions\Payment\PayPalFriendsFamily;

use App\Exceptions\PaymentMethods\PayPalFriendsFamily\ValidationException;
use App\Exceptions\PaymentMethods\TransactionNotFoundException;
use App\Models\Order;
use App\Models\PaymentMethod\PayPalFriendsFamily;
use App\Models\Transaction;
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
        $this->validateTransactionData($transactionData);
        $transactionAmount = str_replace(',', '.', $transactionData[PayPalFriendsFamily::AMOUNT_COLUMN]);
        $transaction = Transaction::where('reference', '=', $transactionData[PayPalFriendsFamily::NOTE_COLUMN])
            ->whereIn('state', [
                Transaction::STATE_INIT,
                Transaction::STATE_ORDERED
            ])->where('amount', '=', $transactionAmount)->first();

        if ($transaction === null && app()->isLocal()) {
            throw new TransactionNotFoundException('Transaction not found. ' . json_encode([
                'reference' => $transactionData[PayPalFriendsFamily::NOTE_COLUMN],
                'amount' => $transactionAmount ?? null,
            ]));
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
            throw new ValidationException(PayPalFriendsFamily::NOTE_COLUMN . ' is not 10 chars long. ' . json_encode(['row' => $transactionData]));
        }
    }
}
