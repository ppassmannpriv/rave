<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">Navbar</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            @foreach($pages as $page)
                <li class="nav-item {{ $page->isActive() ? "active" : "" }}">
                    @if ($page->isActive())
                    <a class="nav-link" href="#">{{ $page->title }}<span class="sr-only">(current)</span></a>
                    @else
                    <a class="nav-link" href="{{ route($page->path) }}">{{ $page->title }}</a>
                    @endif
                </li>
            @endforeach
            <li class="nav-item">
                <a class="nav-link disabled" href="#">Shop</a>
            </li>
        </ul>
    </div>
</nav>
