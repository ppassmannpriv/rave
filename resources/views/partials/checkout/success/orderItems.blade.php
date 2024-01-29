<h5 class="h5">Your items</h5>
<div class="orderItems">
    <ul class="list-group container m-0 p-0" id="cart-list">
        @foreach($order->orderItems as $orderItem)
        <li class="list-group-item d-flex p-0">
            @if ($orderItem->cartItem?->type === 'TICKET')
                <div class="d-flex name col-sm-6 flex-fill border border-right-0 p-3"><span>{{ $orderItem->eventTicket->event->name }}</span></div>
            @elseif($orderItem->cartItem?->type === 'FEE')
                <div class="d-flex name col-sm-6 flex-fill border border-right-0 p-3"><span>Transaction Fee</span></div>
            @elseif($orderItem->cartItem?->type === 'VIRTUAL')
                <div class="d-flex name col-sm-6 flex-fill border border-right-0 p-3"><span>Virtual Product</span></div>
            @endif
            @if ($orderItem->cartItem?->type === 'TICKET')
                <div class="d-flex type col-sm-2 text-right flex-fill border border-right-0 p-3"><span>{{ $orderItem->eventTicket::TICKET_TYPE_RADIO[$orderItem->eventTicket->ticket_type] }}</span></div>
            @elseif($orderItem->cartItem?->type === 'FEE')
                <div class="d-flex type col-sm-2 text-right flex-fill border border-right-0 p-3"><span></span></div>
            @elseif($orderItem->cartItem?->type === 'VIRTUAL')
                    <div class="d-flex type col-sm-2 text-right flex-fill border border-right-0 p-3"><span></span></div>
            @endif
            <div class="d-flex qty col-sm-2 text-right flex-fill border border-right-0 p-3"><span class="w-100">x {{ $orderItem->qty }}</span></div>
            <div class="d-flex price col-sm-2 text-right flex-fill border p-3"><span class="w-100">@money($orderItem->row_price)</span></div>
        </li>
        @endforeach
    </ul>
</div>
