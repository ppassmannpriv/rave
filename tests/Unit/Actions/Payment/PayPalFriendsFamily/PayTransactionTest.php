<?php

namespace Tests\Unit\Actions\Payment\PayPalFriendsFamily;

use App\Actions\Payment\PayPalFriendsFamily\PayTransaction;
use App\Exceptions\PaymentMethods\PayPalFriendsFamily\ValidationException;
use App\Exceptions\PaymentMethods\TransactionNotFoundException;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\Transaction\PartialTransaction;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Tests for the PayPal Friends & Family reconciliation matcher
 * (docs/architecture/05-roadmap.md Phase 1.1 then 5.1, docs/architecture/01-known-issues.md KI-02/KI-17).
 *
 * Originally written as characterization tests pinning the pre-fix (buggy) behavior; KI-02
 * (underpayment leaving no trace) and KI-17 (fragile money comparison) are now fixed via
 * App\ValueObjects\Money + App\Casts\MoneyCast wired into Transaction/PartialTransaction, and
 * PayTransaction::handle rewritten to compare cumulative Money totals instead of raw `===`. The
 * tests below assert the FIXED behavior and double as regression coverage for both bugs.
 *
 * Environment note (see docs/architecture/01-known-issues.md KI-18): this project's full
 * migration history is not SQLite-compatible (one migration drops a foreign key, which SQLite's
 * schema builder cannot do without a table rebuild), and this dev sandbox has no Docker/MySQL
 * available. This test therefore builds a minimal, hand-written SQLite schema covering only the
 * columns this action actually touches, rather than running RefreshDatabase against the real
 * migrations. This is sufficient to verify the Action class's own PHP-level logic (which is what
 * these tests target), but is NOT a substitute for CI running the real migrations against real
 * MySQL per docs/architecture/03-testing-strategy.md - re-run this suite there once available.
 */
class PayTransactionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('alias');
            $table->string('name');
            $table->string('FQN');
            $table->text('description')->nullable();
            $table->boolean('active');
            $table->boolean('admin_only')->default(false);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status');
            $table->decimal('price', 15, 2)->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('payment_method_id');
            $table->integer('order_id');
            $table->string('reference');
            $table->string('external_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('state');
            $table->string('comment')->nullable();
            $table->timestamps();
        });

        Schema::create('transaction_partials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->string('external_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->json('raw')->nullable();
            $table->timestamps();
        });
    }

    /** @return array{0: Order, 1: Transaction} */
    private function makeOrderWithTransaction(string $reference, string $amount, string $orderStatus = Order::STATUS_INITIALIZED, string $transactionState = Transaction::STATE_INIT, ?User $user = null): array
    {
        $paymentMethod = PaymentMethod::create([
            'alias' => 'paypal_ff',
            'name' => 'PayPal Friends&Family',
            'FQN' => 'PayPalFriendsFamily',
            'active' => true,
        ]);

        $order = Order::create([
            'user_id' => $user?->id,
            'status' => $orderStatus,
        ]);

        $transaction = Transaction::create([
            'payment_method_id' => $paymentMethod->id,
            'order_id' => $order->id,
            'reference' => $reference,
            'amount' => $amount,
            'state' => $transactionState,
        ]);
        $order->transaction()->associate($transaction);
        $order->save();

        return [$order, $transaction];
    }

    /**
     * KI-17 (confirmed manifestation): `formatTransactionAmount()` only does a comma-to-dot
     * replace and always returns a PHP string. The matcher then compares that string to
     * `$transaction->amount` with strict `===`, which requires identical type AND value.
     * FIXED via App\ValueObjects\Money: comparisons now go through exact integer-cents equality
     * instead of a type-sensitive `===` on raw driver output, so an exact payment is correctly
     * recognized regardless of whether the DB returns the amount as a string, int, or float, and
     * regardless of how many decimal places the CSV amount is formatted with.
     */
    public function test_exact_match_marks_transaction_paid_and_order_paid(): void
    {
        [$order, $transaction] = $this->makeOrderWithTransaction('ABCDEFGH12', '25.00');

        PayTransaction::make()->handle([
            'Brutto' => '25,00',
            'Hinweis' => 'ABCDEFGH12',
            'Absender E-Mail-Adresse' => 'buyer@example.com',
        ]);

        $transaction->refresh();
        $order->refresh();

        $this->assertSame(Transaction::STATE_PAID, $transaction->state);
        $this->assertSame(Order::STATUS_PAID, $order->status);
    }

    public function test_exact_match_still_works_when_csv_amount_has_fewer_decimal_places_ki17_regression(): void
    {
        [$order, $transaction] = $this->makeOrderWithTransaction('ABCDEFGH12', '25.50');

        PayTransaction::make()->handle([
            'Brutto' => '25,5',
            'Hinweis' => 'ABCDEFGH12',
            'Absender E-Mail-Adresse' => 'buyer@example.com',
        ]);

        $transaction->refresh();
        $order->refresh();

        $this->assertSame(Transaction::STATE_PAID, $transaction->state);
        $this->assertSame(Order::STATUS_PAID, $order->status, 'A payment formatted with fewer decimal places than the stored amount must still match exactly - KI-17 regression guard.');
    }

    public function test_overpayment_marks_paid_but_order_processing_with_comment(): void
    {
        [$order, $transaction] = $this->makeOrderWithTransaction('ABCDEFGH12', '20.00');

        PayTransaction::make()->handle([
            'Brutto' => '25,00',
            'Hinweis' => 'ABCDEFGH12',
            'Absender E-Mail-Adresse' => 'buyer@example.com',
        ]);

        $transaction->refresh();
        $order->refresh();

        $this->assertSame(Transaction::STATE_PAID, $transaction->state);
        $this->assertSame(Order::STATUS_PROCESSING, $order->status);
        $this->assertSame('Customer paid too much!', $transaction->comment);
    }

    /**
     * FIXED: the lookup no longer filters by amount (it matches by reference + open state only),
     * so an underpayment IS found and recorded - a PartialTransaction audit row is created and
     * the transaction gets a visible "still outstanding" comment, instead of KI-02's original
     * zero-trace silence. The transaction stays open (state unchanged) so a follow-up top-up
     * payment can still complete it - see the cumulative top-up test below.
     */
    public function test_underpayment_is_recorded_with_a_visible_remaining_balance(): void
    {
        [$order, $transaction] = $this->makeOrderWithTransaction('ABCDEFGH12', '25.00', Order::STATUS_INITIALIZED, Transaction::STATE_ORDERED);

        PayTransaction::make()->handle([
            'Brutto' => '10,00',
            'Hinweis' => 'ABCDEFGH12',
            'Absender E-Mail-Adresse' => 'buyer@example.com',
        ]);

        $transaction->refresh();
        $order->refresh();

        $this->assertSame(Transaction::STATE_ORDERED, $transaction->state);
        $this->assertSame(Order::STATUS_INITIALIZED, $order->status);
        $this->assertSame(1, PartialTransaction::where('transaction_id', $transaction->id)->count());
        $this->assertSame('Partial payment received, 15.00 still outstanding.', $transaction->comment);
    }

    public function test_a_second_top_up_payment_completes_a_previously_underpaid_transaction(): void
    {
        [$order, $transaction] = $this->makeOrderWithTransaction('ABCDEFGH12', '25.00', Order::STATUS_INITIALIZED, Transaction::STATE_ORDERED);

        PayTransaction::make()->handle([
            'Brutto' => '10,00',
            'Hinweis' => 'ABCDEFGH12',
            'Absender E-Mail-Adresse' => 'buyer@example.com',
        ]);
        PayTransaction::make()->handle([
            'Brutto' => '15,00',
            'Hinweis' => 'ABCDEFGH12',
            'Absender E-Mail-Adresse' => 'buyer@example.com',
        ]);

        $transaction->refresh();
        $order->refresh();

        $this->assertSame(Transaction::STATE_PAID, $transaction->state);
        $this->assertSame(Order::STATUS_PAID, $order->status);
        $this->assertSame(2, PartialTransaction::where('transaction_id', $transaction->id)->count());
    }

    public function test_column_names_are_read_from_config(): void
    {
        config([
            'paypal_ff.columns.amount' => 'Gross',
            'paypal_ff.columns.note' => 'Note',
            'paypal_ff.columns.user_email' => 'Sender Email',
        ]);
        [$order, $transaction] = $this->makeOrderWithTransaction('ABCDEFGH12', '25.00');

        PayTransaction::make()->handle([
            'Gross' => '25.00',
            'Note' => 'ABCDEFGH12',
            'Sender Email' => 'buyer@example.com',
        ]);

        $this->assertSame(Transaction::STATE_PAID, $transaction->refresh()->state);
        $this->assertSame(Order::STATUS_PAID, $order->refresh()->status);
    }

    public function test_the_same_csv_row_processed_twice_is_applied_only_once(): void
    {
        [$order, $transaction] = $this->makeOrderWithTransaction('ABCDEFGH12', '20.00', Order::STATUS_INITIALIZED, Transaction::STATE_ORDERED);
        $row = [
            'Brutto' => '10,00',
            'Hinweis' => 'ABCDEFGH12',
            'Absender E-Mail-Adresse' => 'buyer@example.com',
            'Transaktionscode' => 'TX123',
        ];

        PayTransaction::make()->handle($row);
        PayTransaction::make()->handle($row);

        $transaction->refresh();
        $this->assertSame(Transaction::STATE_ORDERED, $transaction->state, 'a replayed 10.00 row must not complete a 20.00 transaction');
        $this->assertSame(Order::STATUS_INITIALIZED, $order->refresh()->status);
        $this->assertSame(1, PartialTransaction::where('transaction_id', $transaction->id)->count());
        $this->assertSame('Partial payment received, 10.00 still outstanding.', $transaction->comment);
    }

    public function test_two_different_rows_with_the_same_amount_are_both_applied(): void
    {
        [, $transaction] = $this->makeOrderWithTransaction('ABCDEFGH12', '20.00', Order::STATUS_INITIALIZED, Transaction::STATE_ORDERED);
        $base = ['Brutto' => '10,00', 'Hinweis' => 'ABCDEFGH12', 'Absender E-Mail-Adresse' => 'buyer@example.com'];

        PayTransaction::make()->handle($base + ['Transaktionscode' => 'TX1']);
        PayTransaction::make()->handle($base + ['Transaktionscode' => 'TX2']);

        $this->assertSame(Transaction::STATE_PAID, $transaction->refresh()->state);
        $this->assertSame(2, PartialTransaction::where('transaction_id', $transaction->id)->count());
    }

    public function test_missing_amount_column_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/Brutto is missing/');

        PayTransaction::make()->handle([
            'Hinweis' => 'ABCDEFGH12',
            'Absender E-Mail-Adresse' => 'buyer@example.com',
        ]);
    }

    public function test_non_numeric_amount_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/Brutto is not numeric/');

        PayTransaction::make()->handle([
            'Brutto' => 'not-a-number',
            'Hinweis' => 'ABCDEFGH12',
            'Absender E-Mail-Adresse' => 'buyer@example.com',
        ]);
    }

    /**
     * When the reference note isn't exactly 10 characters, the matcher falls back to guessing
     * by sender email among that user's pending orders, and matches if the (too-long/noisy) note
     * contains the real order reference as a substring.
     */
    public function test_wrong_length_reference_falls_back_to_email_based_fuzzy_match(): void
    {
        $user = User::create([
            'name' => 'Buyer',
            'email' => 'buyer@example.com',
            'password' => 'irrelevant',
        ]);

        [$order, $transaction] = $this->makeOrderWithTransaction('ABCDEFGH12', '25.00', Order::STATUS_INITIALIZED, Transaction::STATE_ORDERED, $user);

        PayTransaction::make()->handle([
            'Brutto' => '25,00',
            'Hinweis' => 'Ticket order ABCDEFGH12 thanks!',
            'Absender E-Mail-Adresse' => 'buyer@example.com',
        ]);

        $transaction->refresh();
        $order->refresh();

        $this->assertSame(Transaction::STATE_PAID, $transaction->state);
        $this->assertSame(Order::STATUS_PAID, $order->status);
        $this->assertSame('Matched based on note', $transaction->comment);
    }

    public function test_wrong_length_reference_with_unknown_email_finds_no_match_and_returns_silently(): void
    {
        [$order, $transaction] = $this->makeOrderWithTransaction('ABCDEFGH12', '25.00');

        // Default test env (phpunit.xml APP_ENV=testing) is not "local", so no exception is
        // thrown for an unmatched payment - it is silently dropped. This is itself a notable
        // gap (an unmatched real payment produces zero signal anywhere), tracked separately.
        PayTransaction::make()->handle([
            'Brutto' => '25,00',
            'Hinweis' => 'a note with no reference in it at all',
            'Absender E-Mail-Adresse' => 'nobody-known@example.com',
        ]);

        $transaction->refresh();
        $order->refresh();

        $this->assertSame(Transaction::STATE_INIT, $transaction->state);
        $this->assertSame(Order::STATUS_INITIALIZED, $order->status);
    }

    /**
     * Once a transaction is paid, the lookup query's `whereIn('state', [initialized, ordered])`
     * filter naturally excludes it from being matched again - a second CSV row for the same
     * reference (e.g. the same file processed twice) silently finds no transaction and no-ops,
     * rather than double-applying the payment. This is a real, already-working idempotency
     * property worth pinning so a future refactor doesn't accidentally remove it.
     */
    public function test_already_paid_transaction_is_not_matched_again(): void
    {
        [$order, $transaction] = $this->makeOrderWithTransaction('ABCDEFGH12', '25.00', Order::STATUS_PAID, Transaction::STATE_PAID);

        PayTransaction::make()->handle([
            'Brutto' => '25,00',
            'Hinweis' => 'ABCDEFGH12',
            'Absender E-Mail-Adresse' => 'buyer@example.com',
        ]);

        $transaction->refresh();
        $order->refresh();

        $this->assertSame(Transaction::STATE_PAID, $transaction->state);
        $this->assertSame(Order::STATUS_PAID, $order->status);
        $this->assertSame(0, PartialTransaction::where('transaction_id', $transaction->id)->count());
    }
}
