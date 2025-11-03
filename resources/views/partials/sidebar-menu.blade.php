@php
    // Ensure these are set when included
    $authUser = $authUser ?? auth()->user();
    $pendingCount = $pendingCount ?? 0;
    if ($authUser && method_exists($authUser,'isAdmin') && $authUser->isAdmin()) {
        // If not passed, compute pending count for admin
        $pendingCount = $pendingCount ?: \App\Models\EventRequest::where('status','pending')->count();
    }
@endphp

<nav class="nav flex-column p-2" role="navigation" aria-label="Sidebar">
    @auth
        @if($authUser->isAdmin())
            <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                <i data-lucide="gauge"></i>
                <span class="label-text">Dashboard</span>
            </a>
            <div class="nav-divider"></div>
            <a class="nav-link d-flex align-items-center {{ request()->routeIs('events.*') ? 'active' : '' }}" href="{{ route('events.index') }}" @if(request()->routeIs('events.*')) aria-current="page" @endif>
                <i data-lucide="calendar"></i>
                <span class="label-text">Events</span>
            </a>
            <a class="nav-link d-flex align-items-center {{ request()->routeIs('bookings.*') ? 'active' : '' }}" href="{{ route('bookings.index') }}" @if(request()->routeIs('bookings.*')) aria-current="page" @endif>
                <i data-lucide="ticket"></i>
                <span class="label-text">Bookings</span>
            </a>
            <a class="nav-link d-flex align-items-center {{ request()->routeIs('tickets.index') ? 'active' : '' }}" href="{{ route('tickets.index') }}" @if(request()->routeIs('tickets.index')) aria-current="page" @endif>
                <i data-lucide="qr-code"></i>
                <span class="label-text">Tickets</span>
            </a>
            <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.artists.*') ? 'active' : '' }}" href="{{ route('admin.artists.index') }}" @if(request()->routeIs('admin.artists.*')) aria-current="page" @endif>
                <i data-lucide="music-3"></i>
                <span class="label-text">Artists</span>
            </a>
            <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.event_requests.*') ? 'active' : '' }}" href="{{ route('admin.event_requests.index') }}" @if(request()->routeIs('admin.event_requests.*')) aria-current="page" @endif>
                <i data-lucide="mails"></i>
                <span class="label-text">Requests</span>
                @if($pendingCount > 0)
                    <span class="badge rounded-pill bg-danger ms-auto">{{ $pendingCount }}</span>
                @endif
            </a>
            <div class="nav-divider"></div>
            <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}" @if(request()->routeIs('admin.users.*')) aria-current="page" @endif>
                <i data-lucide="users"></i>
                <span class="label-text">Users</span>
            </a>
            @php
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
                        <i data-lucide="user-cog"></i>
                        <span class="label-text">Staff</span>
                    </span>
                    <span class="submenu-chevron"><i data-lucide="chevron-down"></i></span>
                </a>
                <div class="submenu" data-submenu-content="staff">
                    <a class="nav-link submenu-link d-flex align-items-center {{ $isAdminsActive ? 'active' : '' }}" href="{{ route('admin.admins.index') }}" @if($isAdminsActive) aria-current="page" @endif>
                        <i data-lucide="shield"></i>
                        <span class="label-text">Admins</span>
                    </a>
                    <a class="nav-link submenu-link d-flex align-items-center {{ $isOperatorsActive ? 'active' : '' }}" href="{{ route('admin.staff.index', ['role'=>'operator']) }}" title="View & manage operators" @if($isOperatorsActive) aria-current="page" @endif>
                        <i data-lucide="user-search"></i>
                        <span class="label-text">Operators</span>
                    </a>
                    <a class="nav-link submenu-link d-flex align-items-center {{ $isApprovalsActive ? 'active' : '' }}" href="{{ route('admin.staff.index', ['role'=>'approval_officer']) }}" title="View & manage approval officers" @if($isApprovalsActive) aria-current="page" @endif>
                        <i data-lucide="signature"></i>
                        <span class="label-text">Approval Officers</span>
                    </a>
                    <a class="nav-link submenu-link d-flex align-items-center {{ $isFinanceActive ? 'active' : '' }}" href="{{ route('admin.staff.index', ['role'=>'finance_officer']) }}" title="View & manage finance officers" @if($isFinanceActive) aria-current="page" @endif>
                        <i data-lucide="wallet-minimal"></i>
                        <span class="label-text">Finance Officers</span>
                    </a>
                </div>
            </div>
        @elseif($authUser->isOperator())
            <a class="nav-link d-flex align-items-center {{ request()->routeIs('tickets.scan') ? 'active' : '' }}" href="{{ route('tickets.scan') }}" @if(request()->routeIs('tickets.scan')) aria-current="page" @endif>
                <i data-lucide="scan"></i>
                <span class="label-text">Scan Tickets</span>
            </a>
        @elseif($authUser->role === \App\Enums\Role::APPROVAL_OFFICER)
            <a class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('approval.*') ? 'active' : '' }}" href="{{ route('approval.index') }}" @if(request()->routeIs('approval.*')) aria-current="page" @endif>
                <span class="d-flex align-items-center">
                    <i data-lucide="mails"></i>
                    <span class="label-text">Requests</span>
                </span>
                @if(($sharedPendingApprovals ?? 0) > 0)
                    <span class="badge bg-warning text-dark ms-2 {{ ($sharedPendingApprovals ?? 0) > 25 ? 'badge-pulse' : '' }}">{{ $sharedPendingApprovals }}</span>
                @endif
            </a>
        @elseif($authUser->isFinanceOfficer())
            {{-- Finance user (no specific links yet) --}}
        @else
            <a class="nav-link d-flex align-items-center {{ request()->routeIs('events.index') ? 'active' : '' }}" href="{{ route('events.index') }}" title="Browse upcoming events" @if(request()->routeIs('events.index')) aria-current="page" @endif>
                <i data-lucide="calendar"></i>
                <span class="label-text">Upcoming Events</span>
            </a>
            <div class="nav-divider"></div>
            <a class="nav-link d-flex align-items-center {{ request()->routeIs('tickets.index') ? 'active' : '' }}" href="{{ route('tickets.index') }}" title="Your tickets & QR codes" @if(request()->routeIs('tickets.index')) aria-current="page" @endif>
                <i data-lucide="qr-code"></i>
                <span class="label-text">Tickets</span>
            </a>
            <a class="nav-link d-flex align-items-center {{ request()->routeIs('bookings.*') ? 'active' : '' }}" href="{{ route('bookings.index') }}" title="Manage your bookings" @if(request()->routeIs('bookings.*')) aria-current="page" @endif>
                <i data-lucide="ticket"></i>
                <span class="label-text">Bookings</span>
            </a>
            <a class="nav-link d-flex align-items-center {{ request()->routeIs('event_requests.index') ? 'active' : '' }}" href="{{ route('event_requests.index') }}" title="Access requests status" @if(request()->routeIs('event_requests.index')) aria-current="page" @endif>
                <i data-lucide="mails"></i>
                <span class="label-text">Requests</span>
            </a>
        @endif
    @else
        <div class="px-2 pt-2 guest-actions">
            <a class="btn btn-primary w-100 mb-2 d-inline-flex align-items-center" href="{{ route('login') }}">
                <i data-lucide="log-in" class="me-1"></i><span class="label-text">Login</span>
            </a>
            <a class="btn btn-outline-primary w-100 d-inline-flex align-items-center" href="{{ route('register') }}">
                <i data-lucide="user-plus" class="me-1"></i><span class="label-text">Register</span>
            </a>
        </div>
    @endauth
</nav>

<!-- Bottom actions -->
<div class="mt-auto px-2 pb-3">
    @auth
        @if(!$authUser->isStaff())
            <a class="nav-link d-flex align-items-center mb-2 {{ request()->routeIs('user.profile.*') ? 'active' : '' }}" href="{{ route('user.profile.index') }}">
                <i data-lucide="settings"></i>
                <span class="label-text">Preferences</span>
            </a>
        @endif
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-danger w-100 logout-btn">
                <i data-lucide="log-out" class="me-1"></i><span class="label-text">Logout</span>
            </button>
        </form>
    @endauth
</div>
