@if(isset($cart) && $cart->cartItems->count() > 0)

    <div class="container-fluid p-0">
        <div class="content bg-black border" id="minicart">
            <div class="row">
                <div class="col-lg-12">
                    <ul class="list-group">
                    @foreach($cart->cartItems as $cartItem)
                        <li class="list-group-item d-flex">
                            <span class="d-flex name col-sm-6">{{ $cartItem->eventTicket->event->name }}</span>
                            <span class="d-flex type col-sm-2 text-right">{{ $cartItem->eventTicket::TICKET_TYPE_RADIO[$cartItem->eventTicket->ticket_type] }}</span>
                            <span class="d-flex qty col-sm-1 text-right">x {{ $cartItem->qty }}</span>
                            <span class="d-flex price col-sm-2 text-right">@money($cartItem->row_price)</span>
                            <a class="d-flex remove col-sm-1 text-right" href="{!! route('cart.remove', ['id' => $cartItem->id]) !!}">Remove</a>
                            <!--<span>type: {{ $cartItem->eventTicket->ticket_type }}</span>
                            <span>single price: {{ $cartItem->single_price }}</span>
                            <span>x {{ $cartItem->qty }}</span>
                            <span>row price: {{ $cartItem->row_price }}</span>
                            <a href="{!! route('cart.remove', ['id' => $cartItem->id]) !!}">Remove</a>-->
                        </li>
                    @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

@endif
