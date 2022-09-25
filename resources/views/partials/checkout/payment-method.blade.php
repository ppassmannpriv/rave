<fieldset>
    <legend>Payment</legend>
    <div class="row">
        <div class="col-sm-4">
            <div role="group" class="form-group">
                <label for="payment_method" class="form-label">Payment Method</label>
                <select required class="form-control" id="payment_method" name="payment_method">
                    @foreach($paymentMethods as $paymentMethod)
                        <option selected disabled value="{{ $paymentMethod->alias }}">{{ $paymentMethod->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-8">
            <p class="align-middle">PayPal Friends & Family - We will generate a code for you, that you will have to use as a subject after ordering. Make sure to do this within the following 24h after you ordered otherwise we will cancel your order!</p>
        </div>
    </div>
</fieldset>
