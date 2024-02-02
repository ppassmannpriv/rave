@extends('layouts.admin')
@section('content')
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.metrics.title_singular') }}
        </div>

        <div class="card-body">
            <h2>Orders in the last 24h</h2>
            <canvas id="ordersData"></canvas>
            <h2>Orders in the last week</h2>
            <canvas id="ordersDataWeekly"></canvas>
        </div>
    </div>

@endsection
@section('scripts')
    @parent
    <script src="{{ asset('js/chart.js') }}"></script>
    <script>
        const orders = @json($orders);
        const groupedByHours = @json($groupedByHours);
        const groupedByDays = @json($groupedByDays);
        const ctx = document.getElementById('ordersData');
        const ordersChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: groupedByHours.map(entry => entry.label),
                datasets: [
                    {
                        label: 'Orders by hours (last 48h)',
                        data: groupedByHours.map(entry => entry.value)
                    }
                ]
            },
            options: {

            }
        });
        const ctxW = document.getElementById('ordersDataWeekly');
        const ordersChartW = new Chart(ctxW, {
            type: 'bar',
            data: {
                labels: groupedByDays.map(entry => entry.label),
                datasets: [
                    {
                        label: 'Orders by days (last week)',
                        data: groupedByDays.map(entry => entry.value)
                    }
                ]
            },
            options: {

            }
        });
    </script>
@endsection
