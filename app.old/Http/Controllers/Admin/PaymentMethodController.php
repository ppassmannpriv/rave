<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadCsvPaymentRequest;
use App\Models\PaymentMethod;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Storage;

class PaymentMethodController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('payment_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $paymentMethods = PaymentMethod::all();

        return view('admin.paymentMethods.index', compact('paymentMethods'));
    }

    public function show(PaymentMethod $paymentMethod)
    {
        abort_if(Gate::denies('payment_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.paymentMethods.show', compact('paymentMethod'));
    }

    public function uploadCsv(UploadCsvPaymentRequest $request)
    {
        abort_if(Gate::denies('payment_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $move = Storage::disk('local')->put(PaymentMethod\PayPalFriendsFamily::REPORT_DIR . '/' . PaymentMethod\PayPalFriendsFamily::REPORT_FILE_NAME . '_' . date('Y-m-d-h_i_s') . '.csv', file_get_contents($request->file('csv')));

        return \Redirect::to('/admin/paymentMethods/index');
    }
}
