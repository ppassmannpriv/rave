<div class="card">
    <div class="card-header">
        {{ trans('cruds.order.title') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-Order">
                <thead>
                <tr>
                    <th>
                        {{ trans('cruds.order.fields.id') }}
                    </th>
                    <th>
                        {{ trans('cruds.user.fields.email') }}
                    </th>
                    <th>
                        {{ trans('cruds.order.fields.qty') }}
                    </th>
                    <th>
                        {{ trans('cruds.order.fields.status') }}
                    </th>
                </tr>
                <tr>
                    <td>
                    </td>
                    <td>
                    </td>
                    <td>
                    </td>
                    <td>
                    </td>
                </tr>
                </thead>
                <tbody>
                @foreach($orders as $key => $order)
                <tr data-entry-id="{{ $order->id }}">
                    <td>
                        {{ $order->id ?? '' }}
                    </td>
                    <td>
                        {{ $order->user->email ?? '' }}
                    </td>
                    <td>
                        {{ $order->quantityItems() ?? '?' }}
                    </td>
                    <td>
                        {{ $order->status ?? '' }}
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
