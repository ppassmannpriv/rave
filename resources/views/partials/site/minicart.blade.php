@if($cart->cartItems->count() > 0)
<div class="c-app flex-row align-items-top">
    <div class="container">
        <div class="content bg-white">
            <div class="row">
                <div class="col-lg-12">
                    <ul class="list-group">
                    @foreach($cart->cartItems as $cartItem)
                        <li class="list-group-item">
                            <span>{{ $cartItem->eventTicket->ticket_type }}</span>
                            <span>{{ $cartItem->eventTicket->price }}</span>
                            <a href="{!! route('cart.remove', ['id' => $cartItem->id]) !!}">Remove</a>
                        </li>
                    @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
