@extends('layouts.app')

@section('title', 'Tickets')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="bi bi-qr-code me-2"></i>
                @if(auth()->user()->isAdmin())
                    All Tickets
                @else
                    Tickets
                @endif
            </h1>

            @if(auth()->user()->isOperator() || auth()->user()->isAdmin())
                <a href="{{ route('tickets.scan') }}" class="btn btn-outline-primary">
                    <i class="bi bi-qr-code-scan me-1"></i>Scan Tickets
                </a>
            @endif
        </div>

        @if($tickets->count() > 0)
            <div class="row">
                @foreach($tickets as $ticket)
                    <div class="col-lg-6 col-xl-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="card-title mb-1">{{ $ticket->ticket_number }}</h6>
                                        <small class="text-muted">
                                            Booking:
                                            <a href="{{ route('bookings.show', $ticket->booking) }}"
                                               class="text-decoration-none">
                                                {{ $ticket->booking->booking_reference }}
                                            </a>
                                        </small>
                                    </div>
                                    <span class="badge status-badge
                                        @if($ticket->status->value === 'valid') bg-success
                                        @elseif($ticket->status->value === 'used') bg-primary
                                        @elseif($ticket->status->value === 'cancelled') bg-danger
                                        @elseif($ticket->status->value === 'expired') bg-warning
                                        @else bg-secondary @endif">
                                        {{ ucfirst($ticket->status->value) }}
                                    </span>
                                </div>

                                <div class="mb-3">
                                    <h5 class="mb-1">
                                        <a href="{{ route('events.show', $ticket->event) }}"
                                           class="text-decoration-none">
                                            {{ $ticket->event->title }}
                                        </a>
                                    </h5>
                                    @if($ticket->type)
                                        <p class="mb-1">
                                            <i class="bi bi-ticket-detailed me-1"></i>
                                            <strong>Type:</strong> {{ $ticket->type->name }}
                                            @if(!is_null($ticket->price))
                                                <span class="text-muted">• EGP {{ number_format($ticket->price, 2) }}</span>
                                            @endif
                                        </p>
                                    @endif

                                    @if(auth()->user()->isAdmin())
                                        <p class="mb-1">
                                            <i class="bi bi-person me-1"></i>
                                            <strong>Holder:</strong> {{ $ticket->user->name }}
                                        </p>
                                        <p class="mb-1">
                                            <i class="bi bi-envelope me-1"></i>
                                            {{ $ticket->user->email }}
                                        </p>
                                    @endif

                                    <p class="mb-1">
                                        <i class="bi bi-calendar me-1"></i>
                                        {{ $ticket->event->event_date->format('l, F j, Y') }}
                                    </p>
                                    <p class="mb-1">
                                        <i class="bi bi-clock me-1"></i>
                                        {{ $ticket->event->event_time->format('g:i A') }}
                                    </p>
                                    <p class="mb-1">
                                        <i class="bi bi-geo-alt me-1"></i>
                                        {{ $ticket->event->location }}
                                    </p>
                                </div>

                                @if($ticket->scanned_at)
                                    <div class="alert alert-info py-2 mb-3">
                                        <small>
                                            <i class="bi bi-check-circle me-1"></i>
                                            <strong>Used:</strong> {{ $ticket->scanned_at->format('M j, Y g:i A') }}
                                            @if($ticket->scannedBy)
                                                <br>by {{ $ticket->scannedBy->name }}
                                            @endif
                                        </small>
                                    </div>
                                @elseif($ticket->isExpired())
                                    <div class="alert alert-warning py-2 mb-3">
                                        <small>
                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                            <strong>Expired:</strong> Event date has passed
                                        </small>
                                    </div>
                                @elseif($ticket->status->value === 'cancelled')
                                    <div class="alert alert-danger py-2 mb-3">
                                        <small>
                                            <i class="bi bi-x-circle me-1"></i>
                                            <strong>Cancelled:</strong> This ticket is no longer valid
                                        </small>
                                    </div>
                                @endif

                                @if($ticket->seat_number)
                                    <div class="text-center mb-3">
                                        <span class="badge bg-secondary">Seat: {{ $ticket->seat_number }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="card-footer bg-transparent">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <a href="{{ route('tickets.show', $ticket) }}"
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-qr-code me-1"></i>View QR Code
                                        </a>
                                    </div>

                                    <div class="btn-group btn-group-sm">
                                        @if($ticket->status->value === 'valid')
                                            <a href="{{ route('tickets.download', $ticket) }}"
                                               class="btn btn-outline-secondary btn-sm">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        @endif

                                        @if(auth()->user()->isAdmin())
                                            <div class="dropdown">
                                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle"
                                                        type="button"
                                                        data-bs-toggle="dropdown">
                                                    <i class="bi bi-gear"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <h6 class="dropdown-header">Change Status</h6>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="#"
                                                           onclick="updateTicketStatus('{{ $ticket->id }}', 'valid')">
                                                            Valid
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="#"
                                                           onclick="updateTicketStatus('{{ $ticket->id }}', 'used')">
                                                            Used
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="#"
                                                           onclick="updateTicketStatus('{{ $ticket->id }}', 'cancelled')">
                                                            Cancelled
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="#"
                                                           onclick="updateTicketStatus('{{ $ticket->id }}', 'expired')">
                                                            Expired
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
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
                <x-pagination :paginator="$tickets->appends(request()->query())" />
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-qr-code display-1 text-muted"></i>
                <h3 class="mt-3 text-muted">No Tickets Found</h3>
                @if(auth()->user()->isAdmin())
                    <p class="text-muted">No tickets have been generated yet.</p>
                @else
                    <p class="text-muted">You don't have any tickets yet.</p>
                    <a href="{{ route('events.index') }}" class="btn btn-primary">
                        <i class="bi bi-calendar-event me-1"></i>Browse Events
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

@if(auth()->user()->isAdmin())
    <!-- Status Update Form -->
    <form id="statusUpdateForm" method="POST" style="display: none;">
        @csrf
        @method('PATCH')
        <input type="hidden" name="status" id="statusInput">
    </form>

    <script>
        function updateTicketStatus(ticketId, status) {
            if (confirm('Are you sure you want to change this ticket status to "' + status + '"?')) {
                document.getElementById('statusInput').value = status;
                document.getElementById('statusUpdateForm').action = '/tickets/' + ticketId + '/status';
                document.getElementById('statusUpdateForm').submit();
            }
        }
    </script>
@endif
@endsection
