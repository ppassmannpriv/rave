@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.payment.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.payments.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                <tr>
                    <th>
                        {{ trans('cruds.payment.fields.id') }}
                    </th>
                    <td>
                        {{ $payment->id }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.payment.fields.provider') }}
                    </th>
                    <td>
                        {{ $payment->paymentMethod->name ?? '' }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.payment.fields.amount') }}
                    </th>
                    <td>
                        {{ $payment->amount }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.payment.fields.state') }}
                    </th>
                    <td>
                        {{ App\Models\Payment::STATE_SELECT[$payment->state] ?? '' }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.payment.fields.reference') }}
                    </th>
                    <td>
                        {{ $payment->reference }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.orders.title') }}
                    </th>
                    <td>
                        <a href="{{ route('admin.orders.show', $payment->order->id) }}">
                            {{ $payment->order->id }}
                        </a>
                    </td>
                </tr>
                </tbody>
            </table>
            <fieldset>
                <legend>Transaction History</legend>
            </fieldset>
            @foreach($payment->partialTransactions as $partial)
            <table class="table table-bordered table-striped">
                <tbody>
                <tr>
                    <th>
                        {{ trans('cruds.transactionPartials.fields.id') }}
                    </th>
                    <td>
                        {{ $partial->id }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.transactionPartials.fields.amount') }}
                    </th>
                    <td>
                        {{ $partial->amount ?? '' }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.transactionPartials.fields.created_at') }}
                    </th>
                    <td>
                        {{ $partial->created_at ?? '' }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.transactionPartials.fields.raw') }}
                    </th>
                    <td>
                        {{ $partial->raw ?? '' }}
                    </td>
                </tr>
                </tbody>
            </table>
            @endforeach
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.payments.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection
