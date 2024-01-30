<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class MetricsController
{
    public function index(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $orders = Order::where('deleted_at', '=', null)
            ->whereDate('created_at', '>=', Carbon::now()->subHours(48))
            ->orderBy('created_at')
            ->get();

        $groupedByHours = $orders->groupBy(function ($order) {
                return Carbon::parse($order->created_at)->format('d/m/Y H');
            })->map(fn ($orders) => $orders->count())->sortKeys()->toArray();

        $now = Carbon::now();
        $groupedByHoursLabels = [$now->format('d/m/Y H')];
        for ($h = 0; $h < 48; $h++) {
            $groupedByHoursLabels[] = $now->subHour()->format('d/m/Y H');
        }
        $groupedByHoursLabels = array_reverse($groupedByHoursLabels);

        $displayable = Arr::map($groupedByHoursLabels, function ($label) use ($groupedByHours) {
            return (object)[
                'label' => $label,
                'value' => $groupedByHours[$label] ?? null
            ];
        });

        return view('admin.metrics.index', [
            'orders' => $orders,
            'groupedByHours' => $displayable,
        ]);
    }
}
