<?php

namespace App\Actions\Payment\PayPalFriendsFamily;

use App\Models\PaymentMethod\PayPalFriendsFamily;
use App\Models\Transaction;
use Lorisleiva\Actions\Concerns\AsAction;

class PayTransaction
{
    use AsAction;

    public function handle(array $transactionData): ?Transaction
    {
        $transaction = Transaction::where('reference', '=', $transactionData[PayPalFriendsFamily::NOTE_COLUMN])
            ->whereIn('state', [
                Transaction::STATE_INIT,
                Transaction::STATE_ORDERED
            ])->where('amount', '=', str_replace(',', '.', $transactionData[PayPalFriendsFamily::AMOUNT_COLUMN]))->first();
        if ($transaction === null) {
            return null;
        }

        dd($transaction);
        return $transaction;
    }
}
