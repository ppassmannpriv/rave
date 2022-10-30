@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Scheduled Tasks
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-Payment">
                <thead>
                <tr>
                    <th>
                        Expression
                    </th>
                    <th>
                        Next run
                    </th>
                    <th>
                        Command
                    </th>
                    <th>
                        Log
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach($scheduledEvents as $scheduledEvent)
                <tr>
                    <td>
                        {{ $scheduledEvent->getExpression() }}
                    </td>
                    <td>
                        {{ $scheduledEvent->nextRunDate()->format('Y-m-d H:i:s') }}
                    </td>
                    <td>
                        {{ $scheduledEvent->command }}
                    </td>
                    <td>
                        {{ $scheduledEvent->output }}
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>



@endsection
@section('scripts')
@parent
@endsection
