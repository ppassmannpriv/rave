<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Bookkeeping\GetIncomePerPaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class BookkeepingController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('payment_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $events = Event::all();
        $bookkeeping = $events->map(function (Event $event) {
            return [
                'event' => $event,
                'incomeSums' => GetIncomePerPaymentMethod::make()->handle($event)
            ];
        });

        return view('admin.bookkeeping.index', compact('bookkeeping'));
    }
}
