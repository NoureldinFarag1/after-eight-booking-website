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
        .navbar-brand { font-weight: 600; }
        .card { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
        .btn-primary { background-color: #0d6efd; border-color: #0d6efd; }
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .event-card { transition: transform 0.2s; }
        .event-card:hover { transform: translateY(-2px); }
        .qr-code-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            display: inline-block;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    @php
    $pendingCount = \App\Models\EventRequest::where('status', 'pending')->count();
@endphp

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <!-- Brand -->
        @php($authUser = auth()->user())
        <a class="navbar-brand" href="{{ $authUser && $authUser->isAdmin() ? route('admin.dashboard') : route('events.index') }}">
            <i class="bi bi-calendar-event me-2"></i>After Eight Events
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Content -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('events.*') ? 'active' : '' }}"
                       href="{{ route('events.index') }}">
                        <i class="bi bi-calendar-event me-1"></i>Events
                    </a>
                </li>

                @auth
                    @php($authUser = auth()->user())
                    @if($authUser->isAdmin())
                        <!-- Admin Nav Items -->
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                               href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-speedometer2 me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.operators.*') ? 'active' : '' }}"
                               href="{{ route('admin.operators.index') }}">
                                <i class="bi bi-people me-1"></i>Operators
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('bookings.*') ? 'active' : '' }}"
                               href="{{ route('bookings.index') }}">
                                <i class="bi bi-ticket-perforated me-1"></i>All Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tickets.index') ? 'active' : '' }}"
                               href="{{ route('tickets.index') }}">
                                <i class="bi bi-qr-code me-1"></i>All Tickets
                            </a>
                        </li>
                    @elseif($authUser->isOperator())
                        <!-- Operator Nav Items -->
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tickets.scan') ? 'active' : '' }}"
                               href="{{ route('tickets.scan') }}">
                                <i class="bi bi-upc-scan me-1"></i>Scan Tickets
                            </a>
                        </li>
                    @else
                        <!-- User Nav Items -->
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('bookings.*') ? 'active' : '' }}"
                               href="{{ route('bookings.index') }}">
                                <i class="bi bi-ticket-perforated me-1"></i>My Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tickets.index') ? 'active' : '' }}"
                               href="{{ route('tickets.index') }}">
                                <i class="bi bi-qr-code me-1"></i>My Tickets
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>

            <!-- Right Side -->
            <ul class="navbar-nav ms-auto align-items-center">
                @auth
                    @php($authUser = auth()->user())
                    @if($authUser->isAdmin())
                        <!-- Notification Bell -->
                        <li class="nav-item me-3">
                            <a href="{{ route('admin.event_requests.index') }}" class="nav-link position-relative">
                                <i class="bi bi-bell fs-5"></i>
                                @if($pendingCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        {{ $pendingCount }}
                                    </span>
                                @endif
                            </a>
                        </li>
                    @endif

                    <!-- User Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="bi bi-person me-1"></i>Profile
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <!-- Guest Nav -->
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">
                            <i class="bi bi-person-plus me-1"></i>Register
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>


    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show m-0" role="alert">
            <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show m-0" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show m-0" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Main Content -->
    <main class="container py-4">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>After Eight Events</h5>
                    <p class="mb-0">Premium event booking platform</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small>&copy; {{ date('Y') }} After Eight Events. All rights reserved.</small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>
