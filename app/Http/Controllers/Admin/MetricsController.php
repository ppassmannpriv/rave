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
            ->whereDate('created_at', '>=', Carbon::now()->subHours(24))
            ->orderBy('created_at')
            ->get();

        $groupedByHours = $orders->groupBy(function ($order) {
                return Carbon::parse($order->created_at)->format('d/m/Y H');
            })->map(fn ($orders) => $orders->count())->sortKeys()->toArray();

        $now = Carbon::now();
        $groupedByHoursLabels = [$now->format('d/m/Y H')];
        for ($h = 0; $h < 24; $h++) {
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
            'groupedByDays' => $this->weeklyData(),
        ]);
    }

    private function weeklyData(): array
    {
        $orders = Order::where('deleted_at', '=', null)
            ->whereDate('created_at', '>=', Carbon::now()->subWeek())
            ->orderBy('created_at')
            ->get();

        $groupedByDays = $orders->groupBy(function ($order) {
            return Carbon::parse($order->created_at)->format('d/m/Y');
        })->map(fn ($orders) => $orders->count())->sortKeys()->toArray();

        $now = Carbon::now();
        $groupedByDaysLabels = [$now->format('d/m/Y')];
        for ($d = 0; $d < 7; $d++) {
            $groupedByDaysLabels[] = $now->subDay()->format('d/m/Y');
        }
        $groupedByDaysLabels = array_reverse($groupedByDaysLabels);

        $displayable = Arr::map($groupedByDaysLabels, function ($label) use ($groupedByDays) {
            return (object)[
                'label' => $label,
                'value' => $groupedByDays[$label] ?? null
            ];
        });
        return $displayable;
    }
}
