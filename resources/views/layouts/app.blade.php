@php
    use App\Models\EventRequest;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'After Eight Booking') }} - @yield('title', 'Event Booking System')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- QR Code Library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
{{-- Allow child views to override body class (e.g. auth screens) --}}
<body class="@yield('body_class','bg-light')">
@php
    $pendingCount = 0;
    $authUser = auth()->user();
    if($authUser && method_exists($authUser,'isAdmin') && $authUser->isAdmin()) {
        $pendingCount = EventRequest::where('status','pending')->count();
    }
@endphp

<div class="d-flex">
    <!-- Sidebar (fixed for md+ screens) -->
    <aside class="sidebar sidebar-fixed border-end d-none d-md-flex flex-column">
        <div class="sidebar-brand">
            <a href="{{ $authUser && $authUser->isAdmin() ? route('admin.dashboard') : route('events.index') }}" class="brand-logo-link" aria-label="After Eight Home">
                <img src="{{ asset('images/Aftereight-logo.png') }}" alt="After Eight" class="brand-logo-full">
            </a>
        </div>

        <nav class="nav flex-column p-2">
            @auth
                @if($authUser->isAdmin())
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2 me-2"></i>
                        <span class="label-text">Dashboard</span>
                    </a>
                    <div class="nav-divider"></div>
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('events.*') ? 'active' : '' }}" href="{{ route('events.index') }}">
                        <i class="bi bi-calendar-event me-2"></i>
                        <span class="label-text">Events</span>
                    </a>
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('bookings.*') ? 'active' : '' }}" href="{{ route('bookings.index') }}">
                        <i class="bi bi-ticket-perforated me-2"></i>
                        <span class="label-text">Bookings</span>
                    </a>
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('tickets.index') ? 'active' : '' }}" href="{{ route('tickets.index') }}">
                        <i class="bi bi-qr-code me-2"></i>
                        <span class="label-text">Tickets</span>
                    </a>
                     <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.event_requests.*') ? 'active' : '' }}" href="{{ route('admin.event_requests.index') }}">
                        <i class="bi bi-bell me-2 position-relative"></i>
                        <span class="label-text">Requests</span>
                        @if($pendingCount > 0)
                            <span class="badge rounded-pill bg-danger ms-auto">{{ $pendingCount }}</span>
                        @endif
                    </a>
                    <div class="nav-divider"></div>
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                        <i class="bi bi-people me-2"></i>
                        <span class="label-text">Users</span>
                    </a>
                    <!-- Staff submenu -->
                    @php
                        // Updated route matching to use admin.staff.* (was legacy admin.operators.*)
                        $isStaffSectionActive = request()->routeIs('admin.staff.*') || request()->routeIs('admin.admins.*');
                        $roleFilter = request('role');
                        $isOperatorsActive = request()->routeIs('admin.staff.*') && $roleFilter === 'operator';
                        $isApprovalsActive = request()->routeIs('admin.staff.*') && $roleFilter === 'approval_officer';
                        $isFinanceActive = request()->routeIs('admin.staff.*') && $roleFilter === 'finance_officer';
                        $isAdminsActive = request()->routeIs('admin.admins.*');
                    @endphp
                    <div class="nav-item has-submenu {{ $isStaffSectionActive ? 'submenu-open' : '' }}">
                        <a class="nav-link d-flex align-items-center justify-content-between submenu-toggle" href="#" data-submenu="staff" aria-expanded="{{ $isStaffSectionActive ? 'true' : 'false' }}">
                            <span class="d-flex align-items-center">
                                <i class="bi bi-person-square me-2"></i>
                                <span class="label-text">Staff</span>
                            </span>
                            <i class="bi bi-chevron-down submenu-chevron"></i>
                        </a>
                        <div class="submenu" data-submenu-content="staff">
                            <a class="nav-link submenu-link d-flex align-items-center {{ $isAdminsActive ? 'active' : '' }}" href="{{ route('admin.admins.index') }}" @if($isAdminsActive) aria-current="page" @endif>
                                <i class="bi bi-shield-lock me-2"></i>
                                <span class="label-text">Admins</span>
                            </a>
                            <a class="nav-link submenu-link d-flex align-items-center {{ $isOperatorsActive ? 'active' : '' }}" href="{{ route('admin.staff.index', ['role'=>'operator']) }}" title="View & manage operators" @if($isOperatorsActive) aria-current="page" @endif>
                                <i class="bi bi-person-gear me-2"></i>
                                <span class="label-text">Operators</span>
                            </a>
                            <a class="nav-link submenu-link d-flex align-items-center {{ $isApprovalsActive ? 'active' : '' }}" href="{{ route('admin.staff.index', ['role'=>'approval_officer']) }}" title="View & manage approval officers" @if($isApprovalsActive) aria-current="page" @endif>
                                <i class="bi bi-check2-circle me-2"></i>
                                <span class="label-text">Approval Officers</span>
                            </a>
                            <a class="nav-link submenu-link d-flex align-items-center {{ $isFinanceActive ? 'active' : '' }}" href="{{ route('admin.staff.index', ['role'=>'finance_officer']) }}" title="View & manage finance officers" @if($isFinanceActive) aria-current="page" @endif>
                                <i class="bi bi-cash-coin me-2"></i>
                                <span class="label-text">Finance Officers</span>
                            </a>
                        </div>



                    </div>
                @elseif($authUser->isOperator())
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('tickets.scan') ? 'active' : '' }}" href="{{ route('tickets.scan') }}">
                        <i class="bi bi-upc-scan me-2"></i>
                        <span class="label-text">Scan Tickets</span>
                    </a>
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('tickets.index') ? 'active' : '' }}" href="{{ route('tickets.index') }}">
                        <i class="bi bi-qr-code me-2"></i>
                        <span class="label-text">All Tickets</span>
                    </a>
                @elseif($authUser->role === \App\Enums\Role::APPROVAL_OFFICER)
                    <a class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('approval.*') ? 'active' : '' }}" href="{{ route('approval.index') }}">
                        <span class="d-flex align-items-center">
                            <i class="bi bi-check-circle me-2"></i>
                            <span class="label-text">Requests</span>
                        </span>
                        @if(($sharedPendingApprovals ?? 0) > 0)
                            <span class="badge bg-warning text-dark ms-2 {{ ($sharedPendingApprovals ?? 0) > 25 ? 'badge-pulse' : '' }}">{{ $sharedPendingApprovals }}</span>
                        @endif
                    </a>
                    @elseif($authUser->isFinanceOfficer())
                        {{-- Finance user (no specific links yet) --}}
                @else
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('events.index') ? 'active' : '' }}" href="{{ route('events.index') }}" title="Browse upcoming events">
                        <i class="bi bi-calendar-event me-2"></i>
                        <span class="label-text">Upcoming Events</span>
                    </a>
                    <div class="nav-divider"></div>
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('tickets.index') ? 'active' : '' }}" href="{{ route('tickets.index') }}" title="Your tickets & QR codes">
                        <i class="bi bi-qr-code me-2"></i>
                        <span class="label-text">Tickets</span>
                    </a>
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('bookings.*') ? 'active' : '' }}" href="{{ route('bookings.index') }}" title="Manage your bookings">
                        <i class="bi bi-ticket-perforated me-2"></i>
                        <span class="label-text">Bookings</span>
                    </a>
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('event_requests.index') ? 'active' : '' }}" href="{{ route('event_requests.index') }}" title="Access requests status">
                        <i class="bi bi-bell me-2"></i>
                        <span class="label-text">Requests</span>
                    </a>
                @endif


            @else
                <div class="px-2 pt-2 guest-actions">
                    <a class="btn btn-primary w-100 mb-2 d-inline-flex align-items-center" href="{{ route('login') }}">
                        <i class="bi bi-box-arrow-in-right me-1"></i><span class="label-text">Login</span>
                    </a>
                    <a class="btn btn-outline-primary w-100 d-inline-flex align-items-center" href="{{ route('register') }}">
                        <i class="bi bi-person-plus me-1"></i><span class="label-text">Register</span>
                    </a>
                </div>
            @endauth
        </nav>

        <!-- Bottom actions -->
        <div class="mt-auto px-2 pb-3">
            @auth
            @if(!$authUser->isStaff())
            <a class="nav-link d-flex align-items-center mb-2 {{ request()->routeIs('user.profile.*') ? 'active' : '' }}" href="{{ route('user.profile.index') }}">
                <i class="bi bi-person-circle me-2"></i>
                <span class="label-text">Preferences</span>
            </a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100 logout-btn">
                    <i class="bi bi-box-arrow-right me-1"></i><span class="label-text">Logout</span>
                </button>
            </form>
            @endauth
        </div>


    </aside>

    <!-- Main Area -->
    <div class="content-wrapper content-with-sidebar flex-grow-1 d-flex flex-column" style="min-height:100vh;">
        <header class="app-header">
            <div class="container-fluid d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h5 mb-0">@yield('title','Event Booking System')</h1>
                </div>
                @auth
                <div class="d-flex align-items-center gap-3">
                    <div class="dropdown">
                        <button class="btn btn-link text-light d-flex align-items-center text-decoration-none dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border:none;background:none;">
                            <div class="user-initials-avatar me-2">
                                {{ strtoupper(substr($authUser->name,0,1)) }}{{ strtoupper(substr(explode(' ', $authUser->name)[1] ?? '',0,1)) }}
                            </div>
                            <span class="fw-semibold">{{ explode(' ', $authUser->name)[0] }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="mb-0">@csrf
                                    <button type="submit" class="dropdown-item d-flex align-items-center text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                @endauth
            </div>
        </header>

        <script>
            window.__FLASH__ = {
                @if(session('success')) success: @json(session('success')), @endif
                @if(session('error')) error: @json(session('error')), @endif
                @if(session('warning')) warning: @json(session('warning')), @endif
                @if(session('info')) info: @json(session('info')), @endif
            };
        </script>

        <main class="container-fluid py-4">
            @yield('content')
        </main>

        <footer class="bg-dark text-light py-4 mt-auto">
            <div class="container-fluid d-flex justify-content-between">
                <div>
                    <h6 class="mb-1">After Eight Events</h6>
                    <small class="mb-0 d-block">Premium event booking platform</small>
                </div>
                <div class="text-end">
                    <small>&copy; {{ date('Y') }} After Eight Events. All rights reserved.</small>
                </div>
            </div>
        </footer>
    </div><!-- /content-wrapper -->
</div><!-- /d-flex -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/csp@3.x.x/dist/cdn.min.js"></script>

@stack('scripts')
<div id="navPageLoader" class="nav-page-loader d-none" aria-hidden="true">
    <div class="inner">
        <div class="spinner-border text-primary" role="status" aria-label="Loading"></div>
    </div>
</div>
</body>
</html>
