@extends('layouts.app')

@section('title', 'Booking')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bi bi-ticket-perforated me-2"></i>Booking
                    </h4>
                    <span class="badge status-badge
                        @if($booking->status->value === 'confirmed') bg-success
                        @elseif($booking->status->value === 'pending') bg-warning
                        @elseif($booking->status->value === 'cancelled') bg-danger
                        @elseif($booking->status->value === 'refunded') bg-info
                        @else bg-secondary @endif">
                        {{ ucfirst($booking->status->value) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Booking Information</h6>
                        <p><strong>Booking Reference:</strong> {{ $booking->booking_reference }}</p>
                        <p><strong>Booking Date:</strong> {{ $booking->booking_date->format('l, F j, Y g:i A') }}</p>
                        <p><strong>Quantity:</strong> {{ $booking->quantity }} ticket{{ $booking->quantity > 1 ? 's' : '' }}</p>
                        @php
                            $firstType = $booking->tickets->first()?->type;
                            $unitBase = $firstType?->price ?? ($booking->tickets->first()?->price ?? 0); // original base stored in type
                            $unitFinal = $booking->tickets->first()?->price ?? 0; // stored final
                            $unitFee = 0;
                            if($firstType){
                                $unitFee = $unitFinal - (float)$firstType->price;
                            }
                            $totalFee = $unitFee * $booking->quantity;
                        @endphp
                        <p><strong>Total Amount:</strong> EGP {{ number_format((float)$booking->total_amount, 2) }}</p>
                        @if($unitFee > 0)
                            <p class="small text-muted mb-1">
                                Base: EGP {{ number_format((float)$unitBase,2) }} + Fee: EGP {{ number_format((float)$unitFee,2) }} per ticket
                            </p>
                            <p class="small text-muted mb-2">Total Fees: EGP {{ number_format((float)$totalFee,2) }}</p>
                        @endif

                        @if($booking->notes)
                            <p><strong>Notes:</strong> {{ $booking->notes }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Customer Information</h6>
                        <p><strong>Name:</strong> {{ $booking->user->name }}</p>
                        <p><strong>Email:</strong> {{ $booking->user->email }}</p>
                        @if($booking->user->phone)
                            <p><strong>Phone:</strong> {{ $booking->user->phone }}</p>
                        @endif
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="text-muted">Event Information</h6>
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="row">
                                @if($booking->event->image_url)
                                    <div class="col-md-4">
                                        <img src="{{ Storage::url($booking->event->image_url) }}"
                                             alt="{{ $booking->event->title }}"
                                             class="img-fluid rounded">
                                    </div>
                                    <div class="col-md-8">
                                @else
                                    <div class="col-12">
                                @endif
                                    <h5>{{ $booking->event->title }}</h5>
                                    <p class="text-muted">{{ $booking->event->description }}</p>

                                    <div class="row">
                                        <div class="col-sm-6">
                                            <p><i class="bi bi-calendar me-1"></i> {{ $booking->event->event_date->format('l, F j, Y') }}</p>
                                            <p><i class="bi bi-clock me-1"></i> {{ $booking->event->event_time->format('g:i A') }}</p>
                                        </div>
                                        <div class="col-sm-6">
                                            <p><i class="bi bi-geo-alt me-1"></i> {{ $booking->event->location }}</p>
                                            @php
                                                $firstTicket = $booking->tickets->first();
                                            @endphp
                                            @if($firstTicket)
                                                <p>
                                                    <i class="bi bi-cash-coin me-1"></i>
                                                    EGP {{ number_format((float)$firstTicket->price, 2) }} per ticket
                                                    @if($firstTicket->type)
                                                        <span class="text-muted">— {{ $firstTicket->type->name }}</span>
                                                        @php
                                                            $feePer = $firstTicket->price - (float)$firstTicket->type->price;
                                                        @endphp
                                                        @if($feePer > 0)
                                                            <br><small class="text-muted">Includes fee: EGP {{ number_format((float)$feePer,2) }}</small>
                                                        @endif
                                                    @endif
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('bookings.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Bookings
                    </a>

                    <div>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('bookings.edit', $booking) }}" class="btn btn-outline-primary">
                                <i class="bi bi-pencil me-1"></i>Edit Booking
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-qr-code me-2"></i>Tickets
                </h5>
            </div>
            <div class="card-body">
                @if($booking->tickets->count() > 0)
                    <div class="d-grid gap-2">
                        @foreach($booking->tickets as $ticket)
                            <div class="card bg-light">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $ticket->ticket_number }}</h6>
                                            <span class="badge status-badge
                                                @if($ticket->status->value === 'valid') bg-success
                                                @elseif($ticket->status->value === 'used') bg-primary
                                                @elseif($ticket->status->value === 'cancelled') bg-danger
                                                @elseif($ticket->status->value === 'expired') bg-warning
                                                @else bg-secondary @endif">
                                                {{ ucfirst($ticket->status->value) }}
                                            </span>
                                        </div>
                                        <div>
                                            <a href="{{ route('tickets.show', $ticket) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-qr-code"></i>
                                            </a>
                                        </div>
                                    </div>

                                    @if($ticket->scanned_at)
                                        <small class="text-muted d-block mt-2">
                                            <i class="bi bi-check-circle me-1"></i>
                                            Scanned: {{ $ticket->scanned_at->format('M j, Y g:i A') }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('tickets.index') }}" class="btn btn-primary w-100">
                            <i class="bi bi-collection me-1"></i>View All Tickets
                        </a>
                    </div>
                @else
                    <p class="text-muted mb-0">No tickets generated yet.</p>
                @endif
            </div>
        </div>

        @if($booking->status->value === 'confirmed')
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>Important Information
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-1"></i>
                            Your tickets are ready
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-qr-code text-primary me-1"></i>
                            Show QR codes at event entrance
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-clock text-warning me-1"></i>
                            Arrive 15 minutes early
                        </li>
                        <li>
                            <i class="bi bi-envelope text-info me-1"></i>
                            Confirmation sent to email
                        </li>
                    </ul>
                </div>
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
