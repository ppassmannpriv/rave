<nav class="navbar navbar-expand-lg p-0">
    @include('partials.site.logo')
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <div class="lines d-flex navbar-toggler-icon">
            <i class="d-flex"></i>
            <i class="d-flex"></i>
            <i class="d-flex"></i>
            <i class="d-flex"></i>
            <i class="d-flex"></i>
        </div>
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
            <div class="d-flex">
                <a class="gradient-hover social-link" href="https://www.instagram.com/schleuse_eins/" target="_blank" title="@schleuse_eins on Instagram">
                    <i class="fa-brands fa-instagram"></i>
                    <span class="sr-only">@schleuse_eins on Instagram</span>
                </a>
            </div>
        </div>
    </div>
    @endif
</nav>
