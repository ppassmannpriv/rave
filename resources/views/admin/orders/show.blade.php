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
                        {{ $order->transaction->reference ?? '' }}
                    </td>
                </tr>
                </tbody>
            </table>
            <fieldset>
                <legend>Order Items</legend>
            </fieldset>
            <table class="table table-bordered table-striped">
                <tbody>
                @foreach($order->orderItems as $orderItem)
                <tr>
                    <th>
                        {{ trans('cruds.orderItems.fields.code') }}
                    </th>
                    <td>
                        {{ $orderItem->eventTicketCode->code }}
                    </td>
                </tr>
                @endforeach
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
