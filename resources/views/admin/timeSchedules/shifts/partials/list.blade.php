@can('time_schedule_shift_create')
<div style="margin-bottom: 10px;" class="row">
    <div class="col-lg-12">
        <a class="btn btn-success" href="{{ route('admin.time-schedules.time-schedule-shifts.create', [$timeSchedule->id]) }}">
            {{ trans('global.add') }} {{ trans('cruds.time-schedule-shifts.title_singular') }}
        </a>
    </div>
</div>
@endcan

<div class="card">
    <div class="card-header">
        {{ trans('cruds.time-schedule-shifts.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-timeScheduleShifts">
                <thead>
                <tr>
                    <th width="10">

                    </th>
                    <th>
                        {{ trans('cruds.time-schedule-shifts.fields.name') }}
                    </th>
                    <th>
                        {{ trans('cruds.time-schedule-shifts.fields.description') }}
                    </th>
                    <th>
                        {{ trans('cruds.time-schedules.fields.start') }}
                    </th>
                    <th>
                        {{ trans('cruds.time-schedules.fields.end') }}
                    </th>
                    <th>
                        {{ trans('cruds.time-schedule-shifts.fields.crew_only') }}
                    </th>
                    <th>
                        &nbsp;
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach($timeScheduleShifts as $key => $timeScheduleShift)
                <tr data-entry-id="{{ $timeScheduleShift->id }}">
                    <td>

                    </td>
                    <td>
                        {{ $timeScheduleShift->name ?? '' }}
                    </td>
                    <td>
                        {{ $timeScheduleShift->description ?? '' }}
                    </td>
                    <td>
                        {{ $timeScheduleShift->start ?? '' }}
                    </td>
                    <td>
                        {{ $timeScheduleShift->end ?? '' }}
                    </td>
                    <td>
                        {{ $timeSchedule->crew_only === 1 ? trans('global.yes') : trans('global.no') }}
                    </td>
                    <td>
                        @can('time_schedule_show')
                        <a class="btn btn-xs btn-primary" href="{{ route('admin.time-schedules.time-schedule-shifts.show', [$timeScheduleShift->timeSchedule->id, $timeScheduleShift->id]) }}">
                            {{ trans('global.view') }}
                        </a>
                        @endcan

                        @can('time_schedule_edit')
                        <a class="btn btn-xs btn-info" href="{{ route('admin.time-schedules.time-schedule-shifts.edit', [$timeScheduleShift->timeSchedule->id, $timeScheduleShift->id]) }}">
                            {{ trans('global.edit') }}
                        </a>
                        @endcan

                        @can('time_schedule_delete')
                        <form action="{{ route('admin.time-schedules.time-schedule-shifts.destroy', [$timeScheduleShift->timeSchedule->id, $timeScheduleShift->id]) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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

@section('scripts')
@parent
<script>
    $(function () {
        let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
        @can('time_schedule_delete')
        let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
        let deleteButton = {
            text: deleteButtonTrans,
            url: "{{ route('admin.time-schedule-shifts.massDestroy') }}",
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
        let table = $('.datatable-timeScheduleShifts:not(.ajaxTable)').DataTable({ buttons: dtButtons })
        $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
            $($.fn.dataTable.tables(true)).DataTable()
                .columns.adjust();
        });

    })

</script>
@endsection
