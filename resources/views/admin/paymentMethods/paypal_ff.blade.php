<div class="row">
    <div class="col-lg-12">
        <p>To validate your PayPal Friends & Family payments please first login to PayPal with the browser you are now using. After that find this screen: <a href="https://www.paypal.com/reports/dlog" target="_blank">Reports DLOG Screen</a></p>
        <p>Here you can generate a CSV File that contains all transactions in a date range. This needs to be uploaded here.</p>
        <div class="form-group mt-5">
            <form action="{{ route('admin.paymentMethods.uploadCsv') }}" method="post" enctype="multipart/form-data">
                @csrf
                <fieldset>
                    <legend>Upload PayPal Transaction Report CSV</legend>
                </fieldset>
                <fieldset>
                    <div class="mb-3">
                        <label for="csv" class="form-label">CSV</label>
                        <input class="form-control" type="file" id="csv" name="csv">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-success">Upload</button>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</div>
