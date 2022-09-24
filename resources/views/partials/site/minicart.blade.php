@if($cart->cartItems->count() > 0)

    <div class="container">
        <div class="content bg-white">
            <div class="row">
                <div class="col-lg-12">
                    <ul class="list-group">
                    @foreach($cart->cartItems as $cartItem)
                        <li class="list-group-item">
                            <span>type: {{ $cartItem->eventTicket->ticket_type }}</span>
                            <span>single price: {{ $cartItem->single_price }}</span>
                            <span>x {{ $cartItem->qty }}</span>
                            <span>row price: {{ $cartItem->row_price }}</span>
                            <a href="{!! route('cart.remove', ['id' => $cartItem->id]) !!}">Remove</a>
                        </li>
                    @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

@endif
