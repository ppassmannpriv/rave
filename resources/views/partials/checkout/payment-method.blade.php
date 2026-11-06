<fieldset>
    <legend>Payment</legend>
    <div class="row">
        <div class="col-sm-4">
            <div role="group" class="form-group">
                <label for="payment_method" class="form-label">Payment Method</label>
                <select required class="form-control" id="payment_method" name="payment_method">
                    @foreach($paymentMethods as $paymentMethod)
                        <option selected value="{{ $paymentMethod->alias }}">{{ $paymentMethod->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-8">
            @foreach($paymentMethods as $paymentMethod)
                <p class="align-middle" id="payment-method-description-{{ $paymentMethod->id }}">{{ $paymentMethod->description }}</p>
            @endforeach
        </div>
    </div>
</fieldset>
