<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\MassDestroyTimeScheduleShiftRequest;
use App\Http\Requests\StoreTimeScheduleShiftRequest;
use App\Http\Requests\UpdateTimeScheduleShiftRequest;
use App\Models\TimeSchedule;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class TimeScheduleShiftController
{
    public function index()
    {
        abort_if(Gate::denies('time_schedule_shift_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $timeScheduleShifts = TimeSchedule\Shift::all();

        return view('admin.timeSchedules.shifts.index', compact('timeScheduleShifts'));
    }

    public function create()
    {
        abort_if(Gate::denies('time_schedule_shift_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.timeSchedules.shifts.create');
    }

    public function store(StoreTimeScheduleShiftRequest $request)
    {
        TimeSchedule\Shift::create($request->all());

        return redirect()->route('admin.time-schedule-shifts.index');
    }

    public function edit(TimeSchedule\Shift $timeScheduleShift)
    {
        abort_if(Gate::denies('time_schedule_shift_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.timeSchedules.edit', compact('timeScheduleShift'));
    }

    public function update(UpdateTimeScheduleShiftRequest $request, TimeSchedule\Shift $timeScheduleShift)
    {
        $timeScheduleShift->update($request->all());

        return redirect()->route('admin.time-schedule-shifts.index');
    }

    public function show(TimeSchedule\Shift $timeScheduleShift)
    {
        abort_if(Gate::denies('time_schedule_shift_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.timeSchedules.show', compact('timeScheduleShift'));
    }

    public function destroy(TimeSchedule\Shift $timeScheduleShift)
    {
        abort_if(Gate::denies('time_schedule_shift_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $timeScheduleShift->delete();

        return back();
    }

    public function massDestroy(MassDestroyTimeScheduleShiftRequest $request)
    {
        abort_if(Gate::denies('time_schedule_shift_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        TimeSchedule\Shift::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
