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

    public function create(TimeSchedule $timeSchedule)
    {
        abort_if(Gate::denies('time_schedule_shift_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.timeSchedules.shifts.create', compact('timeSchedule'));
    }

    public function store(StoreTimeScheduleShiftRequest $request, TimeSchedule $timeSchedule)
    {
        $data = [...$request->all(), 'time_schedule_id' => $timeSchedule->id];

        TimeSchedule\Shift::createRepeating($data);

        return redirect()->route('admin.time-schedules.show', [$timeSchedule->id]);
    }

    public function edit(TimeSchedule $timeSchedule, TimeSchedule\Shift $timeScheduleShift)
    {
        abort_if(Gate::denies('time_schedule_shift_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.timeSchedules.shifts.edit', compact('timeScheduleShift', 'timeSchedule'));
    }

    public function update(TimeSchedule $timeSchedule, UpdateTimeScheduleShiftRequest $request, TimeSchedule\Shift $timeScheduleShift)
    {
        $timeScheduleShift->update($request->all());

        return redirect()->route('admin.time-schedules.show', [$timeSchedule->id]);
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
