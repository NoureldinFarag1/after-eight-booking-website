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
    <style>
        /* Staff table layout stability */
        .staff-table td, .staff-table th { vertical-align: middle; }
        .staff-table .text-truncate { max-width: 180px; }
        @media (max-width: 1200px) {
            .staff-table td:nth-child(3),
            .staff-table th:nth-child(3) { display:none; }
        }
    </style>
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

            @if(!$authUser || !$authUser->role || $authUser->role !== \App\Enums\Role::APPROVAL_OFFICER)
                <a class="nav-link d-flex align-items-center {{ request()->routeIs('events.*') ? 'active' : '' }}" href="{{ route('events.index') }}">
                    <i class="bi bi-calendar-event me-2"></i>
                    <span class="label-text">Events</span>
                </a>
            @endif

            @auth
                @if($authUser->isAdmin())
                    <div class="mt-2 small text-uppercase text-muted px-2 section-label">Admin</div>
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2 me-2"></i>
                        <span class="label-text">Dashboard</span>
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
                                <i class="bi bi-people me-2"></i>
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

                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('bookings.*') ? 'active' : '' }}" href="{{ route('bookings.index') }}">
                        <i class="bi bi-ticket-perforated me-2"></i>
                        <span class="label-text">Bookings</span>
                    </a>
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('tickets.index') ? 'active' : '' }}" href="{{ route('tickets.index') }}">
                        <i class="bi bi-qr-code me-2"></i>
                        <span class="label-text">Tickets</span>
                    </a>

                    {{-- fixed Insights for finance officers in admin branch --}}
                    @if(auth()->check() && auth()->user()->isFinanceOfficer())
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('finance.insights') ? 'active' : '' }}" href="{{ route('finance.insights') }}">
                            <i class="bi bi-bar-chart-line me-2"></i>
                            <span class="label-text">Insights</span>
                        </a>
                    @endif

                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.event_requests.*') ? 'active' : '' }}" href="{{ route('admin.event_requests.index') }}">
                        <i class="bi bi-bell me-2 position-relative"></i>
                        <span class="label-text">Requests</span>
                        @if($pendingCount > 0)
                            <span class="badge rounded-pill bg-danger ms-auto">{{ $pendingCount }}</span>
                        @endif
                    </a>
                @elseif($authUser->isOperator())
                    <div class="mt-2 small text-uppercase text-muted px-2 section-label">Operator</div>
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('tickets.scan') ? 'active' : '' }}" href="{{ route('tickets.scan') }}">
                        <i class="bi bi-upc-scan me-2"></i>
                        <span class="label-text">Scan Tickets</span>
                    </a>
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('tickets.index') ? 'active' : '' }}" href="{{ route('tickets.index') }}">
                        <i class="bi bi-qr-code me-2"></i>
                        <span class="label-text">All Tickets</span>
                    </a>
                @elseif($authUser->role === \App\Enums\Role::APPROVAL_OFFICER)
                    <div class="mt-2 small text-uppercase text-muted px-2 section-label">Approvals</div>
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
                        <div class="mt-2 small text-uppercase text-muted px-2 section-label">Finance</div>
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('finance.insights') ? 'active' : '' }}" href="{{ route('finance.insights') }}">
                            <i class="bi bi-bar-chart-line me-2"></i>
                            <span class="label-text">Insights</span>
                        </a>
                @else
                    <div class="mt-2 small text-uppercase text-muted px-2 section-label">Account</div>
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('bookings.*') ? 'active' : '' }}" href="{{ route('bookings.index') }}">
                        <i class="bi bi-ticket-perforated me-2"></i>
                        <span class="label-text">My Bookings</span>
                    </a>
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('tickets.index') ? 'active' : '' }}" href="{{ route('tickets.index') }}">
                        <i class="bi bi-qr-code me-2"></i>
                        <span class="label-text">My Tickets</span>
                    </a>

                    {{-- fixed Insights for finance officers in account branch --}}
                    @if(auth()->check() && auth()->user()->isFinanceOfficer())
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('finance.insights') ? 'active' : '' }}" href="{{ route('finance.insights') }}">
                            <i class="bi bi-bar-chart-line me-2"></i>
                            <span class="label-text">Insights</span>
                        </a>
                    @endif

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
    <div class="content-wrapper content-with-sidebar flex-grow-1 d-flex flex-column" style="min-height: 100vh;">
        <!-- Top bar -->
        <header class="app-header">
            <div class="container-fluid d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center gap-2">
                    <!-- Desktop sidebar collapse toggle -->
                    <button class="btn-icon d-none d-md-inline-flex" id="sidebarToggleBtn" type="button" aria-label="Toggle sidebar">
                        <i id="sidebarToggleIcon" class="bi bi-chevron-left"></i>
                    </button>
                    <!-- Mobile sidebar toggle -->
                    <button class="btn-icon d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar" aria-label="Open menu">
                        <i class="bi bi-list"></i>
                    </button>
                    <h1 class="h5 mb-0">@yield('title', 'Event Booking System')</h1>
                </div>
                @auth
                    <div class="d-flex align-items-center gap-3">
                        <div class="small d-flex align-items-center">
                            <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
                        </div>
                    </div>
                @endauth
            </div>
        </header>

        <!-- Flash Messages (handled by Notyf toasts) -->
        <script>
            window.__FLASH__ = {
                @if(session('success')) success: @json(session('success')), @endif
                @if(session('error')) error: @json(session('error')), @endif
                @if(session('warning')) warning: @json(session('warning')), @endif
                @if(session('info')) info: @json(session('info')), @endif
            };
        </script>

        <!-- Main Content -->
        <main class="container-fluid py-4">
            @yield('content')
        </main>

        <!-- Footer -->
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
    </div>
