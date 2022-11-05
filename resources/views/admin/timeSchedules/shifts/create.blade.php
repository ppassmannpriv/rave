@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.time-schedule-shifts.title_singular') }} - {{ $timeSchedule?->event->name ?? '' }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.time-schedules.time-schedule-shifts.store", [$timeSchedule->id]) }}" enctype="multipart/form-data">
        @method('POST')
        @csrf
        <div class="form-group">
            <label class="required" for="name">{{ trans('cruds.time-schedule-shifts.fields.name') }}</label>
            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', '') }}" required>
            @if($errors->has('name'))
            <div class="invalid-feedback">
                {{ $errors->first('name') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.time-schedule-shifts.fields.start_helper') }}</span>
        </div>
        <div class="form-group">
            <label class="required" for="start">{{ trans('cruds.time-schedule-shifts.fields.start') }}</label>
            <input class="form-control datetime {{ $errors->has('start') ? 'is-invalid' : '' }}" type="text" name="start" id="start" value="{{ old('start', '') }}" placeholder="{{ $timeSchedule->start }}" required>
            @if($errors->has('start'))
            <div class="invalid-feedback">
                {{ $errors->first('start') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.time-schedule-shifts.fields.start_helper') }}</span>
        </div>
        <div class="form-group">
            <label class="required" for="end">{{ trans('cruds.time-schedule-shifts.fields.end') }}</label>
            <input class="form-control datetime {{ $errors->has('end') ? 'is-invalid' : '' }}" type="text" name="end" id="end" value="{{ old('end', '') }}" placeholder="{{ $timeSchedule->end }}" required>
            @if($errors->has('end'))
            <div class="invalid-feedback">
                {{ $errors->first('end') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.time-schedule-shifts.fields.end_helper') }}</span>
        </div>
        <div class="form-group">
            <label class="required" for="repeat">{{ trans('cruds.time-schedule-shifts.fields.repeat') }}</label>
            <input class="form-control {{ $errors->has('repeat') ? 'is-invalid' : '' }}" type="number" name="repeat" id="repeat" value="{{ old('repeat', 0) }}"  required>
            @if($errors->has('repeat'))
            <div class="invalid-feedback">
                {{ $errors->first('repeat') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.time-schedule-shifts.fields.repeat_helper') }}</span>
        </div>
        <div class="form-group">
            <label class="required">{{ trans('cruds.time-schedule-shifts.fields.crew_only') }}</label>
            @foreach(['1' => trans('global.yes'), '0' => trans('global.no')] as $key => $label)
            <div class="form-check {{ $errors->has('active') ? 'is-invalid' : '' }}">
                <input class="form-check-input" type="radio" id="crew_only_{{ $key }}" name="crew_only" value="{{ $key }}" {{ old('crew_only') === $key ? 'checked' : '' }} required>
                <label class="form-check-label" for="crew_only_{{ $key }}">{{ $label }}</label>
            </div>
            @endforeach
            @if($errors->has('crew_only'))
            <div class="invalid-feedback">
                {{ $errors->first('crew_only') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.time-schedule-shifts.fields.crew_only_helper') }}</span>
        </div>
        <div class="form-group">
            <label for="description">{{ trans('cruds.time-schedule-shifts.fields.description') }}</label>
            <textarea class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}" name="description" id="description">{!! old('description', '') !!}</textarea>
            @if($errors->has('description'))
            <div class="invalid-feedback">
                {{ $errors->first('description') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.time-schedule-shifts.fields.description_helper') }}</span>
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

