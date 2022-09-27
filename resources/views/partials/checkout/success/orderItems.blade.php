<div class="orderItems">
    <ul class="list-group container m-0 p-0" id="cart-list">
        @foreach($order->orderItems as $orderItem)
        <li class="list-group-item d-flex">
            <div class="d-flex name col-sm-6 flex-fill"><span>{{ $orderItem->eventTicket->event->name }}</span></div>
            <div class="d-flex type col-sm-2 text-right flex-fill"><span>{{ $orderItem->eventTicket::TICKET_TYPE_RADIO[$orderItem->eventTicket->ticket_type] }}</span></div>
            <div class="d-flex qty col-sm-2 text-right flex-fill"><span class="w-100">x {{ $orderItem->qty }}</span></div>
            <div class="d-flex price col-sm-2 text-right flex-fill"><span class="w-100">@money($orderItem->row_price)</span></div>
        </li>
        @endforeach
    </ul>
</div>
