@extends('layouts.app')

@section('title', 'My Bookings')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="bi bi-ticket-perforated me-2"></i>
                @if(auth()->user()->isAdmin())
                    All Bookings
                @else
                    My Bookings
                @endif
            </h1>
        </div>

        @php
            // $eventRequests now provided as a LengthAwarePaginator when user is not admin
        @endphp

        @if(($bookings->count() + $eventRequests->count()) > 0)
            <form method="get" class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Search</label>
                            <input type="text" class="form-control" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Reference or Event Title">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Any</option>
                                @foreach(($statusOptions ?? []) as $st)
                                    <option value="{{ $st }}" @selected(($filters['status'] ?? '')===$st)>{{ ucfirst($st) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Filter</button>
                            <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                        </div>
                    </div>
                </div>
            </form>
            @if(!$eventRequests->isEmpty())
                <div class="mb-4">
                    <h2 class="h5 mb-3 d-flex align-items-center"><i class="bi bi-bell me-2"></i>My Event Requests</h2>
                    <div class="row">
                        @foreach($eventRequests as $req)
                            <div class="col-lg-6 col-xl-4 mb-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-1">{{ $req->event?->title ?? 'Event #' . $req->event_id }}</h6>
                                                <small class="text-muted">Submitted {{ $req->created_at?->diffForHumans() }}</small>
                                            </div>
                                            <span class="badge status-badge @if($req->status==='approved') bg-success @elseif($req->status==='pending') bg-warning text-dark @elseif($req->status==='declined') bg-danger @else bg-secondary @endif">{{ ucfirst($req->status) }}</span>
                                        </div>
                                        <p class="mb-2 small text-muted">Primary: {{ $req->primary_name }} &lt;{{ $req->primary_email }}&gt;</p>
                                        <p class="mb-0 small">Attendees: <strong>{{ $req->attendee_count }}</strong></p>
                                    </div>
                                    <div class="card-footer bg-transparent d-flex justify-content-between">
                                        <a href="{{ route('event_requests.show', $req) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye me-1"></i>Details
                                        </a>
                                        @if($req->status==='pending')
                                            <span class="text-muted small">Awaiting review</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-center mt-2">
                        <x-pagination :paginator="$eventRequests->appends(request()->query())" />
                    </div>
                </div>
            @endif
            <div class="row">
                @foreach($bookings as $booking)
                    <div class="col-lg-6 col-xl-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="card-title mb-1">{{ $booking->booking_reference }}</h6>
                                        <small class="text-muted">{{ $booking->booking_date->format('M j, Y g:i A') }}</small>
                                    </div>
                                    <span class="badge status-badge
                                        @if($booking->status->value === 'confirmed') bg-success
                                        @elseif($booking->status->value === 'pending') bg-warning
                                        @elseif($booking->status->value === 'cancelled') bg-danger
                                        @elseif($booking->status->value === 'refunded') bg-info
                                        @else bg-secondary @endif">
                                        {{ ucfirst($booking->status->value) }}
                                    </span>
                                </div>

                                <div class="mb-3">
                                    <h5 class="mb-1">
                                        <a href="{{ route('events.show', $booking->event) }}"
                                           class="text-decoration-none">
                                            {{ $booking->event->title }}
                                        </a>
                                    </h5>

                                    @if(auth()->user()->isAdmin())
                                        <p class="mb-1">
                                            <i class="bi bi-person me-1"></i>
                                            <strong>Customer:</strong> {{ $booking->user->name }}
                                        </p>
                                        <p class="mb-1">
                                            <i class="bi bi-envelope me-1"></i>
                                            {{ $booking->user->email }}
                                        </p>
                                    @endif

                                    <p class="mb-1">
                                        <i class="bi bi-calendar me-1"></i>
                                        {{ $booking->event->event_date->format('l, F j, Y') }}
                                    </p>
                                    <p class="mb-1">
                                        <i class="bi bi-clock me-1"></i>
                                        {{ $booking->event->event_time->format('g:i A') }}
                                    </p>
                                    <p class="mb-1">
                                        <i class="bi bi-geo-alt me-1"></i>
                                        {{ $booking->event->location }}
                                    </p>
                                </div>

                                <div class="row text-center mb-3">
                                    <div class="col-4">
                                        <div class="text-muted small">Quantity</div>
                                        <div class="fw-bold">{{ $booking->quantity }}</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-muted small">Total</div>
                                        <div class="fw-bold text-primary">EGP {{ number_format($booking->total_amount, 2) }}</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-muted small">Tickets</div>
                                        <div class="fw-bold">{{ $booking->tickets->count() }}</div>
                                    </div>
                                </div>

                                @if($booking->notes)
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <strong>Notes:</strong> {{ $booking->notes }}
                                        </small>
                                    </div>
                                @endif
                            </div>

                            <div class="card-footer bg-transparent">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <a href="{{ route('bookings.show', $booking) }}"
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye me-1"></i>View Details
                                        </a>
                                    </div>

                                    <div class="btn-group btn-group-sm">
                                        @if($booking->canBeCancelled() && (!auth()->user()->isAdmin() || auth()->user()->id === $booking->user_id))
                                            <button type="button"
                                                    class="btn btn-outline-danger btn-sm"
                                                    onclick="confirmCancel('{{ $booking->id }}', '{{ $booking->booking_reference }}')">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        @endif

                                        @if(auth()->user()->isAdmin())
                                            <a href="{{ route('bookings.edit', $booking) }}"
                                               class="btn btn-outline-secondary btn-sm">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                <x-pagination :paginator="$bookings->appends(request()->query())" />
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-ticket-perforated display-1 text-muted"></i>
                <h3 class="mt-3 text-muted">No Bookings or Requests Found</h3>
                @if(auth()->user()->isAdmin())
                    <p class="text-muted">No bookings or event requests yet.</p>
                @else
                    <p class="text-muted mb-3">You haven't created any bookings or submitted event requests.</p>
                    <a href="{{ route('events.index') }}" class="btn btn-primary">
                        <i class="bi bi-calendar-event me-1"></i>Browse Events
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Cancel Confirmation Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel booking <strong id="booking-reference"></strong>?</p>
                <p class="text-danger">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    This action cannot be undone. All associated tickets will be cancelled.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Booking</button>
                <form id="cancelForm" method="POST" class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger">Cancel Booking</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmCancel(bookingId, bookingReference) {
        document.getElementById('booking-reference').textContent = bookingReference;
        document.getElementById('cancelForm').action = '/bookings/' + bookingId + '/cancel';
        new bootstrap.Modal(document.getElementById('cancelModal')).show();
    }
</script>
@endsection
