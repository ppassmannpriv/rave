<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Events\Dispatcher;

class TaskScheduleController
{
    public function index(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $scheduledEvents = $this->getScheduledJobs();

        return view('admin.tasks.schedule.index', [
            'scheduledEvents' => $scheduledEvents,
        ]);
    }

    private function getScheduledJobs()
    {
        new \App\Console\Kernel(app(), new Dispatcher());
        $schedule = app(Schedule::class);

        return collect($schedule->events());
    }
}
