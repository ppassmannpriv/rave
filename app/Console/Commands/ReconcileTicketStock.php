<?php

namespace App\Console\Commands;

use App\Models\EventTicket;
use Illuminate\Console\Command;

class ReconcileTicketStock extends Command
{
    protected $signature = 'tickets:reconcile-stock {--apply : Write the suggested stock instead of only printing it}';

    protected $description = 'Compare each ticket\'s stock with cap minus the ticket codes already handed out, and optionally correct it.';

    public function handle(): int
    {
        $rows = [];
        $changes = 0;
        foreach (EventTicket::all() as $eventTicket) {
            $handedOut = $eventTicket->eventTicketSold();
            $suggested = max(0, $eventTicket->cap - $handedOut);
            $rows[] = [$eventTicket->id, $eventTicket->ticket_type, $eventTicket->stock, $eventTicket->cap, $handedOut, $suggested];
            if ($suggested !== (int) $eventTicket->stock) {
                $changes++;
                if ($this->option('apply')) {
                    $eventTicket->stock = $suggested;
                    $eventTicket->save();
                }
            }
        }

        $this->table(['ticket', 'type', 'stock', 'cap', 'codes handed out', 'suggested stock'], $rows);
        $this->info($this->option('apply')
            ? $changes.' ticket(s) corrected.'
            : $changes.' ticket(s) differ. Dry run: nothing written, use --apply to correct them.');

        return 0;
    }
}
