@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.time-schedules.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.time-schedules.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                <tr>
                    <th>
                        {{ trans('cruds.time-schedules.fields.id') }}
                    </th>
                    <td>
                        {{ $timeSchedule->id }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.time-schedules.fields.event') }}
                    </th>
                    <td>
                        {{ $timeSchedule->event?->name ?? '' }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.time-schedules.fields.start') }}
                    </th>
                    <td>
                        {{ $timeSchedule->start }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.time-schedules.fields.end') }}
                    </th>
                    <td>
                        {{ $timeSchedule->end }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.time-schedules.fields.description') }}
                    </th>
                    <td>
                        {!! $timeSchedule->description !!}
                    </td>
                </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.time-schedules.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>
@includeIf('admin.timeSchedules.shifts.partials.list', ['timeSchedule' => $timeSchedule, 'timeScheduleShifts' => $timeSchedule->shifts])

@endsection
