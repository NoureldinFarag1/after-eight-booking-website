@extends('layouts.app')

@section('title', $event->title)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            @if($event->image_url)
                <img src="{{ Storage::url($event->image_url) }}"
                     class="card-img-top"
                     alt="{{ $event->title }}"
                     style="height: 400px; object-fit: cover;">
            @endif

            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h1 class="card-title h2">{{ $event->title }}</h1>
                        <span class="badge status-badge
                            @if($event->status->value === 'published') bg-success
                            @elseif($event->status->value === 'draft') bg-secondary
                            @elseif($event->status->value === 'cancelled') bg-danger
                            @else bg-warning @endif">
                            {{ ucfirst($event->status->value) }}
                        </span>
                    </div>

                    @auth
                        @if(auth()->user()->isAdmin())
                            <div class="btn-group">
                                <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil me-1"></i>Edit
                                </a>
                                <a href="{{ route('admin.events.ticket-types.index', $event) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-ticket-detailed me-1"></i>Manage Ticket Types
                                </a>
                                <button type="button" class="btn btn-outline-danger"
                                        onclick="confirmDelete('{{ $event->id }}')">
                                    <i class="bi bi-trash me-1"></i>Delete
                                </button>
                            </div>
                        @endif
                    @endauth
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-calendar text-primary me-2"></i>
                            <span>{{ $event->event_date->format('l, F j, Y') }}</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-clock text-primary me-2"></i>
                            <span>{{ $event->event_time->format('g:i A') }}</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-geo-alt text-primary me-2"></i>
                            <span>{{ $event->location }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-people text-primary me-2"></i>
                            <span>{{ $event->capacity }} total seats</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-ticket text-primary me-2"></i>
                            <span>{{ $event->getAvailableSeatsAttribute() }} seats available</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-currency-dollar text-primary me-2"></i>
                            <span>
                                @if($event->price > 0)
                                    ${{ number_format($event->price, 2) }} per ticket
                                @else
                                    Free event
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h4>Description</h4>
                    <p class="text-muted">{{ $event->description }}</p>
                </div>

                @if($event->terms_conditions)
                    <div class="mb-4">
                        <h5>Terms & Conditions</h5>
                        <p class="small text-muted">{{ $event->terms_conditions }}</p>
                    </div>
                @endif
            </div>
        </div>

        @auth
            @if(auth()->user()->isAdmin())
                <!-- Admin: Recent Bookings -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Bookings</h5>
                    </div>
                    <div class="card-body">
                        @if($event->bookings->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Reference</th>
                                            <th>Customer</th>
                                            <th>Quantity</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($event->bookings->take(10) as $booking)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('bookings.show', $booking) }}" class="text-decoration-none">
                                                        {{ $booking->booking_reference }}
                                                    </a>
                                                </td>
                                                <td>{{ $booking->user->name }}</td>
                                                <td>{{ $booking->quantity }}</td>
                                                <td>${{ number_format($booking->total_amount, 2) }}</td>
                                                <td>
                                                    <span class="badge
                                                        @if($booking->status->value === 'confirmed') bg-success
                                                        @elseif($booking->status->value === 'pending') bg-warning
                                                        @elseif($booking->status->value === 'cancelled') bg-danger
                                                        @else bg-secondary @endif">
                                                        {{ ucfirst($booking->status->value) }}
                                                    </span>
                                                </td>
                                                <td>{{ $booking->booking_date->format('M j, Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No bookings yet.</p>
                        @endif
                    </div>
                </div>

                <!-- Admin: Recent Requests -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Requests</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $recentRequests = \App\Models\EventRequest::where('event_id', $event->id)->latest()->take(10)->get();
                        @endphp

                        @if($recentRequests->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Requester</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentRequests as $r)
                                            <tr>
                                                <td>{{ $r->id }}</td>
                                                <td>{{ $r->user->name ?? 'N/A' }}</td>
                                                <td>
                                                    <span class="badge
                                                        @if($r->status === 'approved') bg-success
                                                        @elseif($r->status === 'pending') bg-warning
                                                        @elseif($r->status === 'declined') bg-danger
                                                        @else bg-secondary @endif">
                                                        {{ ucfirst($r->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $r->created_at->format('M j, Y') }}</td>
                                                <td>
                                                    <a href="{{ route('event_requests.show', $r->id) }}" class="btn btn-sm btn-outline-primary">View</a>

                                                    @if($r->status === 'pending' && (auth()->user()->isAdmin() ?? auth()->user()->is_admin ?? false))
                                                        <form action="{{ route('admin.event_requests.approve', $r->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                                        </form>
                                                        <form action="{{ route('admin.event_requests.decline', $r->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger">Decline</button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No requests yet.</p>
                        @endif
                    </div>
                </div>
@endif
        @endauth
    </div>

    <div class="col-lg-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header">
                <h5 class="mb-0">Book This Event</h5>
            </div>
            <div class="card-body">
                @if($event->isBookable())
                    @auth
                     @if($event->type === 'booking')
                            {{-- Existing booking form with ticket types --}}
                            <form action="{{ route('bookings.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="event_id" value="{{ $event->id }}">

                                @php($types = $event->ticketTypes()->where('is_active', true)->orderBy('price')->get())
                                @if($types->count() > 0)
                                    <div class="mb-3">
                                        <label for="ticket_type_id" class="form-label">Ticket Type</label>
                                        <select class="form-select" id="ticket_type_id" name="ticket_type_id" required>
                                            <option value="">Select type</option>
                                            @foreach($types as $t)
                                                <option value="{{ $t->id }}" data-price="{{ $t->price }}">
                                                    {{ $t->name }} — ${{ number_format($t->price, 2) }}
                                                    @if(!is_null($t->capacity)) (cap: {{ $t->capacity }}) @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Number of Tickets</label>
                                    <select class="form-select" id="quantity" name="quantity" required>
                                        @for($i = 1; $i <= min(10, $event->getAvailableSeatsAttribute()); $i++)
                                            <option value="{{ $i }}">{{ $i }} ticket{{ $i > 1 ? 's' : '' }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Price per ticket:</span>
                                        <span class="fw-bold">
                                            <span id="unit-price">
                                                @if($types->count() > 0)
                                                    ${{ number_format($types->first()->price, 2) }}
                                                @else
                                                    @if($event->price > 0)
                                                        ${{ number_format($event->price, 2) }}
                                                    @else
                                                        Free
                                                    @endif
                                                @endif
                                            </span>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Total:</span>
                                        <span class="fw-bold text-primary" id="total-price">
                                            $0.00
                                        </span>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-cart-plus me-1"></i>Book Now
                                </button>
                            </form>

                            <script>
                                (function() {
                                    const quantityEl = document.getElementById('quantity');
                                    const typeEl = document.getElementById('ticket_type_id');
                                    const totalEl = document.getElementById('total-price');
                                    const unitPriceEl = document.getElementById('unit-price');
                                    const hasTypes = !!typeEl;
                                    let unitPrice = hasTypes ? parseFloat(typeEl.selectedOptions[0]?.dataset.price || 0) : {{ $event->price }};

                                    function update() {
                                        const qty = parseInt(quantityEl.value || '0');
                                        if (unitPriceEl) unitPriceEl.textContent = unitPrice > 0 ? '$' + unitPrice.toFixed(2) : 'Free';
                                        const total = qty * unitPrice;
                                        totalEl.textContent = unitPrice > 0 && qty > 0 ? '$' + total.toFixed(2) : '$0.00';
                                    }

                                    quantityEl.addEventListener('change', update);
                                    if (hasTypes) {
                                        typeEl.addEventListener('change', function() {
                                            unitPrice = parseFloat(this.selectedOptions[0].dataset.price || 0);
                                            update();
                                        });
                                    }
                                    update();
                                })();
                            </script>
                        @elseif($event->type === 'request')
                            {{-- Request form link --}}
                            <a href="{{ route('event-requests.create', $event) }}" class="btn btn-warning w-100">
                                <i class="bi bi-envelope-plus me-1"></i> Submit a request for this event
                            </a>
                        @endif
                    @else
                        <div class="text-center">
                            <p class="text-muted">Please log in to book this event.</p>
                            <a href="{{ route('login') }}" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login
                            </a>
                            <a href="{{ route('register') }}" class="btn btn-outline-primary">
                                <i class="bi bi-person-plus me-1"></i>Register
                            </a>
                        </div>
                    @endauth
                @elseif($event->isSoldOut())
                    <div class="alert alert-danger text-center">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>Sold Out</strong><br>
                        This event has no remaining seats.
                    </div>
                @else
                    <div class="alert alert-warning text-center">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>Not Available</strong><br>
                        This event is not currently available for booking.
                    </div>
                @endif

                <div class="border-top pt-3 mt-3">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Tickets will be delivered electronically with QR codes for entry.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

@auth
    @if(auth()->user()->isAdmin())
        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this event? This action cannot be undone.</p>
                        <p class="text-danger"><strong>Note:</strong> Events with existing bookings cannot be deleted.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form id="deleteForm" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete Event</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function confirmDelete(eventId) {
                document.getElementById('deleteForm').action = '/admin/events/' + eventId;
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            }
        </script>
    @endif
@endauth
@endsection
