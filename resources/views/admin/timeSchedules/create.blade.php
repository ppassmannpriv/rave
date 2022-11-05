@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.time-schedules.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.time-schedules.store") }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label class="required" for="start">{{ trans('cruds.time-schedules.fields.start') }}</label>
            <input class="form-control datetime {{ $errors->has('start') ? 'is-invalid' : '' }}" type="text" name="start" id="start" value="{{ old('start') }}" required>
            @if($errors->has('start'))
            <div class="invalid-feedback">
                {{ $errors->first('start') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.time-schedules.fields.start_helper') }}</span>
        </div>
        <div class="form-group">
            <label class="required" for="end">{{ trans('cruds.time-schedules.fields.end') }}</label>
            <input class="form-control datetime {{ $errors->has('end') ? 'is-invalid' : '' }}" type="text" name="end" id="end" value="{{ old('end') }}" required>
            @if($errors->has('end'))
            <div class="invalid-feedback">
                {{ $errors->first('end') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.time-schedules.fields.end_helper') }}</span>
        </div>
        <div class="form-group">
            <label class="required">{{ trans('cruds.time-schedules.fields.active') }}</label>
            @foreach(['1' => trans('global.yes'), '2' => trans('global.no')] as $key => $label)
            <div class="form-check {{ $errors->has('active') ? 'is-invalid' : '' }}">
                <input class="form-check-input" type="radio" id="active_{{ $key }}" name="active" value="{{ $key }}" {{ old('active', '1') === (string) $key ? 'checked' : '' }} required>
                <label class="form-check-label" for="active_{{ $key }}">{{ $label }}</label>
            </div>
            @endforeach
            @if($errors->has('active'))
            <div class="invalid-feedback">
                {{ $errors->first('active') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.time-schedules.fields.active_helper') }}</span>
        </div>
        <div class="form-group">
            <label class="required" for="event_id">{{ trans('cruds.time-schedules.fields.event') }}</label>
            <select class="form-control select2 {{ $errors->has('event') ? 'is-invalid' : '' }}" name="event_id" id="event_id" required>
                @foreach($events as $id => $entry)
                <option value="{{ $id }}" {{ old('event_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                @endforeach
            </select>
            @if($errors->has('event'))
            <div class="invalid-feedback">
                {{ $errors->first('event') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.time-schedules.fields.event_helper') }}</span>
        </div>
        <div class="form-group">
            <label class="required" for="description">{{ trans('cruds.time-schedules.fields.description') }}</label>
            <textarea class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}" name="description" id="description">{!! old('description') !!}</textarea>
            @if($errors->has('description'))
            <div class="invalid-feedback">
                {{ $errors->first('description') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.time-schedules.fields.description_helper') }}</span>
        </div>
        <div class="form-group">
            <button class="btn btn-danger" type="submit">
                {{ trans('global.save') }}
            </button>
        </div>
        </form>
    </div>
</div>



@endsection


@section('scripts')
<script>
    $(document).ready(function () {
        var allEditors = document.querySelectorAll('.ckeditor');
        for (var i = 0; i < allEditors.length; ++i) {
            ClassicEditor.create(
                allEditors[i], {
                    extraPlugins: []
                }
            );
        }
    });
</script>
@endsection
