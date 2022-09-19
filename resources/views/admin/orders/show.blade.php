@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.order.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.orders.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                <tr>
                    <th>
                        {{ trans('cruds.order.fields.id') }}
                    </th>
                    <td>
                        {{ $order->id }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.order.fields.user') }}
                    </th>
                    <td>
                        {{ $order->user->name ?? '' }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.order.fields.payment') }}
                    </th>
                    <td>
                        {{ $order->payment->reference ?? '' }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.order.fields.event_ticket_code') }}
                    </th>
                    <td>
                        {{ $order->event_ticket_code->code ?? '' }}
                    </td>
                </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.orders.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection