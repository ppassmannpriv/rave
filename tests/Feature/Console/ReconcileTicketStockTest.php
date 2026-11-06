<?php

namespace Tests\Feature\Console;

use App\Events\EventTicketCreatedEvent;
use App\Models\EventTicket;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReconcileTicketStockTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Event::fake([EventTicketCreatedEvent::class]);

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
    }

    private function ticketWithHandedOutCodes(int $cap, int $stock, int $handedOut): EventTicket
    {
        $ticket = EventTicket::create(['ticket_type' => 'regular', 'price' => '25.00', 'cap' => $cap, 'stock' => $stock]);
        for ($i = 0; $i < $cap; $i++) {
            DB::table('event_ticket_codes')->insert([
                'code' => 'T'.$ticket->id.'-'.$i,
                'event_ticket_id' => $ticket->id,
                'order_item_id' => $i < $handedOut ? 1000 + $i : null,
            ]);
        }

        return $ticket;
    }

    public function test_dry_run_reports_the_difference_and_changes_nothing(): void
    {
        $drifted = $this->ticketWithHandedOutCodes(cap: 10, stock: 15, handedOut: 3);

        $this->artisan('tickets:reconcile-stock')
            ->expectsOutputToContain('1 ticket(s) differ')
            ->assertExitCode(0);

        $this->assertSame(15, (int) $drifted->refresh()->stock);
    }

    public function test_apply_sets_stock_to_cap_minus_handed_out_codes_and_leaves_correct_tickets_alone(): void
    {
        $drifted = $this->ticketWithHandedOutCodes(cap: 10, stock: 15, handedOut: 3);
        $correct = $this->ticketWithHandedOutCodes(cap: 5, stock: 5, handedOut: 0);

        $this->artisan('tickets:reconcile-stock', ['--apply' => true])
            ->expectsOutputToContain('1 ticket(s) corrected')
            ->assertExitCode(0);

        $this->assertSame(7, (int) $drifted->refresh()->stock);
        $this->assertSame(5, (int) $correct->refresh()->stock);
    }

    public function test_suggested_stock_never_goes_below_zero(): void
    {
        $oversold = $this->ticketWithHandedOutCodes(cap: 2, stock: 4, handedOut: 2);
        DB::table('event_ticket_codes')->insert(['code' => 'EXTRA', 'event_ticket_id' => $oversold->id, 'order_item_id' => 2000]);

        $this->artisan('tickets:reconcile-stock', ['--apply' => true])->assertExitCode(0);

        $this->assertSame(0, (int) $oversold->refresh()->stock);
    }
}
