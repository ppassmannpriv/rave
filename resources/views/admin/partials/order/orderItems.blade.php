<div class="form-group">
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
                @foreach($orderItem->eventTicketCodes as $eventTicketCode)
                    {{ $eventTicketCode->code }}
                @endforeach
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
