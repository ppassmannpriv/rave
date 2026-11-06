<?php

namespace Tests\Feature\Cart;

use App\Events\EventTicketCreatedEvent;
use App\Events\OrderEventTicketGenerationEvent;
use App\Mail\OrderCreated;
use App\Mail\OrderCreatedNotification;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\EventTicket;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CartService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Customer cart -> order lifecycle over the real HTTP routes. Same hand-built SQLite schema approach
 * (and caveat) as PayPalPaymentFlowTest: no MySQL in this sandbox and the migration history is not
 * SQLite compatible (KI-18). Only POST endpoints and the view-less success redirect are exercised, so
 * no Blade layout (navigation/CMS tables) is rendered.
 */
class CartOrderLifecycleTest extends TestCase
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
            'paypal.currency' => 'EUR',
        ]);
        Carbon::setTestNow('2026-06-01 12:00:00');
        Event::fake([OrderEventTicketGenerationEvent::class, EventTicketCreatedEvent::class]);
        Mail::fake();

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
        foreach (['roles', 'permissions'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->timestamps();
                $table->softDeletes();
            });
        }
        Schema::create('permission_role', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
        });
        Schema::create('role_user', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
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
        Schema::create('event_ticket_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->unsignedBigInteger('event_ticket_id')->nullable();
            $table->unsignedBigInteger('order_item_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('event_ticket_id');
            $table->integer('qty');
            $table->decimal('single_price', 15, 2);
            $table->decimal('row_price', 15, 2);
            $table->unsignedBigInteger('cart_id');
            $table->timestamps();
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
            $table->string('status')->default(Order::STATUS_INITIALIZED);
            $table->decimal('price', 15, 2)->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable();
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

        PaymentMethod::create(['alias' => 'paypal_ff', 'name' => 'PayPal F&F', 'FQN' => 'PayPalFriendsFamily', 'active' => true]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private ?Cart $cart = null;

    private function ticket(int $cap = 100, int $stock = 100, string $price = '25.00'): EventTicket
    {
        return EventTicket::create([
            'ticket_type' => 'regular',
            'price' => $price,
            'stock' => $stock,
            'cap' => $cap,
            'from' => '2026-01-01 00:00:00',
            'to' => '2026-12-31 23:59:59',
        ]);
    }

    private function cartItem(EventTicket $ticket, int $qty = 2): CartItem
    {
        if ($this->cart === null) {
            $this->cart = Cart::create();
            $this->withSession([CartService::DEFAULT_INSTANCE => $this->cart->id]);
        }

        return CartItem::create([
            'cart_id' => $this->cart->id,
            'event_ticket_id' => $ticket->id,
            'qty' => $qty,
            'single_price' => $ticket->price,
            'row_price' => number_format($ticket->price * $qty, 2, '.', ''),
        ]);
    }

    private function orderPayload(string $paymentMethod, string $email = 'jane@example.com'): array
    {
        return [
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'email' => $email,
            'payment_method' => $paymentMethod,
        ];
    }

    private function payPalMethod(bool $active, bool $adminOnly): PaymentMethod
    {
        return PaymentMethod::create(['alias' => 'paypal', 'name' => 'PayPal', 'FQN' => 'PayPal', 'active' => $active, 'admin_only' => $adminOnly]);
    }

    private function fakePayPal(int $createOrderStatus = 201): void
    {
        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            '*/v2/checkout/orders' => $createOrderStatus === 201
                ? Http::response(['id' => 'ORDER1', 'links' => [['rel' => 'payer-action', 'href' => 'https://paypal.test/approve']]], 201)
                : Http::response(['name' => 'INTERNAL_SERVER_ERROR'], $createOrderStatus),
        ]);
    }

    private function admin(): User
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'x']);
        $role = new Role(['title' => 'Admin']);
        $role->id = 1;
        $role->save();
        $admin->roles()->attach(1);

        return $admin;
    }

    public function test_adding_an_available_ticket_creates_a_cart_item_and_redirects_to_the_cart(): void
    {
        $ticket = $this->ticket();

        $this->post('/cart/add', ['event_ticket_id' => $ticket->id, 'qty' => 2])
            ->assertRedirect('/cart')
            ->assertSessionMissing('error');

        $this->assertSame(1, CartItem::count());
        $item = CartItem::first();
        $this->assertSame($ticket->id, (int) $item->event_ticket_id);
        $this->assertSame(2, (int) $item->qty);
        $this->assertEquals(25, $item->single_price);
        $this->assertEquals(50, $item->row_price);
        $this->assertSame((int) $item->cart_id, (int) session(CartService::DEFAULT_INSTANCE));
    }

    public function test_adding_a_sold_out_ticket_redirects_to_the_cart_with_an_error_and_adds_nothing(): void
    {
        $ticket = $this->ticket(cap: 0);

        $this->post('/cart/add', ['event_ticket_id' => $ticket->id, 'qty' => 1])
            ->assertRedirect('/cart')
            ->assertSessionHas('error');

        $this->assertSame(0, CartItem::count());
    }

    public function test_ordering_with_friends_and_family_creates_user_order_and_transaction_mails_and_redirects_to_success(): void
    {
        $ticket = $this->ticket();
        $cartItem = $this->cartItem($ticket, 2);

        $response = $this->post('/cart/order', $this->orderPayload('paypal_ff'));

        $response->assertRedirect('/cart/success');
        $user = User::where('email', 'jane@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('Jane Doe', $user->name);

        $this->assertSame(1, Order::count());
        $order = Order::first();
        $this->assertSame(Order::STATUS_INITIALIZED, $order->status);
        $this->assertSame($user->id, (int) $order->user_id);
        $this->assertEquals(50, $order->price);
        $this->assertSame(1, $order->orderItems()->count());

        $this->assertSame(1, Transaction::count());
        $transaction = Transaction::first();
        $this->assertSame(Transaction::STATE_INIT, $transaction->state);
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{10}$/', $transaction->reference);
        $this->assertSame('50.00', $transaction->amount->toDecimalString());
        $this->assertSame('paypal_ff', $transaction->paymentMethod->alias);
        $this->assertSame($order->id, (int) $transaction->order_id);
        $this->assertSame($transaction->id, (int) $order->transaction_id);

        Mail::assertSent(OrderCreated::class, fn (OrderCreated $mail) => $mail->hasTo('jane@example.com') && $mail->order->id === $order->id);
        Mail::assertSent(OrderCreatedNotification::class, 1);
        Event::assertDispatched(OrderEventTicketGenerationEvent::class);
        $response->assertSessionHas('lastOrder', fn ($lastOrder) => $lastOrder instanceof Order && $lastOrder->id === $order->id);
    }

    public function test_ordering_takes_the_ordered_quantity_off_the_ticket_stock(): void
    {
        $ticket = $this->ticket(stock: 100);
        $this->cartItem($ticket, 2);

        $this->post('/cart/order', $this->orderPayload('paypal_ff'))->assertRedirect('/cart/success');

        $this->assertSame(1, Order::count());
        $this->assertSame(98, (int) $ticket->refresh()->stock);
    }

    public function test_ordering_an_existing_customer_email_reuses_that_user(): void
    {
        $existing = User::create(['name' => 'Jane Existing', 'email' => 'jane@example.com', 'password' => 'x']);
        $cartItem = $this->cartItem($this->ticket());

        $this->post('/cart/order', $this->orderPayload('paypal_ff'))->assertRedirect('/cart/success');

        $this->assertSame(1, User::count());
        $this->assertSame($existing->id, (int) Order::first()->user_id);
    }

    public function test_ordering_with_an_unknown_payment_method_is_rejected_and_creates_no_order(): void
    {
        $cartItem = $this->cartItem($this->ticket());

        $this->post('/cart/order', $this->orderPayload('bitcoin'))->assertSessionHasErrors('payment_method');

        $this->assertSame(0, Order::count());
        $this->assertSame(0, Transaction::count());
    }

    public function test_a_guest_cannot_order_the_admin_only_paypal_method(): void
    {
        $this->payPalMethod(active: false, adminOnly: true);
        $cartItem = $this->cartItem($this->ticket());

        $this->post('/cart/order', $this->orderPayload('paypal'))->assertSessionHasErrors('payment_method');

        $this->assertSame(0, Order::count());
    }

    public function test_a_logged_in_customer_cannot_order_the_admin_only_paypal_method(): void
    {
        $this->payPalMethod(active: false, adminOnly: true);
        $customer = User::create(['name' => 'Customer', 'email' => 'customer@example.com', 'password' => 'x']);
        $cartItem = $this->cartItem($this->ticket());

        $this->actingAs($customer)
            ->post('/cart/order', $this->orderPayload('paypal', 'customer@example.com'))
            ->assertSessionHasErrors('payment_method');

        $this->assertSame(0, Order::count());
    }

    public function test_a_customer_ordering_active_paypal_is_redirected_to_paypal_approval(): void
    {
        $this->payPalMethod(active: true, adminOnly: false);
        $this->fakePayPal();
        $cartItem = $this->cartItem($this->ticket());

        $this->post('/cart/order', $this->orderPayload('paypal'))
            ->assertRedirect('https://paypal.test/approve');

        $transaction = Transaction::first();
        $this->assertSame('ORDER1', $transaction->external_id);
        $this->assertSame(Transaction::STATE_INIT, $transaction->state);
        $this->assertSame('paypal', $transaction->paymentMethod->alias);
    }

    public function test_when_paypal_is_unreachable_the_order_is_kept_and_the_customer_lands_on_success_with_a_warning(): void
    {
        $this->payPalMethod(active: true, adminOnly: false);
        $this->fakePayPal(500);
        $cartItem = $this->cartItem($this->ticket());

        $this->post('/cart/order', $this->orderPayload('paypal'))
            ->assertRedirect('/cart/success')
            ->assertSessionHas('warning')
            ->assertSessionHas('lastOrder');

        $this->assertSame(1, Order::count());
        $transaction = Transaction::first();
        $this->assertNull($transaction->external_id);
        $this->assertSame(Transaction::STATE_INIT, $transaction->state);
    }

    public function test_an_admin_can_order_the_admin_only_paypal_method(): void
    {
        $this->payPalMethod(active: false, adminOnly: true);
        $this->fakePayPal();
        $admin = $this->admin();
        $cartItem = $this->cartItem($this->ticket());

        $this->actingAs($admin)
            ->post('/cart/order', $this->orderPayload('paypal', 'admin@example.com'))
            ->assertRedirect('https://paypal.test/approve');

        $this->assertSame(1, Order::count());
        $this->assertSame($admin->id, (int) Order::first()->user_id);
        $this->assertSame('ORDER1', Transaction::first()->external_id);
    }

    public function test_the_success_page_without_a_last_order_redirects_home_with_a_warning(): void
    {
        $this->get('/cart/success')
            ->assertRedirect('/')
            ->assertSessionHas('warning');
    }

    public function test_cart_items_of_another_visitors_cart_are_never_part_of_my_order(): void
    {
        $ticket = $this->ticket();
        $mine = $this->cartItem($ticket, 1);
        CartItem::create([
            'cart_id' => Cart::create()->id,
            'event_ticket_id' => $ticket->id,
            'qty' => 5,
            'single_price' => '25.00',
            'row_price' => '125.00',
        ]);

        $this->post('/cart/order', $this->orderPayload('paypal_ff'))->assertRedirect('/cart/success');

        $order = Order::first();
        $this->assertEquals(25, $order->price);
        $this->assertSame([$mine->id], $order->orderItems->pluck('cart_item_id')->map(fn ($id) => (int) $id)->all());
    }

    public function test_ordering_with_an_empty_cart_is_rejected(): void
    {
        $this->post('/cart/order', $this->orderPayload('paypal_ff'))->assertSessionHasErrors('cart');

        $this->assertSame(0, Order::count());
    }

    public function test_adding_a_different_ticket_adds_a_second_row_to_the_cart(): void
    {
        $first = $this->ticket();
        $second = $this->ticket();

        $this->post('/cart/add', ['event_ticket_id' => $first->id, 'qty' => 1])->assertSessionMissing('error');
        $this->post('/cart/add', ['event_ticket_id' => $second->id, 'qty' => 2])->assertSessionMissing('error');

        $this->assertSame(2, CartItem::count());
        $this->assertEqualsCanonicalizing([$first->id, $second->id], CartItem::pluck('event_ticket_id')->map(fn ($id) => (int) $id)->all());
        $this->assertSame(1, CartItem::pluck('cart_id')->unique()->count());
    }

    public function test_a_multi_item_cart_becomes_one_order_with_summed_price_and_matching_transaction_amount(): void
    {
        $regular = $this->ticket(stock: 100, price: '25.00');
        $vip = $this->ticket(stock: 10, price: '40.50');
        $this->cartItem($regular, 2);
        $this->cartItem($vip, 1);

        $response = $this->post('/cart/order', $this->orderPayload('paypal_ff'));

        $response->assertRedirect('/cart/success');
        $this->assertSame(1, Order::count());
        $order = Order::first();
        $this->assertSame(2, $order->orderItems()->count());
        $this->assertEquals(90.5, $order->price);
        $this->assertSame(1, Transaction::count());
        $this->assertSame('90.50', Transaction::first()->amount->toDecimalString());
        $this->assertSame(98, (int) $regular->refresh()->stock);
        $this->assertSame(9, (int) $vip->refresh()->stock);
        Mail::assertSent(OrderCreated::class, 1);
        $response->assertSessionMissing(CartService::DEFAULT_INSTANCE);
    }

    public function test_the_cart_is_emptied_after_ordering_so_the_same_cart_cannot_be_ordered_twice(): void
    {
        $this->cartItem($this->ticket(), 1);

        $this->post('/cart/order', $this->orderPayload('paypal_ff'))->assertRedirect('/cart/success');
        $this->post('/cart/order', $this->orderPayload('paypal_ff'))->assertSessionHasErrors('cart');

        $this->assertSame(1, Order::count());
    }

    public function test_ordering_more_than_the_remaining_stock_is_refused_and_changes_nothing(): void
    {
        $ticket = $this->ticket(stock: 1);
        $this->cartItem($ticket, 2);

        $this->post('/cart/order', $this->orderPayload('paypal_ff'))
            ->assertRedirect('/cart')
            ->assertSessionHas('error');

        $this->assertSame(0, Order::count());
        $this->assertSame(0, Transaction::count());
        $this->assertSame(0, User::count());
        $this->assertSame(1, (int) $ticket->refresh()->stock);
    }

    public function test_one_sold_out_item_rolls_back_the_stock_of_the_other_items(): void
    {
        $available = $this->ticket(stock: 10);
        $soldOut = $this->ticket(stock: 0);
        $this->cartItem($available, 3);
        $this->cartItem($soldOut, 1);

        $this->post('/cart/order', $this->orderPayload('paypal_ff'))
            ->assertRedirect('/cart')
            ->assertSessionHas('error');

        $this->assertSame(0, Order::count());
        $this->assertSame(10, (int) $available->refresh()->stock, 'the first item must not stay decremented for an order that was never created');
    }

    public function test_adding_more_tickets_than_the_stock_is_refused(): void
    {
        $ticket = $this->ticket(stock: 1);

        $this->post('/cart/add', ['event_ticket_id' => $ticket->id, 'qty' => 2])
            ->assertRedirect('/cart')
            ->assertSessionHas('error');

        $this->assertSame(0, CartItem::count());
    }

    public function test_a_ticket_with_no_stock_left_cannot_be_added_even_if_codes_remain(): void
    {
        $ticket = $this->ticket(cap: 100, stock: 0);

        $this->post('/cart/add', ['event_ticket_id' => $ticket->id, 'qty' => 1])
            ->assertRedirect('/cart')
            ->assertSessionHas('error');

        $this->assertSame(0, CartItem::count());
    }

    public function test_adding_the_same_ticket_again_updates_its_quantity(): void
    {
        $ticket = $this->ticket();

        $this->post('/cart/add', ['event_ticket_id' => $ticket->id, 'qty' => 1])->assertSessionMissing('error');
        $this->post('/cart/add', ['event_ticket_id' => $ticket->id, 'qty' => 3])->assertSessionMissing('error');

        $this->assertSame(1, CartItem::count());
        $this->assertSame(3, (int) CartItem::first()->qty);
    }
}
