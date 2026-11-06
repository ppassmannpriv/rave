<?php

namespace Tests\Unit\Actions\EventTicketCode;

use App\Actions\EventTicketCode\CleanUpOrphans;
use App\Models\EventTicketCode;
use App\Models\Order;
use App\Models\Order\OrderItem;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Elapsed-time regression tests for CleanUpOrphans (roadmap item 3.4): only events whose
 * start is at or after "now" are processed. Both sides of that edge are exercised with a
 * frozen clock.
 *
 * Same hand-built SQLite schema approach (and caveat) as PayTransactionTest (KI-18).
 */
class CleanUpOrphansTest extends TestCase
{
    private const NOW = '2026-03-10 12:00:00';

    private const INITIAL_STOCK = 10;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse(self::NOW));

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->datetime('start');
            $table->datetime('end');
            $table->string('location');
            $table->longText('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status');
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
        Schema::create('event_ticket_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->unsignedBigInteger('event_ticket_id');
            $table->unsignedBigInteger('order_item_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * Events and tickets are inserted through the query builder: their models' date mutators
     * expect the panel display format, and EventTicket's `created` event generates codes.
     *
     * @return int the event ticket id
     */
    private function makeEventTicket(Carbon $eventStart): int
    {
        $eventId = DB::table('events')->insertGetId([
            'name' => 'Rave',
            'start' => $eventStart->toDateTimeString(),
            'end' => $eventStart->copy()->addHours(8)->toDateTimeString(),
            'location' => 'Club',
        ]);

        return DB::table('event_tickets')->insertGetId([
            'ticket_type' => 'regular',
            'price' => '25.00',
            'event_id' => $eventId,
            'stock' => self::INITIAL_STOCK,
            'cap' => 100,
        ]);
    }

    private function makeCodeForOrder(int $eventTicketId, ?int $orderId): EventTicketCode
    {
        $orderItem = OrderItem::create([
            'order_id' => $orderId,
            'qty' => 1,
            'single_price' => '25.00',
            'row_price' => '25.00',
            'event_ticket_id' => $eventTicketId,
        ]);

        return EventTicketCode::create([
            'code' => 'CODE'.$orderItem->id,
            'event_ticket_id' => $eventTicketId,
            'order_item_id' => $orderItem->id,
        ]);
    }

    private function makeOrder(string $status): Order
    {
        return Order::create(['status' => $status]);
    }

    private function stockOf(int $eventTicketId): int
    {
        return (int) DB::table('event_tickets')->where('id', $eventTicketId)->value('stock');
    }

    private function assertDetachedAndRestocked(EventTicketCode $code, int $eventTicketId): void
    {
        $this->assertNull($code->refresh()->order_item_id);
        $this->assertSame(self::INITIAL_STOCK + 1, $this->stockOf($eventTicketId));
    }

    private function assertUntouched(EventTicketCode $code, int $originalOrderItemId, int $eventTicketId): void
    {
        $this->assertSame($originalOrderItemId, (int) $code->refresh()->order_item_id);
        $this->assertSame(self::INITIAL_STOCK, $this->stockOf($eventTicketId));
    }

    public function test_cancelled_order_code_for_event_starting_one_second_from_now_is_detached_and_restocked(): void
    {
        $ticketId = $this->makeEventTicket(Carbon::now()->addSecond());
        $code = $this->makeCodeForOrder($ticketId, $this->makeOrder(Order::STATUS_CANCELLED)->id);

        CleanUpOrphans::make()->handle();

        $this->assertDetachedAndRestocked($code, $ticketId);
    }

    public function test_cancelled_order_code_for_event_starting_exactly_now_is_detached_and_restocked(): void
    {
        $ticketId = $this->makeEventTicket(Carbon::now());
        $code = $this->makeCodeForOrder($ticketId, $this->makeOrder(Order::STATUS_CANCELLED)->id);

        CleanUpOrphans::make()->handle();

        $this->assertDetachedAndRestocked($code, $ticketId);
    }

    public function test_cancelled_order_code_for_event_that_started_one_second_ago_is_untouched(): void
    {
        $ticketId = $this->makeEventTicket(Carbon::now()->subSecond());
        $code = $this->makeCodeForOrder($ticketId, $this->makeOrder(Order::STATUS_CANCELLED)->id);
        $orderItemId = $code->order_item_id;

        CleanUpOrphans::make()->handle();

        $this->assertUntouched($code, $orderItemId, $ticketId);
    }

    public function test_code_of_non_cancelled_order_for_future_event_is_untouched(): void
    {
        $ticketId = $this->makeEventTicket(Carbon::now()->addSecond());
        $code = $this->makeCodeForOrder($ticketId, $this->makeOrder(Order::STATUS_INITIALIZED)->id);
        $orderItemId = $code->order_item_id;

        CleanUpOrphans::make()->handle();

        $this->assertUntouched($code, $orderItemId, $ticketId);
    }

    public function test_code_whose_order_item_points_to_a_missing_order_is_detached_and_restocked(): void
    {
        $ticketId = $this->makeEventTicket(Carbon::now()->addSecond());
        $code = $this->makeCodeForOrder($ticketId, 999);

        CleanUpOrphans::make()->handle();

        $this->assertDetachedAndRestocked($code, $ticketId);
    }

    public function test_code_whose_order_item_has_no_order_id_is_detached_and_restocked(): void
    {
        $ticketId = $this->makeEventTicket(Carbon::now()->addSecond());
        $code = $this->makeCodeForOrder($ticketId, null);

        CleanUpOrphans::make()->handle();

        $this->assertDetachedAndRestocked($code, $ticketId);
    }

    public function test_code_of_soft_deleted_cancelled_order_is_detached_and_restocked(): void
    {
        $ticketId = $this->makeEventTicket(Carbon::now()->addSecond());
        $order = $this->makeOrder(Order::STATUS_CANCELLED);
        $code = $this->makeCodeForOrder($ticketId, $order->id);
        $order->delete();

        CleanUpOrphans::make()->handle();

        $this->assertDetachedAndRestocked($code, $ticketId);
    }

    /**
     * The order lookup uses withTrashed(), so a soft-deleted order that was never cancelled
     * still counts as "present" and its code stays reserved.
     */
    public function test_code_of_soft_deleted_non_cancelled_order_is_untouched_current_behaviour(): void
    {
        $ticketId = $this->makeEventTicket(Carbon::now()->addSecond());
        $order = $this->makeOrder(Order::STATUS_INITIALIZED);
        $code = $this->makeCodeForOrder($ticketId, $order->id);
        $orderItemId = $code->order_item_id;
        $order->delete();

        CleanUpOrphans::make()->handle();

        $this->assertUntouched($code, $orderItemId, $ticketId);
    }
}
