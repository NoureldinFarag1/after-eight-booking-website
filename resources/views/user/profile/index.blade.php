@extends('layouts.app')

@section('title', 'My Profile & Settings')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="h3 mb-1">My Profile & Settings</h1>
        <p class="text-muted mb-0">Manage your personal information and view your activity</p>
    </div>
    <div class="d-flex gap-2">
        @if($user->hasCompletedProfile())
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-gear me-1"></i>Account Settings
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item disabled" href="#">
                            <i class="bi bi-person-fill me-2 text-muted"></i>
                            <div>
                                <div class="fw-semibold text-muted">Profile Information</div>
                                <div class="small text-muted">Secured for your protection</div>
                            </div>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('user.profile.password.edit') }}">
                            <i class="bi bi-key me-2"></i>Change Password
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="mailto:support@aftereightevents.com?subject=Profile Update Request&body=Hello,%0A%0AI would like to request an update to my profile information.%0A%0AUser Email: {{ $user->email }}%0AName: {{ $user->name }}%0A%0APlease describe the changes you need:%0A%0A">
                            <i class="bi bi-envelope me-2"></i>Request Profile Changes
                        </a>
                    </li>
                </ul>
            </div>
        @else
            <a href="{{ route('profile.complete') }}" class="btn btn-warning">
                <i class="bi bi-exclamation-triangle me-1"></i>Complete Profile
            </a>
        @endif
    </div>
</div>

