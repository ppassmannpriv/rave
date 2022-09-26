@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.order.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.orders.update", [$order->id]) }}" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="form-group">
            <label class="required" for="user_id">{{ trans('cruds.order.fields.user') }}</label>
            <select class="form-control select2 {{ $errors->has('user') ? 'is-invalid' : '' }}" name="user_id" id="user_id" required>
                @foreach($users as $id => $entry)
                <option value="{{ $id }}" {{ (old('user_id') ? old('user_id') : $order->user->id ?? '') == $id ? 'selected' : '' }}>{{ $id }}: {{ $entry }}</option>
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
            <label class="required" for="status">{{ trans('cruds.order.fields.status') }}</label>
            <select class="form-control select2 {{ $errors->has('status') ? 'is-invalid' : '' }}" name="status" id="status" required>
                @foreach($orderStatuses as $status)
                <option value="{{ $status }}" {{ (old('status') ? old('status') : $order->status ?? '') == $status ? 'selected' : '' }}>{{ $status }}</option>
                @endforeach
            </select>
            @if($errors->has('user'))
            <div class="invalid-feedback">
                {{ $errors->first('user') }}
            </div>
            @endif
            <span class="help-block">{{ trans('cruds.order.fields.user_helper') }}</span>
        </div>
        @include('admin.partials.order.details')
        @include('admin.partials.order.orderItems')
        <div class="form-group">
            <button class="btn btn-danger" type="submit">
                {{ trans('global.save') }}
            </button>
        </div>
        </form>
    </div>
</div>



@endsection
