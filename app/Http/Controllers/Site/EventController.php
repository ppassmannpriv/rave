<?php

namespace App\Http\Controllers\Site;

use App\Models\Event;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class EventController extends WebController
{
    public function index(Request $request): Factory|View|Application
    {
        // $events = Event::where('end', '<=', Carbon::now())->get();
        $events = Event::all()->filter(fn($event) => !$event->isOver());
        return $this->respond('events.index', ['events' => $events, 'siteType' => 'events']);
    }

    public function show($request)
    {
        $event = Event::find($request);
        return $this->respond('events.show', ['event' => $event, 'siteType' => 'events']);
    }
}
