<?php

namespace Tests\Unit\Console\Commands;

use App\Mail\OrderCancelled;
use App\Mail\PaymentReminder;
use App\Models\EventTicket;
use App\Models\Order;
use App\Models\Order\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Elapsed-time regression tests for `orders:payment-check` (roadmap item 3.4).
 *
 * Every window edge is exercised from both sides with a frozen clock so that a future
 * Carbon 3 / Laravel 12 upgrade cannot silently flip an "older than X days" comparison.
 *
 * Same hand-built SQLite schema approach (and caveat) as PayTransactionTest (KI-18).
 */
class PaymentReminderCheckTest extends TestCase
{
    private const NOW = '2026-03-10 12:00:00';

    private int $userCount = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse(self::NOW));

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
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
        Schema::create('event_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_type');
            $table->decimal('price', 15, 2);
            $table->datetime('from')->nullable();
            $table->datetime('to')->nullable();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->integer('stock')->default(100);
            $table->integer('cap')->default(100);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->integer('qty')->default(1);
            $table->decimal('single_price', 15, 2);
            $table->decimal('row_price', 15, 2);
            $table->unsignedBigInteger('cart_item_id')->nullable();
            $table->unsignedInteger('event_ticket_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('event_ticket_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->unsignedBigInteger('event_ticket_id');
            $table->unsignedBigInteger('order_item_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Mail::fake();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * Inserted through the query builder so EventTicket's `created` event (which generates
     * ticket codes) does not fire.
     */
    private function makeEventTicket(int $stock = 10): EventTicket
    {
        $id = DB::table('event_tickets')->insertGetId([
            'ticket_type' => 'regular',
            'price' => '25.00',
            'stock' => $stock,
            'cap' => 100,
        ]);

        return EventTicket::findOrFail($id);
    }

    /** @return array{0: Order, 1: Transaction, 2: EventTicket} */
    private function makeOrder(
        Carbon $createdAt,
        string $orderStatus = Order::STATUS_INITIALIZED,
        string $transactionState = Transaction::STATE_INIT,
        string $paymentAlias = 'paypal_ff',
        int $qty = 2
    ): array {
        $this->userCount++;
        $user = User::create([
            'name' => 'Buyer '.$this->userCount,
            'email' => 'buyer'.$this->userCount.'@example.com',
            'password' => 'irrelevant',
        ]);
        $paymentMethod = PaymentMethod::firstOrCreate(
            ['alias' => $paymentAlias],
            ['name' => $paymentAlias, 'FQN' => 'x', 'active' => true]
        );

        $order = Order::create([
            'user_id' => $user->id,
            'status' => $orderStatus,
            'created_at' => $createdAt,
        ]);
        $transaction = Transaction::create([
            'payment_method_id' => $paymentMethod->id,
            'order_id' => $order->id,
            'reference' => 'REF'.str_pad((string) $order->id, 7, '0', STR_PAD_LEFT),
            'amount' => '50.00',
            'state' => $transactionState,
        ]);
        $order->transaction()->associate($transaction);
        $order->save();

        $eventTicket = $this->makeEventTicket();
        OrderItem::create([
            'order_id' => $order->id,
            'qty' => $qty,
            'single_price' => '25.00',
            'row_price' => '50.00',
            'event_ticket_id' => $eventTicket->id,
        ]);

        return [$order, $transaction, $eventTicket];
    }

    private function ago(int $days, int $hours = 0, int $minutes = 0, int $seconds = 0): Carbon
    {
        return Carbon::now()->subDays($days)->subHours($hours)->subMinutes($minutes)->subSeconds($seconds);
    }

    private function runCheck(): void
    {
        $this->artisan('orders:payment-check')->assertExitCode(0);
    }

    private function assertReminded(Order $order): void
    {
        Mail::assertSent(PaymentReminder::class, fn (PaymentReminder $mail) => $mail->order->is($order));
    }

    private function assertNotReminded(Order $order): void
    {
        Mail::assertNotSent(PaymentReminder::class, fn (PaymentReminder $mail) => $mail->order->is($order));
    }

    private function assertNotCancelled(Order $order, Transaction $transaction, EventTicket $eventTicket, string $status, string $state, int $stock = 10): void
    {
        Mail::assertNotSent(OrderCancelled::class, fn (OrderCancelled $mail) => $mail->order->is($order));
        $this->assertSame($status, $order->refresh()->status);
        $this->assertSame($state, $transaction->refresh()->state);
        $this->assertSame($stock, (int) $eventTicket->refresh()->stock);
    }

    private function assertCancelled(Order $order, Transaction $transaction, EventTicket $eventTicket, int $expectedStock = 12): void
    {
        Mail::assertSent(OrderCancelled::class, fn (OrderCancelled $mail) => $mail->order->is($order));
        $this->assertSame(Order::STATUS_CANCELLED, $order->refresh()->status);
        $this->assertSame(Transaction::STATE_CANCEL, $transaction->refresh()->state);
        $this->assertSame($expectedStock, (int) $eventTicket->refresh()->stock);
    }

    private function assertMailCounts(int $reminders, int $cancellations): void
    {
        foreach ([PaymentReminder::class => $reminders, OrderCancelled::class => $cancellations] as $mailable => $times) {
            $times === 0 ? Mail::assertNotSent($mailable) : Mail::assertSent($mailable, $times);
        }
        Mail::assertNothingQueued();
    }

    public function test_order_one_second_younger_than_five_days_is_neither_reminded_nor_cancelled(): void
    {
        [$order, $transaction, $ticket] = $this->makeOrder($this->ago(4, 23, 59, 59));

        $this->runCheck();

        Mail::assertNothingSent();
        $this->assertNotCancelled($order, $transaction, $ticket, Order::STATUS_INITIALIZED, Transaction::STATE_INIT);
    }

    public function test_order_exactly_five_days_old_is_reminded_but_not_cancelled(): void
    {
        [$order, $transaction, $ticket] = $this->makeOrder($this->ago(5));

        $this->runCheck();

        $this->assertReminded($order);
        $this->assertMailCounts(1, 0);
        $this->assertNotCancelled($order, $transaction, $ticket, Order::STATUS_INITIALIZED, Transaction::STATE_INIT);
    }

    public function test_order_five_and_a_half_days_old_is_reminded_but_not_cancelled(): void
    {
        [$order, $transaction, $ticket] = $this->makeOrder($this->ago(5, 12));

        $this->runCheck();

        $this->assertReminded($order);
        $this->assertMailCounts(1, 0);
        $this->assertNotCancelled($order, $transaction, $ticket, Order::STATUS_INITIALIZED, Transaction::STATE_INIT);
    }

    public function test_order_one_second_younger_than_six_days_is_reminded_but_not_cancelled(): void
    {
        [$order, $transaction, $ticket] = $this->makeOrder($this->ago(5, 23, 59, 59));

        $this->runCheck();

        $this->assertReminded($order);
        $this->assertMailCounts(1, 0);
        $this->assertNotCancelled($order, $transaction, $ticket, Order::STATUS_INITIALIZED, Transaction::STATE_INIT);
    }

    public function test_order_one_second_older_than_six_days_is_cancelled_restocked_and_not_reminded(): void
    {
        [$order, $transaction, $ticket] = $this->makeOrder($this->ago(6, 0, 0, 1));

        $this->runCheck();

        $this->assertNotReminded($order);
        $this->assertMailCounts(0, 1);
        $this->assertCancelled($order, $transaction, $ticket);
    }

    /**
     * The reminder window (created_at >= now-6d) and the cancel window (created_at <= now-6d)
     * are both inclusive, so an order created exactly six days ago to the second falls into
     * both: today it receives a payment reminder AND is cancelled in the same run.
     */
    public function test_order_exactly_six_days_old_is_both_reminded_and_cancelled_current_behaviour(): void
    {
        [$order, $transaction, $ticket] = $this->makeOrder($this->ago(6));

        $this->runCheck();

        $this->assertReminded($order);
        $this->assertMailCounts(1, 1);
        $this->assertCancelled($order, $transaction, $ticket);
    }

    public function test_paid_order_is_never_reminded_or_cancelled_even_when_ten_days_old(): void
    {
        [$order, $transaction, $ticket] = $this->makeOrder($this->ago(10), Order::STATUS_PAID, Transaction::STATE_PAID);

        $this->runCheck();

        Mail::assertNothingSent();
        $this->assertNotCancelled($order, $transaction, $ticket, Order::STATUS_PAID, Transaction::STATE_PAID);
    }

    public function test_already_cancelled_order_is_never_reminded_or_cancelled_again_even_when_ten_days_old(): void
    {
        [$order, $transaction, $ticket] = $this->makeOrder($this->ago(10), Order::STATUS_CANCELLED, Transaction::STATE_CANCEL);

        $this->runCheck();

        Mail::assertNothingSent();
        $this->assertNotCancelled($order, $transaction, $ticket, Order::STATUS_CANCELLED, Transaction::STATE_CANCEL);
    }

    public function test_friends_and_family_and_paypal_orders_are_reminded_the_same_way(): void
    {
        [$ffOrder, $ffTransaction, $ffTicket] = $this->makeOrder($this->ago(5, 12), paymentAlias: 'paypal_ff');
        [$ppOrder, $ppTransaction, $ppTicket] = $this->makeOrder($this->ago(5, 12), paymentAlias: 'paypal');

        $this->runCheck();

        $this->assertReminded($ffOrder);
        $this->assertReminded($ppOrder);
        $this->assertMailCounts(2, 0);
        $this->assertNotCancelled($ffOrder, $ffTransaction, $ffTicket, Order::STATUS_INITIALIZED, Transaction::STATE_INIT);
        $this->assertNotCancelled($ppOrder, $ppTransaction, $ppTicket, Order::STATUS_INITIALIZED, Transaction::STATE_INIT);
    }

    public function test_friends_and_family_and_paypal_orders_are_cancelled_the_same_way(): void
    {
        [$ffOrder, $ffTransaction, $ffTicket] = $this->makeOrder($this->ago(6, 0, 0, 1), paymentAlias: 'paypal_ff');
        [$ppOrder, $ppTransaction, $ppTicket] = $this->makeOrder($this->ago(6, 0, 0, 1), paymentAlias: 'paypal');

        $this->runCheck();

        $this->assertMailCounts(0, 2);
        $this->assertCancelled($ffOrder, $ffTransaction, $ffTicket);
        $this->assertCancelled($ppOrder, $ppTransaction, $ppTicket);
    }
}