<div class="row">
    <!-- Profile Information -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-person-circle me-2"></i>Personal Information
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                         style="width: 80px; height: 80px;">
                        <span class="text-white fw-bold fs-2">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                    </div>
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <div class="mb-2">
                        @if($user->hasCompletedProfile())
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>Profile Complete
                            </span>
                        @else
                            <span class="badge bg-warning">
                                <i class="bi bi-exclamation-triangle me-1"></i>Profile Incomplete
                            </span>
                        @endif
                        <span class="badge bg-primary ms-1">Active Member</span>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-12">
                        <div class="text-muted small">Email</div>
                        <div class="fw-semibold">{{ $user->email }}</div>
                    </div>
                    @if($user->phone)
                        <div class="col-12">
                            <div class="text-muted small">Phone</div>
                            <div class="fw-semibold">{{ $user->phone }}</div>
                        </div>
                    @endif
                    @if($user->birthday)
                        <div class="col-6">
                            <div class="text-muted small">Birthday</div>
                            <div class="fw-semibold">{{ $user->birthday->format('M j, Y') }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Age</div>
                            <div class="fw-semibold">{{ $user->getDisplayAge() }} years</div>
                        </div>
                    @elseif($user->age)
                        <div class="col-6">
                            <div class="text-muted small">Age</div>
                            <div class="fw-semibold">{{ $user->age }} years</div>
                        </div>
                    @endif
                    @if($user->gender)
                        <div class="col-{{ $user->birthday ? '12' : '6' }}">
                            <div class="text-muted small">Gender</div>
                            <div class="fw-semibold">{{ ucfirst($user->gender) }}</div>
                        </div>
                    @endif
                    <div class="col-12">
                        <div class="text-muted small">Member Since</div>
                        <div class="fw-semibold">{{ $membershipStats['member_since']->format('F j, Y') }}</div>
                    </div>
                </div>

                @if($user->hasCompletedProfile())
                    <div class="mt-3 p-2 bg-black rounded">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-shield-check text-success me-2 mt-1"></i>
                            <div class="small">
                                <div class="fw-semibold text-success">Profile Secured</div>
                                <div class="text-muted">
                                    Your personal information is protected for security reasons.
                                    <a href="mailto:support@aftereightevents.com?subject=Profile Update Request&body=Hello,%0A%0AI would like to request an update to my profile information.%0A%0AUser Email: {{ $user->email }}%0AName: {{ $user->name }}%0A%0APlease describe the changes you need:%0A%0A"
                                       class="text-decoration-none">Contact support</a> for any changes.
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Activity Details -->
    <div class="col-lg-8">
        <!-- Recent Bookings -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-ticket-perforated me-2"></i>Recent Bookings
                </h5>
                <a href="{{ route('bookings.index') }}" class="small text-decoration-none">View all</a>
            </div>
            <div class="card-body">
                @php $recentBookingsLimited = $recentBookings->take(5); @endphp
                @if($recentBookingsLimited->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Event</th>
                                    <th>Date</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentBookingsLimited as $booking)
                                    <tr>
                                        <td>
                                            <a href="{{ route('bookings.show', $booking) }}"
                                               class="text-decoration-none fw-semibold text-muted">
                                                {{ $booking->booking_reference }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="text-muted small">{{ $booking->event->title }}</div>
                                            <div class="text-muted small">
                                                {{ $booking->event->event_date->format('M j, Y') }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-muted small">{{ $booking->booking_date->format('M j, Y') }}</div>
                                            <div class="text-muted small">{{ $booking->booking_date->format('g:i A') }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-black">{{ $booking->quantity }}</span>
                                        </td>
                                        <td class="text-end fw-semibold text-muted">
                                            {{ number_format((float)$booking->total_amount, 2) }} EGP
                                        </td>
                                        <td class="text-center">
                                            <span class="badge
                                                @if($booking->status->value === 'confirmed') bg-success
                                                @elseif($booking->status->value === 'pending') bg-warning
                                                @elseif($booking->status->value === 'cancelled') bg-danger
                                                @else bg-secondary @endif">
                                                {{ ucfirst($booking->status->value) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-ticket text-muted mb-3" style="font-size: 2rem;"></i>
                        <p class="text-muted mb-2">No bookings yet</p>
                        <a href="{{ route('events.index') }}" class="btn btn-primary">
                            <i class="bi bi-calendar-event me-1"></i>Browse Events
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Tickets -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-qr-code me-2"></i>Recent Tickets
                </h5>
                <a href="{{ route('tickets.index') }}" class="small text-decoration-none">View all</a>
            </div>
            <div class="card-body">
                @php $recentTicketsLimited = $recentTickets->take(5); @endphp
                @if($recentTicketsLimited->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Ticket Code</th>
                                    <th>Event</th>
                                    <th>Booking</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTicketsLimited as $ticket)
                                    <tr>
                                        <td>
                                            <a href="{{ route('tickets.show', $ticket) }}"
                                               class="text-decoration-none fw-semibold font-monospace text-muted">
                                                {{ $ticket->qr_code }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="text-muted">{{ $ticket->event->title }}</div>
                                            <div class="text-muted small">
                                                {{ $ticket->event->event_date->format('M j, Y') }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($ticket->booking)
                                                <a href="{{ route('bookings.show', $ticket->booking) }}"
                                                   class="text-decoration-none text-muted">
                                                    {{ $ticket->booking->booking_reference }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge
                                                @if($ticket->status->value === 'active') bg-success
                                                @elseif($ticket->status->value === 'used') bg-info
                                                @elseif($ticket->status->value === 'cancelled') bg-danger
                                                @else bg-secondary @endif">
                                                {{ ucfirst($ticket->status->value) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-qr-code text-muted mb-3" style="font-size: 2rem;"></i>
                        <p class="text-muted mb-0">No tickets yet</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Invitations -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-envelope-heart me-2"></i>Recent Invitations
                </h5>
                <a href="{{ route('invitations.index') }}" class="small text-decoration-none">View all</a>
            </div>
            <div class="card-body">
                @php $recentInvitationsLimited = $recentInvitations->take(5); @endphp
                @if($recentInvitationsLimited->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Date Received</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentInvitationsLimited as $invitation)
                                    <tr>
                                        <td>
                                            @if($invitation->event)
                                                <div class="fw-semibold text-muted">{{ $invitation->event->title }}</div>
                                                <div class="text-muted small">
                                                    {{ $invitation->event->event_date->format('M j, Y') }}
                                                </div>
                                            @else
                                                <div class="fw-semibold text-muted">Event Deleted</div>
                                                <div class="text-muted small">No longer available</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="text-muted">{{ $invitation->created_at->format('M j, Y') }}</div>
                                            <div class="text-muted small">{{ $invitation->created_at->format('g:i A') }}</div>
                                        </td>
                                        <td class="text-center text-muted">
                                            <span class="badge
                                                @if($invitation->status === 'sent') bg-info
                                                @elseif($invitation->status === 'viewed') bg-warning
                                                @elseif($invitation->status === 'accepted') bg-success
                                                @else bg-secondary @endif">
                                                {{ ucfirst($invitation->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-envelope-heart text-muted mb-3" style="font-size: 2rem;"></i>
                        <p class="text-muted mb-0">No invitations received yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
