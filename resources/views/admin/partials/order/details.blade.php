<div class="form-group">
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
                {{ trans('cruds.order.fields.status') }}
            </th>
            <td>
                {{ $order->status ?? '' }}
            </td>
        </tr>
        </tbody>
    </table>
</div>
