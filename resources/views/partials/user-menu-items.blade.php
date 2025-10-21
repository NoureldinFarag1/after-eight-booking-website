@php
    $authUser = $authUser ?? auth()->user();
@endphp
@if(!$authUser)
    <li>
        <a class="dropdown-item d-flex align-items-center" href="{{ route('login') }}">
            <i data-lucide="log-in" class="me-2"></i>Login
        </a>
    </li>
    <li>
        <a class="dropdown-item d-flex align-items-center" href="{{ route('register') }}">
            <i data-lucide="user-plus" class="me-2"></i>Register
        </a>
    </li>
@else
    @if(!$authUser->isStaff())
        <li>
            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('tickets.index') ? 'active' : '' }}" href="{{ route('tickets.index') }}">
                <i data-lucide="qr-code" class="me-2"></i>Tickets
            </a>
        </li>
        <li>
            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('bookings.*') ? 'active' : '' }}" href="{{ route('bookings.index') }}">
                <i data-lucide="ticket" class="me-2"></i>Bookings
            </a>
        </li>
        <li>
            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('event_requests.index') ? 'active' : '' }}" href="{{ route('event_requests.index') }}">
                <i data-lucide="bell" class="me-2"></i>Requests
            </a>
        </li>
        <br>
        <li>
            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('user.profile.*') ? 'active' : '' }}" href="{{ route('user.profile.index') }}">
                <i data-lucide="settings" class="me-2"></i>Preferences
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
    @else
        @if($authUser->isAdmin())
            <li>
                <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i data-lucide="gauge" class="me-2"></i>Admin Dashboard
                </a>
            </li>
        @elseif($authUser->isOperator())
            <li>
                <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('tickets.scan') ? 'active' : '' }}" href="{{ route('tickets.scan') }}">
                    <i data-lucide="scan" class="me-2"></i>Scan Tickets
                </a>
            </li>
        @elseif($authUser->isApprovalOfficer())
            <li>
                <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('approval.*') ? 'active' : '' }}" href="{{ route('approval.index') }}">
                    <i data-lucide="circle-check" class="me-2"></i>Requests
                </a>
            </li>
        @endif
        <li><hr class="dropdown-divider"></li>
    @endif
    <li>
        <form method="POST" action="{{ route('logout') }}" class="mb-0">@csrf
            <button type="submit" class="dropdown-item d-flex align-items-center text-danger"><i data-lucide="log-out" class="me-2"></i>Logout</button>
        </form>
    </li>
@endif
