
@extends('layouts.app')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    {{ $contentPage['title'] }}
                </div>

                <div class="card-body">
                    <!--<div class="alert alert-success" role="alert">
                        asdf
                    </div>-->
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
