<?php

namespace Tests\Unit\Actions\Payment\PayPal;

use App\Actions\Payment\PayPal\CapturePayPalOrder;
use App\Actions\Payment\PayPal\StartPayPalPayment;
use App\Models\Order;
use App\Models\PayPalWebhookEvent;
use App\Models\PaymentMethod;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Same hand-built SQLite schema approach (and caveat) as PayTransactionTest: this sandbox has no
 * MySQL and the real migration history is not SQLite-compatible (KI-18). These tests verify the
 * PHP-level flow against faked PayPal HTTP, not the migration file itself.
 */
class PayPalPaymentFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        config([
            'paypal.mode' => 'sandbox',
            'paypal.client_id' => 'client',
            'paypal.client_secret' => 'secret',
            'paypal.webhook_id' => 'WH-1',
            'paypal.currency' => 'EUR',
        ]);

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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('role_user', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
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
            $table->unique(['transaction_id', 'external_id']);
        });
        Schema::create('paypal_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique();
            $table->string('event_type');
            $table->json('payload');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /** @return array{0: Order, 1: Transaction} */
    private function makePayPalTransaction(string $amount = '25.00', string $alias = 'paypal'): array
    {
        $paymentMethod = PaymentMethod::create(['alias' => $alias, 'name' => 'PayPal', 'FQN' => 'x', 'active' => true]);
        $order = Order::create(['status' => Order::STATUS_INITIALIZED]);
        $transaction = Transaction::create([
            'payment_method_id' => $paymentMethod->id,
            'order_id' => $order->id,
            'reference' => 'ABCDEFGH12',
            'amount' => $amount,
            'state' => Transaction::STATE_INIT,
        ]);
        $order->transaction()->associate($transaction);
        $order->save();

        return [$order, $transaction];
    }

    private function capture(string $value = '25.00', string $currency = 'EUR', string $status = 'COMPLETED', string $id = 'CAP1'): array
    {
        return ['id' => $id, 'status' => $status, 'custom_id' => 'ABCDEFGH12', 'amount' => ['currency_code' => $currency, 'value' => $value]];
    }

    private function fakePayPal(array $capture): void
    {
        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            '*/v2/checkout/orders' => Http::response(['id' => 'ORDER1', 'links' => [['rel' => 'payer-action', 'href' => 'https://paypal.test/approve']]], 201),
            '*/v2/checkout/orders/ORDER1/capture' => Http::response(['id' => 'ORDER1', 'status' => 'COMPLETED', 'purchase_units' => [['payments' => ['captures' => [$capture]]]]]),
            '*/v1/notifications/verify-webhook-signature' => Http::response(['verification_status' => 'SUCCESS']),
        ]);
    }

    public function test_starting_a_payment_stores_the_paypal_order_id_and_leaves_the_state_open_for_the_reminder_lifecycle(): void
    {
        [, $transaction] = $this->makePayPalTransaction();
        $this->fakePayPal($this->capture());

        $url = StartPayPalPayment::run($transaction);

        $this->assertSame('https://paypal.test/approve', $url);
        $transaction->refresh();
        $this->assertSame('ORDER1', $transaction->external_id);
        $this->assertSame(Transaction::STATE_INIT, $transaction->state);
    }

    public function test_a_completed_exact_capture_marks_transaction_and_order_paid(): void
    {
        [$order, $transaction] = $this->makePayPalTransaction();
        $transaction->update(['external_id' => 'ORDER1']);
        $this->fakePayPal($this->capture());

        CapturePayPalOrder::run($transaction);

        $this->assertSame(Transaction::STATE_PAID, $transaction->refresh()->state);
        $this->assertSame(Order::STATUS_PAID, $order->refresh()->status);
        $this->assertSame(1, $transaction->partialTransactions()->count());
    }

    public function test_capturing_twice_does_not_double_apply_the_payment(): void
    {
        [, $transaction] = $this->makePayPalTransaction();
        $transaction->update(['external_id' => 'ORDER1']);
        $this->fakePayPal($this->capture());

        CapturePayPalOrder::run($transaction);
        CapturePayPalOrder::run($transaction->refresh());

        $this->assertSame(1, $transaction->partialTransactions()->count());
        $this->assertSame(Transaction::STATE_PAID, $transaction->refresh()->state);
    }

    public function test_a_capture_in_the_wrong_currency_is_not_counted_as_payment(): void
    {
        [$order, $transaction] = $this->makePayPalTransaction();
        $transaction->update(['external_id' => 'ORDER1']);
        $this->fakePayPal($this->capture('25.00', 'USD'));

        CapturePayPalOrder::run($transaction);

        $transaction->refresh();
        $this->assertSame(Transaction::STATE_INIT, $transaction->state);
        $this->assertSame(0, $transaction->partialTransactions()->count());
        $this->assertStringContainsString('unexpected currency', $transaction->comment);
    }

    public function test_a_pending_capture_is_not_counted_as_payment(): void
    {
        [, $transaction] = $this->makePayPalTransaction();
        $transaction->update(['external_id' => 'ORDER1']);
        $this->fakePayPal($this->capture('25.00', 'EUR', 'PENDING'));

        CapturePayPalOrder::run($transaction);

        $this->assertSame(0, $transaction->partialTransactions()->count());
        $this->assertSame(Transaction::STATE_INIT, $transaction->refresh()->state);
    }

    private function webhookEvent(string $id = 'EVT1', string $type = 'PAYMENT.CAPTURE.COMPLETED'): array
    {
        return ['id' => $id, 'event_type' => $type, 'resource' => $this->capture()];
    }

    public function test_verified_capture_webhook_marks_paid_and_a_redelivery_is_a_no_op(): void
    {
        [$order, $transaction] = $this->makePayPalTransaction();
        $this->fakePayPal($this->capture());

        $this->postJson('/webhooks/paypal', $this->webhookEvent())->assertOk();
        $this->postJson('/webhooks/paypal', $this->webhookEvent())->assertOk();

        $this->assertSame(Transaction::STATE_PAID, $transaction->refresh()->state);
        $this->assertSame(Order::STATUS_PAID, $order->refresh()->status);
        $this->assertSame(1, $transaction->partialTransactions()->count());
        $this->assertSame(1, PayPalWebhookEvent::count());
        $this->assertNotNull(PayPalWebhookEvent::first()->processed_at);
    }

    public function test_capture_completed_webhook_after_return_capture_does_not_double_apply(): void
    {
        [, $transaction] = $this->makePayPalTransaction();
        $transaction->update(['external_id' => 'ORDER1']);
        $this->fakePayPal($this->capture());

        CapturePayPalOrder::run($transaction);
        $this->postJson('/webhooks/paypal', $this->webhookEvent())->assertOk();

        $this->assertSame(1, $transaction->partialTransactions()->count());
    }

    public function test_approved_webhook_captures_the_order(): void
    {
        [, $transaction] = $this->makePayPalTransaction();
        $transaction->update(['external_id' => 'ORDER1']);
        $this->fakePayPal($this->capture());

        $this->postJson('/webhooks/paypal', ['id' => 'EVT2', 'event_type' => 'CHECKOUT.ORDER.APPROVED', 'resource' => ['id' => 'ORDER1']])->assertOk();

        $this->assertSame(Transaction::STATE_PAID, $transaction->refresh()->state);
    }

    public function test_webhook_with_failed_signature_verification_is_rejected_and_not_applied(): void
    {
        [, $transaction] = $this->makePayPalTransaction();
        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            '*/v1/notifications/verify-webhook-signature' => Http::response(['verification_status' => 'FAILURE']),
        ]);

        $this->postJson('/webhooks/paypal', $this->webhookEvent())->assertStatus(400);

        $this->assertSame(Transaction::STATE_INIT, $transaction->refresh()->state);
        $this->assertSame(0, PayPalWebhookEvent::count());
    }

    public function test_webhook_for_a_friends_and_family_transaction_is_ignored(): void
    {
        [, $transaction] = $this->makePayPalTransaction('25.00', 'paypal_ff');
        $this->fakePayPal($this->capture());

        $this->postJson('/webhooks/paypal', $this->webhookEvent())->assertOk();

        $this->assertSame(Transaction::STATE_INIT, $transaction->refresh()->state);
        $this->assertSame(0, $transaction->partialTransactions()->count());
    }

    public function test_return_url_captures_and_redirects_to_success(): void
    {
        [, $transaction] = $this->makePayPalTransaction();
        $transaction->update(['external_id' => 'ORDER1']);
        $this->fakePayPal($this->capture());

        $this->get('/paypal/return/ABCDEFGH12')->assertRedirect('/cart/success');

        $this->assertSame(Transaction::STATE_PAID, $transaction->refresh()->state);
    }

    public function test_return_url_does_not_expose_friends_and_family_transactions(): void
    {
        $this->makePayPalTransaction('25.00', 'paypal_ff');

        $this->get('/paypal/return/ABCDEFGH12')->assertNotFound();
    }

    public function test_customers_do_not_see_admin_only_or_inactive_methods_but_admins_see_admin_only(): void
    {
        PaymentMethod::create(['alias' => 'paypal_ff', 'name' => 'F&F', 'FQN' => 'x', 'active' => true]);
        PaymentMethod::create(['alias' => 'paypal', 'name' => 'PayPal', 'FQN' => 'x', 'active' => false, 'admin_only' => true]);
        PaymentMethod::create(['alias' => 'old', 'name' => 'Old', 'FQN' => 'x', 'active' => false]);
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'x']);
        $role = new Role(['title' => 'Admin']);
        $role->id = 1;
        $role->save();
        $admin->roles()->attach(1);
        $customer = User::create(['name' => 'Customer', 'email' => 'c@example.com', 'password' => 'x']);

        $this->assertSame(['paypal_ff'], PaymentMethod::selectableBy(null)->pluck('alias')->all());
        $this->assertSame(['paypal_ff'], PaymentMethod::selectableBy($customer)->pluck('alias')->all());
        $this->assertEqualsCanonicalizing(['paypal_ff', 'paypal'], PaymentMethod::selectableBy($admin)->pluck('alias')->all());
    }
}