</div>

<!-- Offcanvas Sidebar (for small screens) -->
<div class="offcanvas offcanvas-start bg-black text-light" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
    <div class="offcanvas-header border-bottom p-0 position-relative mobile-sidebar-header">
        <div class="mobile-sidebar-brand w-100">
            <img src="{{ asset('images/Aftereight-logo.png') }}" alt="After Eight" class="mobile-brand-img">
        </div>
        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0 d-flex flex-column">
        <nav class="nav flex-column p-2">
            @if(!$authUser || !$authUser->role || $authUser->role !== \App\Enums\Role::APPROVAL_OFFICER)
                <a class="nav-link d-flex align-items-center {{ request()->routeIs('events.*') ? 'active' : '' }}" href="{{ route('events.index') }}">
                    <i class="bi bi-calendar-event me-2"></i>
                    <span>Events</span>
                </a>
            @endif

            @auth
                @if($authUser->isAdmin())
                    <div class="mt-2 small text-uppercase text-muted px-2">Admin</div>
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2 me-2"></i>
                        <span>Dashboard</span>
                    </a>
                    <!-- Staff submenu (mobile) -->
                    @php
                        // Mobile version active state logic (updated from admin.operators.* to admin.staff.*)
                        $mobileIsStaffSectionActive = request()->routeIs('admin.staff.*') || request()->routeIs('admin.admins.*');
                        $mobileRoleFilter = request('role');
                        $mobileOpsActive = request()->routeIs('admin.staff.*') && $mobileRoleFilter === 'operator';
                        $mobileApprovalsActive = request()->routeIs('admin.staff.*') && $mobileRoleFilter === 'approval_officer';
                        $mobileFinanceActive = request()->routeIs('admin.staff.*') && $mobileRoleFilter === 'finance_officer';
                        $mobileAdminsActive = request()->routeIs('admin.admins.*');
                    @endphp
                    <div class="nav-item has-submenu {{ $mobileIsStaffSectionActive ? 'submenu-open' : '' }}">
                        <a class="nav-link d-flex align-items-center justify-content-between submenu-toggle" href="#" data-submenu="mobile-staff" aria-expanded="{{ $mobileIsStaffSectionActive ? 'true' : 'false' }}">
                            <span class="d-flex align-items-center">
                                <i class="bi bi-people me-2"></i>
                                <span>Staff</span>
                            </span>
                            <i class="bi bi-chevron-down submenu-chevron"></i>
                        </a>
                        <div class="submenu" data-submenu-content="mobile-staff">
                            <a class="nav-link submenu-link d-flex align-items-center {{ $mobileAdminsActive ? 'active' : '' }}" href="{{ route('admin.admins.index') }}" @if($mobileAdminsActive) aria-current="page" @endif>
                                <i class="bi bi-shield-lock me-2"></i>
                                <span>Admins</span>
                            </a>
                            <a class="nav-link submenu-link d-flex align-items-center {{ $mobileOpsActive ? 'active' : '' }}" href="{{ route('admin.staff.index', ['role'=>'operator']) }}" title="View & manage operators" @if($mobileOpsActive) aria-current="page" @endif>
                                <i class="bi bi-person-gear me-2"></i>
                                <span>Operators</span>
                            </a>
                            <a class="nav-link submenu-link d-flex align-items-center {{ $mobileApprovalsActive ? 'active' : '' }}" href="{{ route('admin.staff.index', ['role'=>'approval_officer']) }}" title="View & manage approval officers" @if($mobileApprovalsActive) aria-current="page" @endif>
                                <i class="bi bi-check2-circle me-2"></i>
                                <span>Approval Officers</span>
                            </a>
                            <a class="nav-link submenu-link d-flex align-items-center {{ $mobileFinanceActive ? 'active' : '' }}" href="{{ route('admin.staff.index', ['role'=>'finance_officer']) }}" title="View & manage finance officers" @if($mobileFinanceActive) aria-current="page" @endif>
                                <i class="bi bi-cash-coin me-2"></i>
                                <span>Finance Officers</span>
                            </a>
                        </div>
                    </div>
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('bookings.*') ? 'active' : '' }}" href="{{ route('bookings.index') }}">
                        <i class="bi bi-ticket-perforated me-2"></i>
                        <span>Bookings</span>
                    </a>
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('tickets.index') ? 'active' : '' }}" href="{{ route('tickets.index') }}">
                        <i class="bi bi-qr-code me-2"></i>
                        <span>Tickets</span>
                    </a>

                    {{-- fixed Insights for finance officers in mobile admin branch --}}
                    @if(auth()->check() && auth()->user()->isFinanceOfficer())
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('finance.insights') ? 'active' : '' }}" href="{{ route('finance.insights') }}">
                            <i class="bi bi-bar-chart-line me-2"></i>
                            <span class="label-text">Insights</span>
                        </a>
                    @endif

                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.event_requests.*') ? 'active' : '' }}" href="{{ route('admin.event_requests.index') }}">
                        <i class="bi bi-bell me-2 position-relative"></i>
                        <span>Requests</span>
                        @if($pendingCount > 0)
                            <span class="badge rounded-pill bg-danger ms-auto">{{ $pendingCount }}</span>
                        @endif
                    </a>
                @elseif($authUser->isOperator())
                    <div class="mt-2 small text-uppercase text-muted px-2">Operator</div>
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('tickets.scan') ? 'active' : '' }}" href="{{ route('tickets.scan') }}">
                        <i class="bi bi-upc-scan me-2"></i>
                        <span>Scan Tickets</span>
                    </a>
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('tickets.index') ? 'active' : '' }}" href="{{ route('tickets.index') }}">
                        <i class="bi bi-qr-code me-2"></i>
                        <span>All Tickets</span>
                    </a>
                @elseif($authUser->role === \App\Enums\Role::APPROVAL_OFFICER)
                    <div class="mt-2 small text-uppercase text-muted px-2">Approvals</div>
                    <a class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('approval.*') ? 'active' : '' }}" href="{{ route('approval.index') }}">
                        <span class="d-flex align-items-center">
                            <i class="bi bi-check-circle me-2"></i>
                            <span>Requests</span>
                        </span>
                        @if(($sharedPendingApprovals ?? 0) > 0)
                            <span class="badge bg-warning text-dark ms-2 {{ ($sharedPendingApprovals ?? 0) > 25 ? 'badge-pulse' : '' }}">{{ $sharedPendingApprovals }}</span>
                        @endif
                    </a>
                    @elseif($authUser->isFinanceOfficer())
                        <div class="mt-2 small text-uppercase text-muted px-2">Finance</div>
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('finance.insights') ? 'active' : '' }}" href="{{ route('finance.insights') }}">
                            <i class="bi bi-bar-chart-line me-2"></i>
                            <span>Insights</span>
                        </a>
                @else
                    <div class="mt-2 small text-uppercase text-muted px-2">Account</div>
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('bookings.*') ? 'active' : '' }}" href="{{ route('bookings.index') }}">
                        <i class="bi bi-ticket-perforated me-2"></i>
                        <span>My Bookings</span>
                    </a>
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('tickets.index') ? 'active' : '' }}" href="{{ route('tickets.index') }}">
                        <i class="bi bi-qr-code me-2"></i>
                        <span>My Tickets</span>
                    </a>

                    {{-- fixed Insights for finance officers in mobile account branch --}}
                    @if(auth()->check() && auth()->user()->isFinanceOfficer())
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('finance.insights') ? 'active' : '' }}" href="{{ route('finance.insights') }}">
                            <i class="bi bi-bar-chart-line me-2"></i>
                            <span class="label-text">Insights</span>
                        </a>
                    @endif

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

        <div class="mt-auto w-100">
            @auth
            <div class="px-2 pb-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100 logout-btn">
                        <i class="bi bi-box-arrow-right me-1"></i><span class="label-text">Logout</span>
                    </button>
                </form>
            </div>
            @endauth

        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')
<div id="navPageLoader" class="nav-page-loader d-none" aria-hidden="true">
    <div class="inner">
        <div class="spinner-border text-primary" role="status" aria-label="Loading"></div>
    </div>
</div>
</body>
</html>
