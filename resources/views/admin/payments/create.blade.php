@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.payment.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.payments.store") }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label class="required">{{ trans('cruds.payment.fields.provider') }}</label>
            @foreach(App\Models\Payment::PROVIDER_RADIO as $key => $label)
            <div class="form-check {{ $errors->has('provider') ? 'is-invalid' : '' }}">
                <input class="form-check-input" type="radio" id="provider_{{ $key }}" name="provider" value="{{ $key }}" {{ old('provider', 'paypal_friends_family') === (string) $key ? 'checked' : '' }} required>
                <label class="form-check-label" for="provider_{{ $key }}">{{ $label }}</label>
            </div>
            @endforeach
            @if($errors->has('provider'))
            <div class="invalid-feedback">
                {{ $errors->first('provider') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.payment.fields.provider_helper') }}</span>
        </div>
        <div class="form-group">
            <label class="required" for="amount">{{ trans('cruds.payment.fields.amount') }}</label>
            <input class="form-control {{ $errors->has('amount') ? 'is-invalid' : '' }}" type="number" name="amount" id="amount" value="{{ old('amount', '') }}" step="0.01" required>
            @if($errors->has('amount'))
            <div class="invalid-feedback">
                {{ $errors->first('amount') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.payment.fields.amount_helper') }}</span>
        </div>
        <div class="form-group">
            <label class="required">{{ trans('cruds.payment.fields.state') }}</label>
            <select class="form-control {{ $errors->has('state') ? 'is-invalid' : '' }}" name="state" id="state" required>
                <option value disabled {{ old('state', null) === null ? 'selected' : '' }}>{{ trans('global.pleaseSelect') }}</option>
                @foreach(App\Models\Payment::STATE_SELECT as $key => $label)
                <option value="{{ $key }}" {{ old('state', '') === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @if($errors->has('state'))
            <div class="invalid-feedback">
                {{ $errors->first('state') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.payment.fields.state_helper') }}</span>
        </div>
        <div class="form-group">
            <label class="required" for="reference">{{ trans('cruds.payment.fields.reference') }}</label>
            <input class="form-control {{ $errors->has('reference') ? 'is-invalid' : '' }}" type="text" name="reference" id="reference" value="{{ old('reference', '') }}" required>
            @if($errors->has('reference'))
            <div class="invalid-feedback">
                {{ $errors->first('reference') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.payment.fields.reference_helper') }}</span>
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
