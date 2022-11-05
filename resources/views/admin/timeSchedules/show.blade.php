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

<div class="card">
    <div class="card-header">
        {{ trans('global.relatedData') }}
    </div>
    <ul class="nav nav-tabs" role="tablist" id="relationship-tabs">
        <li class="nav-item">
            <a class="nav-link" href="#shifts" role="tab" data-toggle="tab">
                {{ trans('cruds.time-schedule-shifts.title') }}
            </a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane" role="tabpanel" id="shifts">
            @includeIf('admin.time-schedules.relationships.shifts', ['shifts' => $timeSchedule->shifts])
        </div>
    </div>
</div>

@endsection
