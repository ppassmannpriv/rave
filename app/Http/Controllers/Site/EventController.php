<?php

namespace App\Http\Controllers\Site;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends WebController
{
    public function index(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $event = Event::findOrFail(1);
        return $this->respond('events.index', ['event' => $event]);
    }

    public function show($request)
    {
        $event = Event::find($request);
        return $this->respond('events.show', ['event' => $event]);
    }
}
