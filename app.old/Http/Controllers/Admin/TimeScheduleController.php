<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\MassDestroyTimeScheduleRequest;
use App\Http\Requests\StoreTimeScheduleRequest;
use App\Http\Requests\UpdateTimeScheduleRequest;
use App\Models\Event;
use App\Models\TimeSchedule;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class TimeScheduleController
{
    public function index()
    {
        abort_if(Gate::denies('time_schedule_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $timeSchedules = TimeSchedule::all();

        return view('admin.timeSchedules.index', compact('timeSchedules'));
    }

    public function create()
    {
        abort_if(Gate::denies('time_schedule_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $events = Event::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.timeSchedules.create', compact('events'));
    }

    public function store(StoreTimeScheduleRequest $request)
    {
        TimeSchedule::create($request->all());

        return redirect()->route('admin.time-schedules.index');
    }

    public function edit(TimeSchedule $timeSchedule)
    {
        abort_if(Gate::denies('time_schedule_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $events = Event::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.timeSchedules.edit', compact('timeSchedule', 'events'));
    }

    public function update(UpdateTimeScheduleRequest $request, TimeSchedule $timeSchedule)
    {
        $timeSchedule->update($request->all());

        return redirect()->route('admin.time-schedules.index');
    }

    public function show(TimeSchedule $timeSchedule)
    {
        abort_if(Gate::denies('time_schedule_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $timeSchedule->load('shifts');

        return view('admin.timeSchedules.show', compact('timeSchedule'));
    }

    public function destroy(TimeSchedule $timeSchedule)
    {
        abort_if(Gate::denies('time_schedule_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $timeSchedule->delete();

        return back();
    }

    public function massDestroy(MassDestroyTimeScheduleRequest $request)
    {
        abort_if(Gate::denies('time_schedule_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        TimeSchedule::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
