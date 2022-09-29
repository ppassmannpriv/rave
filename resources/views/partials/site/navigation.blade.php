<nav class="navbar navbar-expand-lg p-0">
    <a id="logo" class="navbar-brand p-4 pl-5 pr-5 m-0 border" href="{{ route('site.index') }}">Schleuse Eins</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    @if(isset($pages))
    <div class="collapse navbar-collapse p-0 m-0" id="navbarNav">
        <div class="navbar-nav">
            @foreach($pages as $page)
                <div class="d-flex {{ $page->isActive() ? "active" : "" }}">
                    @if ($page->isActive())
                    <a class="nav-link nav-item pl-5 pr-5 border-right border-bottom border-top" href="#">{{ $page->title }}<span class="sr-only">(current)</span></a>
                    @else
                    <a class="nav-link nav-item pl-5 pr-5 border-right border-bottom border-top" href="{{ route($page->path) }}">{{ $page->title }}</a>
                    @endif
                </div>
            @endforeach
            <div class="d-flex ">
                <a class="nav-link nav-item pl-5 pr-5 border-right border-bottom border-top" href="{{ route('events.list') }}">Events</a>
            </div>
            <div class="d-flex ">
                <a class="nav-link nav-item pl-5 pr-5 border-right border-bottom border-top" href="{{ route('cart.index') }}">Cart</a>
            </div>
        </div>
    </div>
    @endif
</nav>
