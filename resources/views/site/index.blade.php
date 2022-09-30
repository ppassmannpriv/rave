
@extends('layouts.app')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="card rounded-0">
                <div class="card-header">
                    <h2 class="h4 m-0">{{ $contentPage['title'] }}</h2>
                </div>

                <div class="card-body">
                    {!! $contentPage['page_text'] !!}
                    {{ $contentPage->getFeaturedImageAttribute() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
@parent

@endsection
