<?php

namespace App\Actions\Payment;

use App\Models\Transaction;
use Lorisleiva\Actions\Concerns\AsAction;

class PayTransaction
{
    use AsAction;

    public function handle(Transaction $transaction): void
    {

    }
}
