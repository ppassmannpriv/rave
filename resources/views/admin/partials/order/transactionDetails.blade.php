<div class="form-group">
    <fieldset>
        <legend>Transaction details</legend>
    </fieldset>
    <table class="table table-bordered table-striped">
        <tbody>
            <tr>
                <th>
                    {{ trans('cruds.order.fields.payment') }}
                </th>
                <td>
                    <a href="{{ route('admin.payments.show', $order->transaction->id) }}">
                        {{ $order->transaction->reference ?? '' }}
                    </a>
                </td>
            </tr>
            <tr>
                <th>
                    {{ trans('cruds.order.fields.payment') }} {{ trans('cruds.order.fields.status') }}
                </th>
                <td>
                    {{ $order->transaction->state ?? '' }}
                </td>
            </tr>
            <tr>
                <th>
                    {{ trans('cruds.payment.fields.amount') }}
                </th>
                <td>
                    {{ $order->transaction->amount ?? '' }}
                </td>
            </tr>
            <tr>
                <th>
                    {{ trans('cruds.payment.fields.provider') }}
                </th>
                <td>
                    {{ $order->transaction->paymentMethod->name ?? '' }}
                </td>
            </tr>
        </tbody>
    </table>
</div>
