@extends('layouts.app')

@section('title', 'Edit Booking')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-pencil me-2"></i>Edit Booking: {{ $booking->booking_reference }}
                </h4>
            </div>
            <div class="card-body">
                <!-- Booking Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Booking Details</h6>
                        <p><strong>Reference:</strong> {{ $booking->booking_reference }}</p>
                        <p><strong>Customer:</strong> {{ $booking->user->name }}</p>
                        <p><strong>Email:</strong> {{ $booking->user->email }}</p>
                        <p><strong>Booking Date:</strong> {{ $booking->booking_date->format('M j, Y g:i A') }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Event Details</h6>
                        <p><strong>Event:</strong> {{ $booking->event->title }}</p>
                        <p><strong>Date:</strong> {{ $booking->event->event_date->format('M j, Y') }}</p>
                        <p><strong>Time:</strong> {{ $booking->event->event_time->format('g:i A') }}</p>
                        <p><strong>Location:</strong> {{ $booking->event->location }}</p>
                    </div>
                </div>

                <hr>

                <!-- Edit Form -->
                <form action="{{ route('bookings.update', $booking) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Booking Status *</label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status"
                                    name="status"
                                    required>
                                <option value="">Select Status</option>
                                <option value="pending" {{ old('status', $booking->status->value) === 'pending' ? 'selected' : '' }}>
                                    Pending
                                </option>
                                <option value="confirmed" {{ old('status', $booking->status->value) === 'confirmed' ? 'selected' : '' }}>
                                    Confirmed
                                </option>
                                <option value="cancelled" {{ old('status', $booking->status->value) === 'cancelled' ? 'selected' : '' }}>
                                    Cancelled
                                </option>
                                <option value="refunded" {{ old('status', $booking->status->value) === 'refunded' ? 'selected' : '' }}>
                                    Refunded
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Booking Details</label>
                            <div class="form-control-plaintext">
                                <div><strong>Quantity:</strong> {{ $booking->quantity }} tickets</div>
                                <div><strong>Total Amount:</strong> EGP {{ number_format($booking->total_amount, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Admin Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                  id="notes"
                                  name="notes"
                                  rows="3"
                                  placeholder="Add any notes about this booking...">{{ old('notes', $booking->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">These notes are visible to other administrators.</div>
                    </div>

                    <!-- Current Tickets -->
                    <div class="mb-4">
                        <h6>Associated Tickets</h6>
                        @if($booking->tickets->count() > 0)
                            <div class="row">
                                @foreach($booking->tickets as $ticket)
                                    <div class="col-md-6 mb-2">
                                        <div class="card bg-light">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <div class="fw-bold">{{ $ticket->ticket_number }}</div>
                                                        <small class="text-muted">
                                                            Status:
                                                            <span class="badge status-badge
                                                                @if($ticket->status->value === 'valid') bg-success
                                                                @elseif($ticket->status->value === 'used') bg-primary
                                                                @elseif($ticket->status->value === 'cancelled') bg-danger
                                                                @elseif($ticket->status->value === 'expired') bg-warning
                                                                @else bg-secondary @endif">
                                                                {{ ucfirst($ticket->status->value) }}
                                                            </span>
                                                        </small>
                                                    </div>
                                                    <a href="{{ route('tickets.show', $ticket) }}"
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </div>

                                                @if($ticket->scanned_at)
                                                    <small class="text-muted d-block mt-1">
                                                        <i class="bi bi-check-circle me-1"></i>
                                                        Scanned: {{ $ticket->scanned_at->format('M j, Y g:i A') }}
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">No tickets generated for this booking.</p>
                        @endif
                    </div>

                    <!-- Status Change Warning -->
                    <div class="alert alert-warning" id="statusWarning" style="display: none;">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>Warning:</strong> Changing the booking status may affect associated tickets.
                        <ul class="mb-0 mt-2">
                            <li><strong>Cancelled:</strong> All tickets will be cancelled automatically</li>
                            <li><strong>Refunded:</strong> Booking will be marked as refunded but tickets remain cancelled</li>
                        </ul>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('bookings.show', $booking) }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Back to Booking
                        </a>

                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Update Booking
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusSelect = document.getElementById('status');
        const statusWarning = document.getElementById('statusWarning');
        const originalStatus = '{{ $booking->status->value }}';

        statusSelect.addEventListener('change', function() {
            const newStatus = this.value;

            if ((newStatus === 'cancelled' || newStatus === 'refunded') && originalStatus !== newStatus) {
                statusWarning.style.display = 'block';
            } else {
                statusWarning.style.display = 'none';
            }
        });
    });
</script>
@endsection
