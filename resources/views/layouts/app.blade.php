@php
    use App\Models\EventRequest;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="dark">

    <title>{{ config('app.name', 'After Eight Booking') }} - @yield('title', 'Event Booking System')</title>

    <!-- Favicon & app icons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <!-- Optional: High-res and Apple touch icons if you add them later -->
    {{--
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('icons/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    --}}

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons removed after migrating to Lucide --}}
    <!-- QR Code Library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
{{-- Allow child views to override body class (e.g. auth screens) --}}
<body class="bg-animated-red-black @yield('body_class','')">
@php
    $pendingCount = 0;
    $authUser = auth()->user();
    if($authUser && method_exists($authUser,'isAdmin') && $authUser->isAdmin()) {
        $pendingCount = EventRequest::where('status','pending')->count();
    }
@endphp



<div class="d-flex">
    <!-- Sidebar (fixed for md+ screens) -->
    @php $showDesktopSidebar = $authUser && method_exists($authUser,'isStaff') && $authUser->isStaff(); @endphp
    @if($showDesktopSidebar)
    <aside class="sidebar sidebar-fixed border-end d-none d-md-flex flex-column">
        <div class="sidebar-brand">
            <a href="{{ $authUser && $authUser->isAdmin() ? route('admin.dashboard') : route('events.index') }}" class="brand-logo-link" aria-label="After Eight Home">
                <img src="{{ asset('images/Aftereight-logo.png') }}" alt="After Eight logo" class="brand-logo-full" />
            </a>
        </div>

        @include('partials.sidebar-menu', ['authUser' => $authUser, 'pendingCount' => $pendingCount])

    </aside>
    @endif

    <!-- Main Area -->
    <div class="content-wrapper {{ $showDesktopSidebar ? 'content-with-sidebar' : '' }} flex-grow-1 d-flex flex-column" style="min-height:100vh;width:100%;">
        <header class="app-header">
            <div class="container-fluid d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center gap-2">
                    <!-- Mobile: Hamburger to open offcanvas sidebar (staff only) -->
                    @if($showDesktopSidebar)
                    <button class="btn btn-icon d-inline-flex d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar" aria-label="Open navigation">
                        <i data-lucide="menu"></i>
                    </button>
                    @endif

                    <!-- Desktop: Toggle collapse/expand sidebar -->
                    @if($showDesktopSidebar)
                        <button id="sidebarToggleBtn" class="btn btn-icon d-none d-md-inline-flex" type="button" aria-label="Toggle sidebar">
                            <i id="sidebarToggleIcon" data-lucide="panel-left"></i>
                        </button>
                    @endif

                    @if($showDesktopSidebar)
                        <h1 class="h5 mb-0">@yield('title','Event Booking System')</h1>
                    @endif
                </div>
                @if($showDesktopSidebar)
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
                                @include('partials.user-menu-items', ['authUser' => $authUser])
                            </ul>
                        </div>
                    </div>
                    @endauth
                @endif
            </div>
        </header>

        @php
            // Hide the pill bar for staff (when desktop sidebar exists) and on auth pages (login/register/etc.)
            $isAuthBody = trim($__env->yieldContent('body_class','')) === 'auth-body';
        @endphp
        @if(!$showDesktopSidebar && !$isAuthBody)
            <div class="home-container">
                @include('partials.pill-nav')
            </div>
        @endif

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

<!-- Mobile Offcanvas Sidebar -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel" aria-label="Mobile Sidebar Navigation">
    <div class="offcanvas-header mobile-sidebar-header d-flex justify-content-between align-items-center px-3 py-3">
        <div class="d-flex align-items-center gap-2">
            <img src="{{ asset('images/Aftereight-logo.png') }}" alt="After Eight logo" style="height:42px; width:auto;" />
            <h5 class="offcanvas-title mb-0" id="mobileSidebarLabel">Menu</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <aside class="sidebar d-flex flex-column h-100" role="navigation" aria-label="Mobile Sidebar">
            @include('partials.sidebar-menu', ['authUser' => $authUser, 'pendingCount' => $pendingCount])
        </aside>
    </div>
    <div class="offcanvas-footer p-3 small text-muted">
        <span>&copy; {{ date('Y') }} After Eight Events</span>
    </div>
</div>

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
