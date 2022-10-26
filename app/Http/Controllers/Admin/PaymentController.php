<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyPaymentRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Transaction;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('payment_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $transactions = Transaction::all();

        return view('admin.payments.index', compact('transactions'));
    }

    public function create()
    {
        abort_if(Gate::denies('payment_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.payments.create');
    }

    public function store(StorePaymentRequest $request)
    {
        $transaction = Transaction::create($request->all());

        return redirect()->route('admin.payments.index');
    }

    public function edit(Transaction $payment)
    {
        abort_if(Gate::denies('payment_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.payments.edit', compact('payment'));
    }

    public function update(UpdatePaymentRequest $request, Transaction $transaction)
    {
        $transactionData = [
            'amount' => $request->get('amount'),
            'state' => $request->get('state'),
            'reference' => $request->get('reference'),
            'payment_method_id' => $request->get('provider'),
        ];
        $transaction->find(request()->route('payment'))?->update($transactionData);

        return redirect()->route('admin.payments.index');
    }

    public function show(Transaction $payment)
    {
        abort_if(Gate::denies('payment_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.payments.show', compact('payment'));
    }

    public function destroy(Transaction $transaction)
    {
        abort_if(Gate::denies('payment_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $transaction->delete();

        return back();
    }

    public function massDestroy(MassDestroyPaymentRequest $request)
    {
        Transaction::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
