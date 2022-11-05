@extends('layouts.admin')
@section('content')
@can('time_schedule_create')
<div style="margin-bottom: 10px;" class="row">
    <div class="col-lg-12">
        <a class="btn btn-success" href="{{ route('admin.time-schedules.create') }}">
            {{ trans('global.add') }} {{ trans('cruds.time-schedules.title_singular') }}
        </a>
    </div>
</div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.time-schedules.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-TimeSchedules">
                <thead>
                <tr>
                    <th width="10">

                    </th>
                    <th>
                        {{ trans('cruds.time-schedules.fields.id') }}
                    </th>
                    <th>
                        {{ trans('cruds.time-schedules.fields.event') }}
                    </th>
                    <th>
                        {{ trans('cruds.time-schedules.fields.start') }}
                    </th>
                    <th>
                        {{ trans('cruds.time-schedules.fields.end') }}
                    </th>
                    <th>
                        {{ trans('cruds.time-schedules.fields.active') }}
                    </th>
                    <th>
                        &nbsp;
                    </th>
                </tr>
                <tr>
                    <td>
                    </td>
                    <td>
                        <input class="search" type="text" placeholder="{{ trans('global.search') }}">
                    </td>
                    <td>
                        <input class="search" type="text" placeholder="{{ trans('global.search') }}">
                    </td>
                    <td>
                        <input class="search" type="text" placeholder="{{ trans('global.search') }}">
                    </td>
                    <td>
                        <input class="search" type="text" placeholder="{{ trans('global.search') }}">
                    </td>
                    <td>
                        <input class="search" type="text" placeholder="{{ trans('global.search') }}">
                    </td>
                    <td>
                    </td>
                </tr>
                </thead>
                <tbody>
                @foreach($timeSchedules as $key => $timeSchedule)
                <tr data-entry-id="{{ $timeSchedule->id }}">
                    <td>

                    </td>
                    <td>
                        {{ $timeSchedule->id ?? '' }}
                    </td>
                    <td>
                        {{ $timeSchedule->event?->name ?? '' }}
                    </td>
                    <td>
                        {{ $timeSchedule->start ?? '' }}
                    </td>
                    <td>
                        {{ $timeSchedule->end ?? '' }}
                    </td>
                    <td>
                        {{ $timeSchedule->active === 1 ? trans('global.yes') : trans('global.no') }}
                    </td>
                    <td>
                        @can('time_schedule_show')
                        <a class="btn btn-xs btn-primary" href="{{ route('admin.time-schedules.show', $timeSchedule->id) }}">
                            {{ trans('global.view') }}
                        </a>
                        @endcan

                        @can('time_schedule_edit')
                        <a class="btn btn-xs btn-info" href="{{ route('admin.time-schedules.edit', $timeSchedule->id) }}">
                            {{ trans('global.edit') }}
                        </a>
                        @endcan

                        @can('time_schedule_delete')
                        <form action="{{ route('admin.time-schedules.destroy', $timeSchedule->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                        </form>
                        @endcan

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
<script>
    $(function () {
        let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
        @can('time_schedule_delete')
        let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
        let deleteButton = {
            text: deleteButtonTrans,
            url: "{{ route('admin.time-schedules.massDestroy') }}",
            className: 'btn-danger',
            action: function (e, dt, node, config) {
                var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
                    return $(entry).data('entry-id')
                });

                if (ids.length === 0) {
                    alert('{{ trans('global.datatables.zero_selected') }}')

                    return
                }

                if (confirm('{{ trans('global.areYouSure') }}')) {
                    $.ajax({
                        headers: {'x-csrf-token': _token},
                        method: 'POST',
                        url: config.url,
                        data: { ids: ids, _method: 'DELETE' }})
                        .done(function () { location.reload() })
                }
            }
        }
        dtButtons.push(deleteButton)
    @endcan

        $.extend(true, $.fn.dataTable.defaults, {
            orderCellsTop: true,
            order: [[ 1, 'desc' ]],
            pageLength: 100,
        });
        let table = $('.datatable-TimeSchedules:not(.ajaxTable)').DataTable({ buttons: dtButtons })
        $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
            $($.fn.dataTable.tables(true)).DataTable()
                .columns.adjust();
        });

        let visibleColumnsIndexes = null;
        $('.datatable thead').on('input', '.search', function () {
            let strict = $(this).attr('strict') || false
            let value = strict && this.value ? "^" + this.value + "$" : this.value

            let index = $(this).parent().index()
            if (visibleColumnsIndexes !== null) {
                index = visibleColumnsIndexes[index]
            }

            table
                .column(index)
                .search(value, strict)
                .draw()
        });
        table.on('column-visibility.dt', function(e, settings, column, state) {
            visibleColumnsIndexes = []
            table.columns(":visible").every(function(colIdx) {
                visibleColumnsIndexes.push(colIdx);
            });
        })
    })

</script>
@endsection
