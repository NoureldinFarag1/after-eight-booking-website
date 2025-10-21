@php $authUser = auth()->user(); @endphp
<div class="home-nav home-nav--sticky mb-4">
    <div class="brand">
        <a href="{{ route('home') }}" class="d-inline-flex align-items-center text-decoration-none">
            <img src="{{ asset('images/Aftereight-logo.png') }}" alt="After Eight logo" />
        </a>
    </div>
    <div class="home-search">
        <i class="search-icon" data-lucide="search"></i>
        <form action="{{ route('events.index') }}" method="get" class="w-100">
            <input type="search" name="q" placeholder="Search for events..." class="form-control" />
        </form>
    </div>
    <div class="nav-links ms-auto">
        <a href="{{ route('events.index') }}">Events</a>
        <a href="mailto:support@aftereight.com">Contact &amp; Support</a>
        @if($authUser)
            <div class="dropdown">
                <button class="btn btn-link p-0 text-decoration-none dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-initials-avatar me-1">
                        {{ strtoupper(substr($authUser->name,0,1)) }}{{ strtoupper(substr(explode(' ', $authUser->name)[1] ?? '',0,1)) }}
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @include('partials.user-menu-items', ['authUser' => $authUser])
                </ul>
            </div>
        @else
            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-light">Login</a>
        @endif
    </div>
</div>
