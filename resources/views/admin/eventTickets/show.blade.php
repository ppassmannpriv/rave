@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.eventTicket.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.event-tickets.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                <tr>
                    <th>
                        {{ trans('cruds.eventTicket.fields.id') }}
                    </th>
                    <td>
                        {{ $eventTicket->id }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.eventTicket.fields.ticket_type') }}
                    </th>
                    <td>
                        {{ App\Models\EventTicket::TICKET_TYPE_RADIO[$eventTicket->ticket_type] ?? '' }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.eventTicket.fields.price') }}
                    </th>
                    <td>
                        {{ $eventTicket->price }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.eventTicket.fields.event') }}
                    </th>
                    <td>
                        {{ $eventTicket->event->name ?? '' }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.eventTicket.fields.from') }}
                    </th>
                    <td>
                        {{ $eventTicket->from }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.eventTicket.fields.to') }}
                    </th>
                    <td>
                        {{ $eventTicket->to }}
                    </td>
                </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.event-tickets.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection
