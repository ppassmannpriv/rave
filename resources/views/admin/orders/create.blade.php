@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.order.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.orders.store") }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label class="required" for="user_id">{{ trans('cruds.order.fields.user') }}</label>
            <select class="form-control select2 {{ $errors->has('user') ? 'is-invalid' : '' }}" name="user_id" id="user_id" required>
                @foreach($users as $id => $entry)
                <option value="{{ $id }}" {{ old('user_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                @endforeach
            </select>
            @if($errors->has('user'))
            <div class="invalid-feedback">
                {{ $errors->first('user') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.order.fields.user_helper') }}</span>
        </div>
        <div class="form-group">
            <label class="required" for="payment_id">{{ trans('cruds.order.fields.payment') }}</label>
            <select class="form-control select2 {{ $errors->has('payment') ? 'is-invalid' : '' }}" name="payment_id" id="payment_id" required>
                @foreach($payments as $id => $entry)
                <option value="{{ $id }}" {{ old('payment_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                @endforeach
            </select>
            @if($errors->has('payment'))
            <div class="invalid-feedback">
                {{ $errors->first('payment') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.order.fields.payment_helper') }}</span>
        </div>
        <div class="form-group">
            <label class="required" for="event_ticket_code_id">{{ trans('cruds.order.fields.event_ticket_code') }}</label>
            <select class="form-control select2 {{ $errors->has('event_ticket_code') ? 'is-invalid' : '' }}" name="event_ticket_code_id" id="event_ticket_code_id" required>
                @foreach($event_ticket_codes as $id => $entry)
                <option value="{{ $id }}" {{ old('event_ticket_code_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                @endforeach
            </select>
            @if($errors->has('event_ticket_code'))
            <div class="invalid-feedback">
                {{ $errors->first('event_ticket_code') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.order.fields.event_ticket_code_helper') }}</span>
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
