@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.eventTicket.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.event-tickets.update", [$eventTicket->id]) }}" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="form-group">
            <label class="required">{{ trans('cruds.eventTicket.fields.ticket_type') }}</label>
            @foreach(App\Models\EventTicket::TICKET_TYPE_RADIO as $key => $label)
            <div class="form-check {{ $errors->has('ticket_type') ? 'is-invalid' : '' }}">
                <input class="form-check-input" type="radio" id="ticket_type_{{ $key }}" name="ticket_type" value="{{ $key }}" {{ old('ticket_type', $eventTicket->ticket_type) === (string) $key ? 'checked' : '' }} required>
                <label class="form-check-label" for="ticket_type_{{ $key }}">{{ $label }}</label>
            </div>
            @endforeach
            @if($errors->has('ticket_type'))
            <div class="invalid-feedback">
                {{ $errors->first('ticket_type') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.eventTicket.fields.ticket_type_helper') }}</span>
        </div>
        <div class="form-group">
            <label class="required" for="price">{{ trans('cruds.eventTicket.fields.price') }}</label>
            <input class="form-control {{ $errors->has('price') ? 'is-invalid' : '' }}" type="number" name="price" id="price" value="{{ old('price', $eventTicket->price) }}" step="0.01" required>
            @if($errors->has('price'))
            <div class="invalid-feedback">
                {{ $errors->first('price') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.eventTicket.fields.price_helper') }}</span>
        </div>
        <div class="form-group">
            <label class="required" for="stock">{{ trans('cruds.eventTicket.fields.stock') }}</label>
            <input class="form-control {{ $errors->has('stock') ? 'is-invalid' : '' }}" type="number" name="stock" id="stock" value="{{ old('stock', $eventTicket->stock) }}" step="1" required>
            @if($errors->has('stock'))
            <div class="invalid-feedback">
                {{ $errors->first('stock') }}
            </div>
            @endif
        </div>
        <div class="form-group">
            <label class="required" for="event_id">{{ trans('cruds.eventTicket.fields.event') }}</label>
            <select class="form-control select2 {{ $errors->has('event') ? 'is-invalid' : '' }}" name="event_id" id="event_id" required>
                @foreach($events as $id => $entry)
                <option value="{{ $id }}" {{ (old('event_id') ? old('event_id') : $eventTicket->event->id ?? '') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                @endforeach
            </select>
            @if($errors->has('event'))
            <div class="invalid-feedback">
                {{ $errors->first('event') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.eventTicket.fields.event_helper') }}</span>
        </div>
        <div class="form-group">
            <label for="from">{{ trans('cruds.eventTicket.fields.from') }}</label>
            <input class="form-control datetime {{ $errors->has('from') ? 'is-invalid' : '' }}" type="text" name="from" id="from" value="{{ old('from', $eventTicket->from) }}">
            @if($errors->has('from'))
            <div class="invalid-feedback">
                {{ $errors->first('from') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.eventTicket.fields.from_helper') }}</span>
        </div>
        <div class="form-group">
            <label for="to">{{ trans('cruds.eventTicket.fields.to') }}</label>
            <input class="form-control datetime {{ $errors->has('to') ? 'is-invalid' : '' }}" type="text" name="to" id="to" value="{{ old('to', $eventTicket->to) }}">
            @if($errors->has('to'))
            <div class="invalid-feedback">
                {{ $errors->first('to') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.eventTicket.fields.to_helper') }}</span>
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
