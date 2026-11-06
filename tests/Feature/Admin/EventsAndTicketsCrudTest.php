<?php

namespace Tests\Feature\Admin;

use App\Events\EventTicketCreatedEvent;
use App\Models\Event;
use App\Models\EventTicket;
use App\Models\EventTicketCode;
use App\Models\Order\OrderItem;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Admin CRUD for events, event tickets and event ticket codes (roadmap 3.6). Same hand-built SQLite
 * schema approach as PayPalFriendsFamilyReportPipelineTest (no MySQL here, migrations are not SQLite
 * compatible, KI-18). Gates come from the real roles/permissions tables via AuthGates. GET routes are
 * only checked for their 403 outcome so no Blade layout is rendered.
 */
class EventsAndTicketsCrudTest extends TestCase
{
    private const EVENT_PERMISSIONS = ['event_access', 'event_create', 'event_edit', 'event_show', 'event_delete'];

    private const TICKET_PERMISSIONS = ['event_ticket_access', 'event_ticket_create', 'event_ticket_edit', 'event_ticket_show', 'event_ticket_delete'];

    private const CODE_PERMISSIONS = ['event_ticket_code_access', 'event_ticket_code_create', 'event_ticket_code_edit', 'event_ticket_code_show', 'event_ticket_code_delete'];

