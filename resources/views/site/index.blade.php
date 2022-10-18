
@extends('layouts.app')
@section('content')
<div class="content col-lg-5 col-md-7 col-sm-12 p-0">
    <div class="row">
        <div class="col-lg-12">
            <div class="card rounded-0">
                <div class="card-header pl-5">
                    <h2 class="h4 m-0">{{ $contentPage['title'] ?? ''}}</h2>
                </div>

                <div class="card-body pl-5">
                    {!! $contentPage->page_text !!}
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
