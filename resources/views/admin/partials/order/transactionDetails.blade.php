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
                    {{ $order->transaction->reference ?? '' }}
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
                    {{ $order->transaction->provider ?? '' }}
                </td>
            </tr>
        </tbody>
    </table>
</div>
