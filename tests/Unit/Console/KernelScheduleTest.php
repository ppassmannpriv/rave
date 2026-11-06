<?php

namespace Tests\Unit\Console;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class KernelScheduleTest extends TestCase
{
    public function scheduledCommands(): array
    {
        return [
            'F&F reconciliation' => ['payment_methods:paypal_ff:scan-report', '*/15 * * * *', 10],
            'order finishing' => ['orders:finish', '*/10 * * * *', 10],
            'payment reminders and cancellation' => ['orders:payment-check', '*/5 * * * *', 10],
            'orphaned ticket code cleanup' => ['event-ticket-codes:clean-up-orphans', '0 * * * *', 30],
        ];
    }

    private function eventFor(string $command): Event
    {
        $events = collect($this->app->make(Schedule::class)->events())
            ->filter(fn (Event $event) => str_contains($event->command, $command));
        $this->assertCount(1, $events, $command . ' must be scheduled exactly once');

        return $events->first();
    }

    /**
     * @dataProvider scheduledCommands
     */
    public function test_task_is_scheduled_with_overlap_protection_and_a_bounded_lock(string $command, string $expression, int $maxLockMinutes): void
    {
        $event = $this->eventFor($command);

        $this->assertSame($expression, $event->expression);
        $this->assertTrue($event->withoutOverlapping, 'a slow run must not be started a second time in parallel');
        $this->assertTrue($event->onOneServer, 'only one server may run the task per tick');
        $this->assertGreaterThan(0, $event->expiresAt);
        $this->assertLessThanOrEqual($maxLockMinutes, $event->expiresAt, 'a crashed run must not block the task for longer than this');
    }
}
