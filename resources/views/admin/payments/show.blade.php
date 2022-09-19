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
                        {{ App\Models\Payment::PROVIDER_RADIO[$payment->provider] ?? '' }}
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
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.payments.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection