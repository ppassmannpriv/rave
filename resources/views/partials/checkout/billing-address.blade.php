<fieldset>
    <legend>Your details</legend>
    <div class="mb-3">
        <label for="firstname" class="form-label">Firstname</label>
        <input required type="text" class="form-control" name="firstname" id="firstname">
    </div>
    <div class="mb-3">
        <label for="lastname" class="form-label">Lastname</label>
        <input required type="text" class="form-control" name="lastname" id="lastname">
    </div>
    <div class="mb-3">
        <label for="street" class="form-label">Street and housenumber</label>
        <input required type="text" class="form-control" name="street" id="street">
    </div>
    <div class="row">
        <div class="mb-3 col-sm-4">
            <label for="postcode" class="form-label">Postcode</label>
            <input required type="text" class="form-control" name="postcode" id="postcode">
        </div>
        <div class="mb-3 col-sm-8">
            <label for="city" class="form-label">City</label>
            <input required type="text" class="form-control" name="city" id="city">
        </div>
    </div>
    <div class="mb-3">
        <label for="country" class="form-label">Country</label>
        <select required class="form-control" name="country" id="country">
            <option value="AT">Austria</option>
            <option value="BE">Belgium</option>
            <option value="HR">Croatia</option>
            <option value="CY">Cyprus</option>
            <option value="EE">Estonia</option>
            <option value="FI">Finland</option>
            <option value="FR">France</option>
            <option value="DE" selected>Germany</option>
            <option value="GR">Greece</option>
            <option value="IE">Ireland</option>
            <option value="IT">Italy</option>
            <option value="LV">Latvia</option>
            <option value="LT">Lithuania</option>
            <option value="LU">Luxembourg</option>
            <option value="MT">Malta</option>
            <option value="NL">Netherlands</option>
            <option value="PT">Portugal</option>
            <option value="SK">Slovakia</option>
            <option value="SI">Slovenia</option>
            <option value="ES">Spain</option>
        </select>
    </div>
    <div class="mb-3">
        <label for="exampleInputEmail1" class="form-label">E-Mail</label>
        <input required type="email" name="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
        <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
    </div>
</fieldset>
