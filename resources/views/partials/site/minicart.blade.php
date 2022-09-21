@if(isset($cart) && count($cart) > 0)
<div class="c-app flex-row align-items-top">
    <div class="container">
        <div class="content bg-white">
            <div class="row">
                <div class="col-lg-12">
                    <ul class="list-group">
                    @foreach($cart as $cartContent)
                        <li class="list-group-item">{{ $cartContent }}</li>
                    @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
