@extends('layouts.app')

@section('title', $event->title)

@section('content')
    @php
        $types = $event->ticketTypes()->where('is_active', true)->orderBy('price')->get();
        $isAdmin = auth()->check() && auth()->user()->isAdmin();
        $isFinanceOfficer = auth()->check() && auth()->user()->isFinanceOfficer();
    @endphp
    <div class="row">
        {{-- Main Content --}}
        <div class="col-lg-8">
            <div class="ae-card">
                @if ($event->image_url)
                    <img src="{{ Storage::url($event->image_url) }}" class="card-img-top" alt="{{ $event->title }}"
                        style="height: 400px; object-fit: cover;">
                @endif

                <div class="ae-card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h1 class="card-title h2 mb-1">{{ $event->title }}</h1>
                            <span
                                class="badge status-badge @if ($event->status->value === 'published') bg-success @elseif($event->status->value === 'draft') bg-secondary @elseif($event->status->value === 'cancelled') bg-danger @else bg-warning @endif">
                                {{ ucfirst($event->status->value) }}
                            </span>
                        </div>

                        @if ($isAdmin)
                            <div class="dropdown">
                                <button class="btn btn-outline-primary dropdown-toggle" type="button" id="manageEventDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-gear-fill me-1"></i> Manage Event
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="manageEventDropdown">
                                    <li><a class="dropdown-item"
                                            href="{{ route('invitations.create', ['event_id' => $event->id]) }}">
                                            <i class="bi bi-envelope-open me-2"></i>Send Invitation</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.events.edit', $event) }}">
                                            <i class="bi bi-pencil me-2"></i>Edit Event</a></li>
                                    <li><a class="dropdown-item"
                                            href="{{ route('admin.events.ticket-types.index', $event) }}">
                                            <i class="bi bi-ticket-detailed me-2"></i>Manage Tickets</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><button type="button" class="dropdown-item text-danger"
                                            onclick="confirmDelete('{{ $event->id }}')">
                                            <i class="bi bi-trash me-2"></i>Delete Event</button></li>
                                </ul>
                            </div>
                        @endif
                    </div>


                    {{-- Event Details Grid --}}
                    <div class="row g-3 mb-4 event-details-grid">
                        <div class="col-md-6 d-flex align-items-center">
                            <i class="bi bi-calendar-event text-primary me-3 fs-4"></i>
                            <div>
                                <div class="fw-bold">Date</div>
                                <div class="text-muted">{{ $event->event_date->format('l, F j, Y') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <i class="bi bi-clock text-primary me-3 fs-4"></i>
                            <div>
                                <div class="fw-bold">Time</div>
                                <div class="text-muted">{{ $event->event_time->format('g:i A') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <i class="bi bi-geo-alt text-primary me-3 fs-4"></i>
                            <div>
                                <div class="fw-bold">Location</div>
                                <div class="text-muted">{{ $event->location }}</div>
                            </div>
                        </div>
                        @if($event->artists)
                        <div class="col-md-6 d-flex align-items-center">
                            <i class="bi bi-music-note-list text-primary me-3 fs-4"></i>
                            <div>
                                <div class="fw-bold">Artists</div>
                                <div class="text-muted">
                                    @foreach($event->artists_list as $artist)
                                        <span class="badge bg-light text-dark border me-1 mb-1">{{ $artist }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-6 d-flex align-items-center">
                            <i class="bi bi-cash-coin text-primary me-3 fs-4"></i>
                            <div>
                                <div class="fw-bold">Pricing</div>
                                <div class="text-muted">
                                    @if ($types->count() > 0)
                                        From EGP {{ number_format($types->min('price'), 2) }}
                                    @else
                                        To be announced
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h4>Description</h4>
                        <p class="text-muted">{{ $event->description }}</p>
                    </div>

                    @if ($event->terms_conditions)
                        <div class="mb-4">
                            <h5>Terms & Conditions</h5>
                            <p class="small text-muted">{{ $event->terms_conditions }}</p>
                        </div>
                    @endif
                </div>
            </div>

            @if ($isAdmin || $isFinanceOfficer)
                <div class="ae-card mt-4">
                    <div class="ae-card-header">
                        <ul class="nav nav-tabs card-header-tabs" id="adminTab" role="tablist">
                            @if ($isFinanceOfficer || $isAdmin)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="insights-tab" data-bs-toggle="tab"
                                        data-bs-target="#insights" type="button" role="tab" aria-controls="insights"
                                        aria-selected="true"><i class="bi bi-bar-chart-line me-1"></i>Insights</button>
                                </li>
                            @endif
                            @if ($isAdmin)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="bookings-tab" data-bs-toggle="tab"
                                        data-bs-target="#bookings" type="button" role="tab" aria-controls="bookings"
                                        aria-selected="false"><i class="bi bi-journal-text me-1"></i>Bookings</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="requests-tab" data-bs-toggle="tab"
                                        data-bs-target="#requests" type="button" role="tab" aria-controls="requests"
                                        aria-selected="false"><i class="bi bi-envelope-paper me-1"></i>Requests</button>
                                </li>
                            @endif
                        </ul>
                    </div>
                    <div class="ae-card-body">
                        <div class="tab-content" id="adminTabContent">
                            {{-- Insights Tab --}}
                            <div class="tab-pane fade show active" id="insights" role="tabpanel"
                                aria-labelledby="insights-tab">
                                @if (isset($insights) && !empty($insights))
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="stat-card">
                                                <div class="stat-icon text-success"><i class="bi bi-cash-stack"></i></div>
                                                <div class="stat-value">EGP {{ number_format($insights['revenue'], 2) }}
                                                </div>
                                                <div class="stat-label">Total Revenue</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="stat-card">
                                                <div class="stat-icon text-info"><i class="bi bi-ticket-perforated"></i>
                                                </div>
                                                <div class="stat-value">{{ $insights['tickets_sold'] }}</div>
                                                <div class="stat-label">Tickets Sold</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="stat-card">
                                                <div class="stat-icon text-primary"><i class="bi bi-envelope-open"></i>
                                                </div>
                                                <div class="stat-value">{{ $insights['invitations_total'] }}</div>
                                                <div class="stat-label">Invitations Sent</div>
                                            </div>
                                        </div>
                                    </div>
                                    @if ($isAdmin && $insights['invitations_by_admin']->count() > 0)
                                        <h5 class="mt-4">Invitations by Admin</h5>
                                        <ul class="list-group">
                                            @foreach ($insights['invitations_by_admin'] as $inv)
                                                <li
                                                    class="list-group-item d-flex justify-content-between align-items-center">
                                                    {{ $inv['admin'] }}
                                                    <span
                                                        class="badge bg-primary rounded-pill">{{ $inv['count'] }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                @else
                                    <p class="text-muted mb-0">No insights available for this event yet.</p>
                                @endif
                            </div>

                            @if ($isAdmin)
                                {{-- Bookings Tab --}}
                                <div class="tab-pane fade" id="bookings" role="tabpanel"
                                    aria-labelledby="bookings-tab">
                                    @if ($event->bookings->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Reference</th>
                                                        <th>Customer</th>
                                                        <th>Qty</th>
                                                        <th>Amount</th>
                                                        <th>Status</th>
                                                        <th>Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($event->bookings->take(10) as $booking)
                                                        <tr>
                                                            <td><a href="{{ route('bookings.show', $booking) }}"
                                                                    class="text-decoration-none fw-bold">{{ $booking->booking_reference }}</a>
                                                            </td>
                                                            <td>{{ $booking->user->name }}</td>
                                                            <td>{{ $booking->quantity }}</td>
                                                            <td>EGP {{ number_format((float)$booking->total_amount, 2) }}</td>
                                                            <td>
                                                                <span
                                                                    class="badge @if ($booking->status->value === 'confirmed') bg-success @elseif($booking->status->value === 'pending') bg-warning @elseif($booking->status->value === 'cancelled') bg-danger @else bg-secondary @endif">
                                                                    {{ ucfirst($booking->status->value) }}
                                                                </span>
                                                            </td>
                                                            <td class="text-nowrap">
                                                                {{ $booking->booking_date->format('M j, Y') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted mb-0">No bookings have been made for this event yet.</p>
                                    @endif
                                </div>

                                {{-- Requests Tab --}}
                                <div class="tab-pane fade" id="requests" role="tabpanel"
                                    aria-labelledby="requests-tab">
                                    @php
                                        $recentRequests = \App\Models\EventRequest::where('event_id', $event->id)
                                            ->latest()
                                            ->limit(10)
                                            ->get();
                                    @endphp
                                    @if ($recentRequests->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Requester</th>
                                                        <th>Status</th>
                                                        <th>Date</th>
                                                        <th class="text-end">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($recentRequests as $r)
                                                        <tr>
                                                            <td>{{ $r->user->name ?? 'N/A' }}</td>
                                                            <td>
                                                                <span
                                                                    class="badge @if ($r->status === 'approved') bg-success @elseif($r->status === 'pending') bg-warning @elseif($r->status === 'declined') bg-danger @else bg-secondary @endif">
                                                                    {{ ucfirst($r->status) }}
                                                                </span>
                                                            </td>
                                                            <td class="text-nowrap">
                                                                {{ $r->created_at->format('M j, Y') }}</td>
                                                            <td class="text-end text-nowrap">
                                                                <a href="{{ route('event_requests.show', $r->id) }}"
                                                                    class="btn btn-sm btn-icon btn-outline-primary"><i
                                                                        class="bi bi-eye"></i></a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted mb-0">No requests yet.
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar / Action Column --}}
        <div class="col-lg-4">
            <div class="ae-card sticky-top" style="top: 20px;">
                <div class="ae-card-header">
                    <h5 class="mb-0">
                        @if ($event->isBookable())
                            Book This Event
                        @else
                            Booking Status
                        @endif
                    </h5>
                </div>
                <div class="ae-card-body">
                    @if ($event->isBookable())
                        @auth
                            @if ($isAdmin)
                                @php
                                    $reservedSeats = $event->capacity - $event->available_seats;
                                    $showInitial = !is_null($event->initial_capacity) && $event->initial_capacity != $event->capacity;
                                @endphp
                                <div class="text-center">
                                    <div class="display-4">{{ $event->available_seats }}</div>
                                    <div class="text-muted">Seats Available</div>
                                </div>
                                <hr>
                                <ul class="list-unstyled">
                                    <li class="d-flex justify-content-between">
                                        <span class="text-muted">Initial Capacity</span>
                                        <strong>{{ $event->initial_capacity ?? $event->capacity }}</strong>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <span class="text-muted">Current Capacity</span>
                                        <strong>{{ $event->capacity }}</strong>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <span class="text-muted">Reserved Seats</span>
                                        <strong>{{ $reservedSeats }}</strong>
                                    </li>
                                </ul>
                                <div class="alert alert-info mt-3 text-center small">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Administrators cannot create bookings.
                                </div>
                            @elseif($isFinanceOfficer)
                                <div class="alert alert-warning text-center">
                                    <i class="bi bi-shield-lock me-1"></i>
                                    Finance Officers cannot create bookings or requests.
                                </div>
                            @else
                                @if ($event->type === 'booking')
                                    {{-- Existing booking form with ticket types --}}
                                    <form action="{{ route('bookings.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="event_id" value="{{ $event->id }}">

                                        @if ($types->count() > 0)
                                            <div class="mb-3">
                                                <label for="ticket_type_id" class="form-label">Ticket Type</label>
                                                <select class="form-select" id="ticket_type_id" name="ticket_type_id"
                                                    required>
                                                    <option value="">Select type</option>
                                                    @foreach ($types as $t)
                                                        <option value="{{ $t->id }}" data-price="{{ $t->price }}" data-total="{{ $t->total_with_fee }}">
                                                            {{ $t->name }} — EGP {{ number_format($t->total_with_fee, 2) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @else
                                            <div class="alert alert-light mb-3">
                                                Ticket types will be available soon.
                                            </div>
                                        @endif

                                        @php
                                            $maxTickets = min(10, $event->getAvailableSeatsAttribute());
                                        @endphp
                                        <div class="mb-3">
                                            <label for="quantity" class="form-label">Number of Tickets</label>
                                            @if ($maxTickets < 1)
                                                <div class="alert alert-warning mb-0 small">
                                                    No seats available for booking.
                                                </div>
                                            @else
                                                <select class="form-select" id="quantity" name="quantity" required>
                                                    @for ($i = 1; $i <= $maxTickets; $i++)
                                                        <option value="{{ $i }}">{{ $i }}
                                                            ticket{{ $i > 1 ? 's' : '' }}</option>
                                                    @endfor
                                                </select>
                                            @endif
                                        </div>

                                        <div class="mb-3 pt-2 border-top">
                                            <div class="d-flex justify-content-between small">
                                                <span class="text-muted">Price per ticket:</span>
                                                <span class="fw-bold" id="unit-price">...</span>
                                            </div>
                                            <div class="d-flex justify-content-between fs-5">
                                                <span>Total:</span>
                                                <span class="fw-bold text-primary" id="total-price">EGP 0.00</span>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary w-100"
                                            @if ($maxTickets < 1 || $types->count() === 0) disabled @endif>
                                            <i class="bi bi-cart-plus me-1"></i>Book Now
                                        </button>

                                        <script>
                                            (function() {
                                                const quantityEl = document.getElementById('quantity');
                                                const typeEl = document.getElementById('ticket_type_id');
                                                const totalEl = document.getElementById('total-price');
                                                const unitPriceEl = document.getElementById('unit-price');
                                                const hasTypes = !!typeEl;

                                                function getSelectedTypePrice(includeFee = true) {
                                                    if (!hasTypes) return 0;
                                                    const selected = typeEl.selectedOptions[0];
                                                    if(!selected) return 0;
                                                    const attr = includeFee ? 'total' : 'price';
                                                    const key = includeFee ? 'dataset.total' : 'dataset.price';
                                                    const value = includeFee ? selected.dataset.total : selected.dataset.price;
                                                    return value !== undefined ? parseFloat(value) : 0;
                                                }

                                                function currentUnitPrice() { return getSelectedTypePrice(true); }

                                                function update() {
                                                    const unitPrice = currentUnitPrice();
                                                    const qty = parseInt(quantityEl.value || '0', 10);
                                                    if (unitPriceEl) unitPriceEl.textContent = unitPrice > 0 ? 'EGP ' + unitPrice.toFixed(2) : '{{ $types->count() > 0 ? 'Select a ticket type' : 'Pricing will be announced' }}';
                                                    totalEl.textContent = unitPrice > 0 && qty > 0 ? 'EGP ' + (qty * unitPrice).toFixed(2) : 'EGP 0.00';
                                                }

                                                if (hasTypes) {
                                                    typeEl.addEventListener('change', update);
                                                }
                                                if (quantityEl) {
                                                    quantityEl.addEventListener('change', update);
                                                    quantityEl.addEventListener('input', update);
                                                }
                                                update();
                                            })();
                                        </script>
                                    </form>
                                @elseif($event->type === 'request')
                                    @php
                                        $existingRequest = \App\Models\EventRequest::where('event_id', $event->id)
                                            ->where('user_id', auth()->id())
                                            ->latest()
                                            ->first();
                                    @endphp
                                    @if ($existingRequest)
                                        <div class="alert alert-light">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong>Your Request Status:</strong>
                                                    <span
                                                        class="badge @if ($existingRequest->status === 'approved') bg-success @elseif($existingRequest->status === 'pending') bg-warning text-dark @elseif($existingRequest->status === 'declined') bg-danger @else bg-secondary @endif">
                                                        {{ ucfirst($existingRequest->status) }}
                                                    </span>
                                                    <div class="small text-muted mt-1">Submitted
                                                        {{ $existingRequest->created_at->diffForHumans() }}</div>
                                                </div>
                                                <div class="ms-3 d-flex flex-column gap-2">
                                                    <a href="{{ route('event_requests.show', $existingRequest->id) }}"
                                                        class="btn btn-sm btn-outline-secondary"><i
                                                            class="bi bi-eye"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <a href="{{ route('event-requests.create', $event) }}"
                                            class="btn btn-warning w-100">
                                            <i class="bi bi-envelope-plus me-1"></i> Request Access
                                        </a>
                                    @endif
                                @endif
                            @endif
                        @else
                            <div class="text-center">
                                <p class="text-muted">Log in or register to book this event.</p>
                                <a href="{{ route('login') }}" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>Login
                                </a>
                                <a href="{{ route('register') }}" class="btn btn-outline-secondary mt-2">
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
                        <small class="text-muted d-flex align-items-center">
                            <i class="bi bi-shield-check me-2 text-success"></i>
                            Tickets are delivered electronically with secure QR codes for entry.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($isAdmin)
        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
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
                        <form id="deleteForm" method="POST" class="d-inline"
                            action="">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Yes, Delete Event</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                window.confirmDelete = function(eventId) {
                    const form = document.getElementById('deleteForm');
                    form.action = '/admin/events/' + eventId;
                    new bootstrap.Modal(document.getElementById('deleteModal')).show();
                };
            });
        </script>
    @endif
@endsection
