<fieldset>
    <legend>Payment</legend>
    <div class="row">
        @if(count($paymentMethods) === 1)
            <div class="col-sm-12">
                @foreach($paymentMethods as $paymentMethod)
                    <p class="align-middle payment-method-description" id="payment-method-description-{{ $paymentMethod->id }}">{!! $paymentMethod->description !!}</p>
                    <input type="hidden" name="payment_method" id="payment_method" value="{{ $paymentMethod->alias }}" />
                @endforeach
            </div>
        @else
        <div class="col-sm-4">
            <div role="group" class="form-group">
                <select
                    required
                    class="form-control"
                    id="payment_method"
                    name="payment_method"
                >
                    @foreach($paymentMethods as $paymentMethod)
                        <option selected value="{{ $paymentMethod->alias }}">{{ $paymentMethod->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-8">
            @foreach($paymentMethods as $paymentMethod)
                <p class="align-middle payment-method-description" id="payment-method-description-{{ $paymentMethod->id }}">{!! $paymentMethod->description !!}</p>
            @endforeach
        </div>
        @endif
    </div>
</fieldset>
