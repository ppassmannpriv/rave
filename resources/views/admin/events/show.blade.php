@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.event.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.events.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                <tr>
                    <th>
                        {{ trans('cruds.event.fields.id') }}
                    </th>
                    <td>
                        {{ $event->id }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.event.fields.name') }}
                    </th>
                    <td>
                        {{ $event->name }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.event.fields.start') }}
                    </th>
                    <td>
                        {{ $event->start }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.event.fields.end') }}
                    </th>
                    <td>
                        {{ $event->end }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.event.fields.location') }}
                    </th>
                    <td>
                        {{ $event->location }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.event.fields.description') }}
                    </th>
                    <td>
                        {!! $event->description !!}
                    </td>
                </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.events.index') }}">
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
            <a class="nav-link" href="#event_tickets" role="tab" data-toggle="tab">
                {{ trans('cruds.eventTicket.title') }}
            </a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane" role="tabpanel" id="event_tickets">
            @includeIf('admin.events.relationships.eventTickets', ['eventTickets' => $event->eventTickets])
        </div>
    </div>
</div>

@endsection
