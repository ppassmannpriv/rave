@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.eventTicketCode.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.event-ticket-codes.update", [$eventTicketCode->id]) }}" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="form-group">
            <label class="required" for="code">{{ trans('cruds.eventTicketCode.fields.code') }}</label>
            <input class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}" type="text" name="code" id="code" value="{{ old('code', $eventTicketCode->code) }}" required>
            @if($errors->has('code'))
            <div class="invalid-feedback">
                {{ $errors->first('code') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.eventTicketCode.fields.code_helper') }}</span>
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