    private int $userCounter = 0;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        EventFacade::fake([EventTicketCreatedEvent::class]);

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
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->integer('stock')->default(100);
            $table->integer('cap')->default(100);
        });
        Schema::create('event_ticket_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('event_ticket_id');
            $table->unsignedBigInteger('order_item_id')->nullable();
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
    }

    private function userWith(array $permissions): User
    {
        $n = ++$this->userCounter;
        $user = User::create(['name' => "User $n", 'email' => "user$n@example.com", 'password' => 'x']);
        $role = Role::create(['title' => "Role $n"]);
        foreach ($permissions as $title) {
            $permission = Permission::where('title', $title)->first() ?? Permission::create(['title' => $title]);
            $role->permissions()->attach($permission->id);
        }
        $user->roles()->attach($role->id);

        return $user;
    }

    private function event(string $name = 'Summer Rave'): Event
    {
        return Event::create([
            'name' => $name,
            'start' => '2026-07-01 20:00:00',
            'end' => '2026-07-02 06:00:00',
            'location' => 'Warehouse',
        ]);
    }

    private function ticket(?Event $event = null, string $type = 'regular'): EventTicket
    {
        return EventTicket::create([
            'ticket_type' => $type,
            'price' => '25.00',
            'stock' => 100,
            'cap' => 100,
            'event_id' => ($event ?? $this->event())->id,
            'from' => '2026-01-01 00:00:00',
            'to' => '2026-06-30 23:59:59',
        ]);
    }

    private function code(EventTicket $ticket, string $code = 'ABCDEF12', ?int $orderItemId = null): EventTicketCode
    {
        return EventTicketCode::create(['code' => $code, 'event_ticket_id' => $ticket->id, 'order_item_id' => $orderItemId]);
    }

    private function orderItem(EventTicket $ticket, ?int $cartItemId): OrderItem
    {
        return OrderItem::create([
            'qty' => 1,
            'single_price' => '25.00',
            'row_price' => '25.00',
            'cart_item_id' => $cartItemId,
            'event_ticket_id' => $ticket->id,
        ]);
    }

    private function eventPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Winter Rave',
            'start' => '2026-12-01 21:00:00',
            'end' => '2026-12-02 05:00:00',
            'location' => 'Bunker',
            'description' => 'Bring a jacket',
        ], $overrides);
    }

    private function ticketPayload(int $eventId, array $overrides = []): array
    {
        return array_merge([
            'ticket_type' => 'vip',
            'price' => '40.50',
            'stock' => 50,
            'cap' => 60,
            'event_id' => $eventId,
            'from' => '2026-02-01 10:00:00',
            'to' => '2026-11-30 23:00:00',
        ], $overrides);
    }

    /**
     * Each route is requested by a user holding every permission of the resource except the one the
     * route requires; all must be 403.
     *
     * @param  array<int, array{0: string, 1: string, 2: string, 3?: array}>  $routes [method, uri, required permission, data]
     */
    private function assertEachRouteForbiddenWithoutItsPermission(array $routes, array $allPermissions): void
    {
        foreach ($routes as [$method, $uri, $required, $data]) {
            $user = $this->userWith(array_values(array_diff($allPermissions, [$required])));
            $this->actingAs($user)->call($method, $uri, $data)
                ->assertForbidden();
        }
    }

    /** @param  array<int, array{0: string, 1: string, 2?: array}>  $routes */
    private function assertEachRouteRedirectsGuestToLogin(array $routes): void
    {
        foreach ($routes as [$method, $uri, $data]) {
            $this->call($method, $uri, $data)->assertRedirect(route('login'));
        }
    }

    public function test_an_admin_with_event_create_can_store_an_event_with_the_posted_fields(): void
    {
        $this->actingAs($this->userWith(['event_create']))
            ->post('/admin/events', $this->eventPayload())
            ->assertRedirect(route('admin.events.index'));

        $this->assertSame(1, Event::count());
        $event = Event::first();
        $this->assertSame('Winter Rave', $event->name);
        $this->assertSame('Bunker', $event->location);
        $this->assertSame('Bring a jacket', $event->description);
        $this->assertSame('2026-12-01 21:00:00', $event->getRawOriginal('start'));
        $this->assertSame('2026-12-02 05:00:00', $event->getRawOriginal('end'));
    }

    public function test_storing_an_event_with_missing_name_and_badly_formatted_dates_is_rejected_and_creates_nothing(): void
    {
        $this->actingAs($this->userWith(['event_create']))
            ->post('/admin/events', $this->eventPayload(['name' => '', 'start' => '01.12.2026', 'end' => 'tomorrow', 'location' => '']))
            ->assertSessionHasErrors(['name', 'start', 'end', 'location']);

        $this->assertSame(0, Event::count());
    }

    public function test_an_admin_with_event_edit_can_update_an_event(): void
    {
        $event = $this->event();

        $this->actingAs($this->userWith(['event_edit']))
            ->put("/admin/events/{$event->id}", $this->eventPayload(['name' => 'Renamed', 'location' => 'Club']))
            ->assertRedirect(route('admin.events.index'));

        $event->refresh();
        $this->assertSame('Renamed', $event->name);
        $this->assertSame('Club', $event->location);
        $this->assertSame('2026-12-01 21:00:00', $event->getRawOriginal('start'));
    }

    public function test_updating_an_event_with_invalid_input_is_rejected_and_leaves_it_unchanged(): void
    {
        $event = $this->event();

        $this->actingAs($this->userWith(['event_edit']))
            ->put("/admin/events/{$event->id}", $this->eventPayload(['name' => '', 'start' => 'not-a-date']))
            ->assertSessionHasErrors(['name', 'start']);

        $event->refresh();
        $this->assertSame('Summer Rave', $event->name);
        $this->assertSame('2026-07-01 20:00:00', $event->getRawOriginal('start'));
    }

    public function test_an_admin_with_event_delete_soft_deletes_an_event(): void
    {
        $event = $this->event();

        $this->actingAs($this->userWith(['event_delete']))
            ->delete("/admin/events/{$event->id}")
            ->assertRedirect();

        $this->assertSame(0, Event::count());
        $this->assertSoftDeleted('events', ['id' => $event->id]);
    }

    public function test_mass_destroy_soft_deletes_only_the_selected_events(): void
    {
        $first = $this->event('First');
        $second = $this->event('Second');
        $kept = $this->event('Kept');

        $this->actingAs($this->userWith(['event_delete']))
            ->delete('/admin/events/destroy', ['ids' => [$first->id, $second->id]])
            ->assertNoContent();

        $this->assertSoftDeleted('events', ['id' => $first->id]);
        $this->assertSoftDeleted('events', ['id' => $second->id]);
        $this->assertSame([$kept->id], Event::pluck('id')->map(fn ($id) => (int) $id)->all());
    }

    public function test_mass_destroy_of_events_with_an_unknown_id_is_rejected_and_deletes_nothing(): void
    {
        $event = $this->event();

        $this->actingAs($this->userWith(['event_delete']))
            ->delete('/admin/events/destroy', ['ids' => [$event->id, 999]])
            ->assertSessionHasErrors('ids.1');

        $this->assertSame(1, Event::count());
    }

    public function test_every_event_route_is_forbidden_without_its_specific_permission(): void
    {
        $event = $this->event();

        $this->assertEachRouteForbiddenWithoutItsPermission([
            ['GET', '/admin/events', 'event_access', []],
            ['GET', '/admin/events/create', 'event_create', []],
            ['POST', '/admin/events', 'event_create', $this->eventPayload()],
            ['GET', "/admin/events/{$event->id}", 'event_show', []],
            ['GET', "/admin/events/{$event->id}/edit", 'event_edit', []],
            ['PUT', "/admin/events/{$event->id}", 'event_edit', $this->eventPayload(['name' => 'Hacked'])],
            ['DELETE', "/admin/events/{$event->id}", 'event_delete', []],
            ['DELETE', '/admin/events/destroy', 'event_delete', ['ids' => [$event->id]]],
        ], self::EVENT_PERMISSIONS);

        $this->assertSame(1, Event::count());
        $this->assertSame('Summer Rave', $event->refresh()->name);
    }

    public function test_every_event_route_redirects_a_guest_to_login(): void
    {
        $event = $this->event();

        $this->assertEachRouteRedirectsGuestToLogin([
            ['GET', '/admin/events', []],
            ['GET', '/admin/events/create', []],
            ['POST', '/admin/events', $this->eventPayload()],
            ['GET', "/admin/events/{$event->id}", []],
            ['GET', "/admin/events/{$event->id}/edit", []],
            ['PUT', "/admin/events/{$event->id}", $this->eventPayload(['name' => 'Hacked'])],
            ['DELETE', "/admin/events/{$event->id}", []],
            ['DELETE', '/admin/events/destroy', ['ids' => [$event->id]]],
        ]);

        $this->assertSame(1, Event::count());
        $this->assertSame('Summer Rave', $event->refresh()->name);
    }

    public function test_an_admin_with_event_ticket_create_can_store_a_ticket_with_the_posted_fields(): void
    {
        $event = $this->event();

        $this->actingAs($this->userWith(['event_ticket_create']))
            ->post('/admin/event-tickets', $this->ticketPayload($event->id))
            ->assertRedirect(route('admin.event-tickets.index'));

        $this->assertSame(1, EventTicket::count());
        $ticket = EventTicket::first();
        $this->assertSame('vip', $ticket->ticket_type);
        $this->assertEquals(40.5, $ticket->price);
        $this->assertSame(50, (int) $ticket->stock);
        $this->assertSame(60, (int) $ticket->cap);
        $this->assertSame($event->id, (int) $ticket->event_id);
        $this->assertSame('2026-02-01 10:00:00', $ticket->getRawOriginal('from'));
        $this->assertSame('2026-11-30 23:00:00', $ticket->getRawOriginal('to'));
        EventFacade::assertDispatched(EventTicketCreatedEvent::class, 1);
    }

    public function test_storing_a_ticket_with_missing_and_non_integer_fields_is_rejected_and_creates_nothing(): void
    {
        $event = $this->event();

        $this->actingAs($this->userWith(['event_ticket_create']))
            ->post('/admin/event-tickets', $this->ticketPayload($event->id, [
                'ticket_type' => '',
                'price' => '',
                'stock' => 'many',
                'cap' => '1.5',
                'event_id' => '',
                'from' => '01.02.2026',
            ]))
            ->assertSessionHasErrors(['ticket_type', 'price', 'stock', 'cap', 'event_id', 'from']);

        $this->assertSame(0, EventTicket::count());
        EventFacade::assertNotDispatched(EventTicketCreatedEvent::class);
    }

    public function test_an_admin_with_event_ticket_edit_can_update_a_ticket(): void
    {
        $ticket = $this->ticket();
        $otherEvent = $this->event('Other');

        $this->actingAs($this->userWith(['event_ticket_edit']))
            ->put("/admin/event-tickets/{$ticket->id}", $this->ticketPayload($otherEvent->id, ['ticket_type' => 'early_bird', 'price' => '15.00', 'stock' => 10]))
            ->assertRedirect(route('admin.event-tickets.index'));

        $ticket->refresh();
        $this->assertSame('early_bird', $ticket->ticket_type);
        $this->assertEquals(15, $ticket->price);
        $this->assertSame(10, (int) $ticket->stock);
        $this->assertSame($otherEvent->id, (int) $ticket->event_id);
        $this->assertSame('2026-11-30 23:00:00', $ticket->getRawOriginal('to'));
    }

    public function test_updating_a_ticket_with_invalid_input_is_rejected_and_leaves_it_unchanged(): void
    {
        $ticket = $this->ticket();

        $this->actingAs($this->userWith(['event_ticket_edit']))
            ->put("/admin/event-tickets/{$ticket->id}", $this->ticketPayload((int) $ticket->event_id, ['stock' => 'lots', 'to' => 'later']))
            ->assertSessionHasErrors(['stock', 'to']);

        $ticket->refresh();
        $this->assertSame(100, (int) $ticket->stock);
        $this->assertSame('regular', $ticket->ticket_type);
    }

    public function test_an_admin_with_event_ticket_delete_soft_deletes_a_ticket_that_was_never_ordered(): void
    {
        $ticket = $this->ticket();

        $this->actingAs($this->userWith(['event_ticket_delete']))
            ->delete("/admin/event-tickets/{$ticket->id}")
            ->assertRedirect();

        $this->assertSame(0, EventTicket::count());
        $this->assertSoftDeleted('event_tickets', ['id' => $ticket->id]);
    }

    public function test_deleting_a_ticket_that_has_order_items_is_refused(): void
    {
        $ticket = $this->ticket();
        $this->orderItem($ticket, cartItemId: 777);

        $this->withoutExceptionHandling();
        try {
            $this->actingAs($this->userWith(['event_ticket_delete']))->delete("/admin/event-tickets/{$ticket->id}");
            $this->fail('deleting an ordered ticket was expected to be refused');
        } catch (\Exception $e) {
            $this->assertNotInstanceOf(\PHPUnit\Framework\AssertionFailedError::class, $e);
        }

        $this->assertNotSoftDeleted('event_tickets', ['id' => $ticket->id]);
    }

    public function test_an_order_item_of_another_ticket_does_not_block_deleting_a_ticket_even_if_its_cart_item_id_matches(): void
    {
        $ticket = $this->ticket();
        $this->orderItem($this->ticket(), cartItemId: $ticket->id);

        $this->actingAs($this->userWith(['event_ticket_delete']))
            ->delete("/admin/event-tickets/{$ticket->id}")
            ->assertRedirect();

        $this->assertSoftDeleted('event_tickets', ['id' => $ticket->id]);
    }

    public function test_mass_destroy_deletes_a_ticket_whose_single_delete_would_be_refused_current_behaviour(): void
    {
        $ticket = $this->ticket();
        $this->orderItem($this->ticket(), cartItemId: $ticket->id);

        $this->actingAs($this->userWith(['event_ticket_delete']))
            ->delete('/admin/event-tickets/destroy', ['ids' => [$ticket->id]])
            ->assertNoContent();

        $this->assertSoftDeleted('event_tickets', ['id' => $ticket->id]);
    }

    public function test_every_event_ticket_route_is_forbidden_without_its_specific_permission(): void
    {
        $ticket = $this->ticket();

        $this->assertEachRouteForbiddenWithoutItsPermission([
            ['GET', '/admin/event-tickets', 'event_ticket_access', []],
            ['GET', '/admin/event-tickets/create', 'event_ticket_create', []],
            ['POST', '/admin/event-tickets', 'event_ticket_create', $this->ticketPayload((int) $ticket->event_id)],
            ['GET', "/admin/event-tickets/{$ticket->id}", 'event_ticket_show', []],
            ['GET', "/admin/event-tickets/{$ticket->id}/edit", 'event_ticket_edit', []],
            ['PUT', "/admin/event-tickets/{$ticket->id}", 'event_ticket_edit', $this->ticketPayload((int) $ticket->event_id, ['stock' => 1])],
            ['DELETE', "/admin/event-tickets/{$ticket->id}", 'event_ticket_delete', []],
            ['DELETE', '/admin/event-tickets/destroy', 'event_ticket_delete', ['ids' => [$ticket->id]]],
        ], self::TICKET_PERMISSIONS);

        $this->assertSame(1, EventTicket::count());
        $this->assertSame(100, (int) $ticket->refresh()->stock);
    }

    public function test_every_event_ticket_route_redirects_a_guest_to_login(): void
    {
        $ticket = $this->ticket();

        $this->assertEachRouteRedirectsGuestToLogin([
            ['GET', '/admin/event-tickets', []],
            ['GET', '/admin/event-tickets/create', []],
            ['POST', '/admin/event-tickets', $this->ticketPayload((int) $ticket->event_id)],
            ['GET', "/admin/event-tickets/{$ticket->id}", []],
            ['GET', "/admin/event-tickets/{$ticket->id}/edit", []],
            ['PUT', "/admin/event-tickets/{$ticket->id}", $this->ticketPayload((int) $ticket->event_id, ['stock' => 1])],
            ['DELETE', "/admin/event-tickets/{$ticket->id}", []],
            ['DELETE', '/admin/event-tickets/destroy', ['ids' => [$ticket->id]]],
        ]);

        $this->assertSame(1, EventTicket::count());
        $this->assertSame(100, (int) $ticket->refresh()->stock);
    }

    public function test_an_admin_with_code_create_can_store_a_code_for_a_ticket(): void
    {
        $ticket = $this->ticket();

        $this->actingAs($this->userWith(['event_ticket_code_create']))
            ->post('/admin/event-ticket-codes', ['code' => 'NEWCODE1', 'event_ticket_id' => $ticket->id])
            ->assertRedirect(route('admin.event-ticket-codes.index'));

        $this->assertSame(1, EventTicketCode::count());
        $code = EventTicketCode::first();
        $this->assertSame('NEWCODE1', $code->code);
        $this->assertSame($ticket->id, (int) $code->event_ticket_id);
        $this->assertNull($code->order_item_id);
    }

    /**
     * The create form only posts "code", the request only validates "code", but event_ticket_id is
     * NOT NULL without default in the migration, so the insert fails.
     */
    public function test_storing_a_code_without_a_ticket_id_fails_in_the_database_current_behaviour(): void
    {
        $this->withoutExceptionHandling();
        $this->expectException(QueryException::class);

        $this->actingAs($this->userWith(['event_ticket_code_create']))
            ->post('/admin/event-ticket-codes', ['code' => 'NEWCODE1']);
    }

    public function test_storing_a_too_short_code_is_rejected_and_creates_nothing(): void
    {
        $this->actingAs($this->userWith(['event_ticket_code_create']))
            ->post('/admin/event-ticket-codes', ['code' => 'ABC', 'event_ticket_id' => $this->ticket()->id])
            ->assertSessionHasErrors('code');

        $this->assertSame(0, EventTicketCode::count());
    }

    public function test_storing_a_duplicate_code_is_rejected(): void
    {
        $ticket = $this->ticket();
        $this->code($ticket, 'TAKEN123');

        $this->actingAs($this->userWith(['event_ticket_code_create']))
            ->post('/admin/event-ticket-codes', ['code' => 'TAKEN123', 'event_ticket_id' => $ticket->id])
            ->assertSessionHasErrors('code');

        $this->assertSame(1, EventTicketCode::count());
    }

    public function test_an_admin_with_code_edit_can_update_a_code_and_may_keep_its_own_value(): void
    {
        $code = $this->code($this->ticket(), 'OLDCODE1');
        $admin = $this->userWith(['event_ticket_code_edit']);

        $this->actingAs($admin)
            ->put("/admin/event-ticket-codes/{$code->id}", ['code' => 'OLDCODE1'])
            ->assertRedirect(route('admin.event-ticket-codes.index'));
        $this->actingAs($admin)
            ->put("/admin/event-ticket-codes/{$code->id}", ['code' => 'NEWCODE2'])
            ->assertRedirect(route('admin.event-ticket-codes.index'));

        $this->assertSame('NEWCODE2', $code->refresh()->code);
    }

    public function test_updating_a_code_to_another_codes_value_is_rejected(): void
    {
        $ticket = $this->ticket();
        $this->code($ticket, 'TAKEN123');
        $code = $this->code($ticket, 'MINE1234');

        $this->actingAs($this->userWith(['event_ticket_code_edit']))
            ->put("/admin/event-ticket-codes/{$code->id}", ['code' => 'TAKEN123'])
            ->assertSessionHasErrors('code');

        $this->assertSame('MINE1234', $code->refresh()->code);
    }

    /** EventTicketCode::delete() throws when the code has NO order item: the guard is inverted. */
    public function test_deleting_an_unsold_code_is_refused_current_behaviour(): void
    {
        $code = $this->code($this->ticket());

        $this->withoutExceptionHandling();
        try {
            $this->actingAs($this->userWith(['event_ticket_code_delete']))->delete("/admin/event-ticket-codes/{$code->id}");
            $this->fail('deleting the unsold code was expected to be refused');
        } catch (\Exception $e) {
            $this->assertNotInstanceOf(\PHPUnit\Framework\AssertionFailedError::class, $e);
        }

        $this->assertNotSoftDeleted('event_ticket_codes', ['id' => $code->id]);
    }

    public function test_deleting_a_sold_code_succeeds_current_behaviour(): void
    {
        $ticket = $this->ticket();
        $code = $this->code($ticket, 'SOLD1234', $this->orderItem($ticket, null)->id);

        $this->actingAs($this->userWith(['event_ticket_code_delete']))
            ->delete("/admin/event-ticket-codes/{$code->id}")
            ->assertRedirect();

        $this->assertSoftDeleted('event_ticket_codes', ['id' => $code->id]);
    }

    public function test_mass_destroy_soft_deletes_the_selected_codes_sold_or_not(): void
    {
        $ticket = $this->ticket();
        $unsold = $this->code($ticket, 'UNSOLD12');
        $sold = $this->code($ticket, 'SOLD1234', $this->orderItem($ticket, null)->id);
        $kept = $this->code($ticket, 'KEPT1234');

        $this->actingAs($this->userWith(['event_ticket_code_delete']))
            ->delete('/admin/event-ticket-codes/destroy', ['ids' => [$unsold->id, $sold->id]])
            ->assertNoContent();

        $this->assertSoftDeleted('event_ticket_codes', ['id' => $unsold->id]);
        $this->assertSoftDeleted('event_ticket_codes', ['id' => $sold->id]);
        $this->assertSame([$kept->id], EventTicketCode::pluck('id')->map(fn ($id) => (int) $id)->all());
    }

    public function test_every_event_ticket_code_route_is_forbidden_without_its_specific_permission(): void
    {
        $code = $this->code($this->ticket(), 'OLDCODE1');

        $this->assertEachRouteForbiddenWithoutItsPermission([
            ['GET', '/admin/event-ticket-codes', 'event_ticket_code_access', []],
            ['GET', '/admin/event-ticket-codes/create', 'event_ticket_code_create', []],
            ['POST', '/admin/event-ticket-codes', 'event_ticket_code_create', ['code' => 'NEWCODE1', 'event_ticket_id' => $code->event_ticket_id]],
            ['GET', "/admin/event-ticket-codes/{$code->id}", 'event_ticket_code_show', []],
            ['GET', "/admin/event-ticket-codes/{$code->id}/edit", 'event_ticket_code_edit', []],
            ['PUT', "/admin/event-ticket-codes/{$code->id}", 'event_ticket_code_edit', ['code' => 'HACKED12']],
            ['DELETE', "/admin/event-ticket-codes/{$code->id}", 'event_ticket_code_delete', []],
            ['DELETE', '/admin/event-ticket-codes/destroy', 'event_ticket_code_delete', ['ids' => [$code->id]]],
        ], self::CODE_PERMISSIONS);

        $this->assertSame(1, EventTicketCode::count());
        $this->assertSame('OLDCODE1', $code->refresh()->code);
    }

    public function test_every_event_ticket_code_route_redirects_a_guest_to_login(): void
    {
        $code = $this->code($this->ticket(), 'OLDCODE1');

        $this->assertEachRouteRedirectsGuestToLogin([
            ['GET', '/admin/event-ticket-codes', []],
            ['GET', '/admin/event-ticket-codes/create', []],
            ['POST', '/admin/event-ticket-codes', ['code' => 'NEWCODE1', 'event_ticket_id' => $code->event_ticket_id]],
            ['GET', "/admin/event-ticket-codes/{$code->id}", []],
            ['GET', "/admin/event-ticket-codes/{$code->id}/edit", []],
            ['PUT', "/admin/event-ticket-codes/{$code->id}", ['code' => 'HACKED12']],
            ['DELETE', "/admin/event-ticket-codes/{$code->id}", []],
            ['DELETE', '/admin/event-ticket-codes/destroy', ['ids' => [$code->id]]],
        ]);

        $this->assertSame(1, EventTicketCode::count());
        $this->assertSame('OLDCODE1', $code->refresh()->code);
    }
}
