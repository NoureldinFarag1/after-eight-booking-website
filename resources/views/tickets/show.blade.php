@extends('layouts.app')

@section('title', 'Ticket Details')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bi bi-qr-code me-2"></i>Ticket Details
                    </h4>
                    <span class="badge status-badge
                        @if($ticket->status->value === 'valid') bg-success
                        @elseif($ticket->status->value === 'used') bg-primary
                        @elseif($ticket->status->value === 'cancelled') bg-danger
                        @elseif($ticket->status->value === 'expired') bg-warning
                        @else bg-secondary @endif">
                        {{ ucfirst($ticket->status->value) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <!-- Ticket Information -->
                        <h6 class="text-muted">Ticket Information</h6>
                        <p><strong>Ticket Number:</strong> {{ $ticket->ticket_number }}</p>
                        <p>
                            <strong>Booking Reference:</strong>
                            <a href="{{ route('bookings.show', $ticket->booking) }}" class="text-decoration-none">
                                {{ $ticket->booking->booking_reference }}
                            </a>
                        </p>
                        @if($ticket->type)
                            <p class="mb-1"><strong>Type:</strong> {{ $ticket->type->name }}</p>
                        @endif
                        @if(!is_null($ticket->price))
                            <p class="mb-1"><strong>Price:</strong> EGP {{ number_format($ticket->price, 2) }}</p>
                        @endif
                        @if($ticket->seat_number)
                            <p><strong>Seat Number:</strong> {{ $ticket->seat_number }}</p>
                        @endif
                        <p><strong>Status:</strong>
                            <span class="badge status-badge
                                @if($ticket->status->value === 'valid') bg-success
                                @elseif($ticket->status->value === 'used') bg-primary
                                @elseif($ticket->status->value === 'cancelled') bg-danger
                                @elseif($ticket->status->value === 'expired') bg-warning
                                @else bg-secondary @endif">
                                {{ ucfirst($ticket->status->value) }}
                            </span>
                        </p>

                        @if($ticket->scanned_at)
                            <div class="alert alert-info">
                                <h6><i class="bi bi-check-circle me-1"></i>Ticket Used</h6>
                                <p class="mb-1"><strong>Scanned:</strong> {{ $ticket->scanned_at->format('l, F j, Y g:i A') }}</p>
                                @if($ticket->scannedBy)
                                    <p class="mb-0"><strong>Scanned by:</strong> {{ $ticket->scannedBy->name }}</p>
                                @endif
                            </div>
                        @elseif($ticket->isExpired())
                            <div class="alert alert-warning">
                                <h6><i class="bi bi-exclamation-triangle me-1"></i>Ticket Expired</h6>
                                <p class="mb-0">This event has already taken place.</p>
                            </div>
                        @elseif($ticket->status->value === 'cancelled')
                            <div class="alert alert-danger">
                                <h6><i class="bi bi-x-circle me-1"></i>Ticket Cancelled</h6>
                                <p class="mb-0">This ticket is no longer valid for entry.</p>
                            </div>
                        @endif

                        <!-- Customer Information -->
                        <h6 class="text-muted mt-4">Ticket Holder</h6>
                        <p><strong>Name:</strong> {{ $ticket->user->name }}</p>
                        <p><strong>Email:</strong> {{ $ticket->user->email }}</p>
                        @if($ticket->user->phone)
                            <p><strong>Phone:</strong> {{ $ticket->user->phone }}</p>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <!-- QR Code -->
                        <div class="text-center mb-4">
                            <h6 class="text-muted">Entry QR Code</h6>
                            @if($ticket->status->value === 'valid')
                                <div class="qr-code-container" id="qrcode-container">
                                    <canvas id="qrcode" style="border: 1px solid #ddd; background: white;"></canvas>
                                    <div id="qr-fallback" style="display: none;" class="bg-light p-3 rounded">
                                        <p class="mb-2 text-muted">QR Code (fallback):</p>
                                        <p class="font-monospace small">{{ $ticket->qr_code }}</p>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('{{ $ticket->qr_code }}')">
                                            <i class="bi bi-clipboard"></i> Copy Code
                                        </button>
                                    </div>
                                </div>
                                <p class="mt-2 small text-muted">
                                    Show this QR code at the event entrance
                                </p>
                            @else
                                <div class="bg-light rounded p-4">
                                    <i class="bi bi-qr-code display-4 text-muted"></i>
                                    <p class="mt-2 text-muted mb-0">
                                        QR code not available<br>
                                        (Ticket status: {{ ucfirst($ticket->status->value) }})
                                    </p>
                                </div>
                            @endif
                        </div>

                        <!-- Event Quick Info -->
                        <div class="card bg-light">
                            <div class="card-body p-3">
                                <h6 class="card-title">Event Quick Info</h6>
                                <p class="mb-1"><strong>{{ $ticket->event->title }}</strong></p>
                                <p class="mb-1">
                                    <i class="bi bi-calendar me-1"></i>
                                    {{ $ticket->event->event_date->format('l, F j, Y') }}
                                </p>
                                <p class="mb-1">
                                    <i class="bi bi-clock me-1"></i>
                                    {{ $ticket->event->event_time->format('g:i A') }}
                                </p>
                                <p class="mb-0">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    {{ $ticket->event->location }}
                                </p>
                                @if($ticket->type)
                                    <p class="mb-0 mt-1">
                                        <i class="bi bi-ticket-detailed me-1"></i>
                                        Type: {{ $ticket->type->name }}
                                        @if(!is_null($ticket->price))
                                            • EGP {{ number_format($ticket->price, 2) }}
                                        @endif
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Event Details -->
                <hr>
                <div class="mb-4">
                    <h6 class="text-muted">Event Details</h6>
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="row">
                                @if($ticket->event->image_url)
                                    <div class="col-md-4">
                                        <img src="{{ Storage::url($ticket->event->image_url) }}"
                                             alt="{{ $ticket->event->title }}"
                                             class="img-fluid rounded">
                                    </div>
                                    <div class="col-md-8">
                                @else
                                    <div class="col-12">
                                @endif
                                    <h5>{{ $ticket->event->title }}</h5>
                                    <p class="text-muted">{{ $ticket->event->description }}</p>

                                    <div class="row">
                                        <div class="col-sm-6">
                                            <p class="mb-1">
                                                <i class="bi bi-calendar text-primary me-1"></i>
                                                {{ $ticket->event->event_date->format('l, F j, Y') }}
                                            </p>
                                            <p class="mb-1">
                                                <i class="bi bi-clock text-primary me-1"></i>
                                                {{ $ticket->event->event_time->format('g:i A') }}
                                            </p>
                                        </div>
                                        <div class="col-sm-6">
                                            <p class="mb-1">
                                                <i class="bi bi-geo-alt text-primary me-1"></i>
                                                {{ $ticket->event->location }}
                                            </p>
                                            @if(!is_null($ticket->price))
                                                <p class="mb-1">
                                                    <i class="bi bi-cash-coin text-primary me-1"></i>
                                                    EGP {{ number_format($ticket->price, 2) }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    <a href="{{ route('events.show', $ticket->event) }}"
                                       class="btn btn-outline-primary btn-sm">
                                        View Event Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('tickets.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Tickets
                    </a>

                    <div>
                        @if($ticket->status->value === 'valid')
                            <a href="{{ route('tickets.download', $ticket) }}" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-download me-1"></i>Download
                            </a>
                            <button type="button" class="btn btn-primary" onclick="printTicket()">
                                <i class="bi bi-printer me-1"></i>Print
                            </button>
                        @endif

                        @if(auth()->user()->isAdmin())
                            <div class="btn-group ms-2">
                                <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bi bi-gear me-1"></i>Admin Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><h6 class="dropdown-header">Change Status</h6></li>
                                    <li><a class="dropdown-item" href="#" onclick="updateStatus('valid')">Valid</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="updateStatus('used')">Used</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="updateStatus('cancelled')">Cancelled</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="updateStatus('expired')">Expired</a></li>
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->isAdmin())
    <!-- Status Update Form -->
    <form id="statusUpdateForm" method="POST" action="{{ route('tickets.update-status', $ticket) }}" style="display: none;">
        @csrf
        @method('PATCH')
        <input type="hidden" name="status" id="statusInput">
    </form>
@endif

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
<script>
    // Check if QRious library loaded
    console.log('QRious library available:', typeof QRious !== 'undefined');
    console.log('QRious object:', typeof QRious !== 'undefined' ? QRious : 'undefined');

    // Generate QR Code
    @if($ticket->status->value === 'valid')
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, attempting to generate QR code...');
            const canvas = document.getElementById('qrcode');
            const fallback = document.getElementById('qr-fallback');
            console.log('Canvas element:', canvas);
            console.log('Fallback element:', fallback);

            if (canvas && typeof QRious !== 'undefined') {
                console.log('Generating QR code for: {{ $ticket->qr_code }}');

                // Try to generate QR code using QRious
                try {
                    const qr = new QRious({
                        element: canvas,
                        value: '{{ $ticket->qr_code }}',
                        size: 200,
                        background: '#ffffff',
                        foreground: '#000000',
                        padding: 10
                    });

                    console.log('QR Code generated successfully with QRious');
                    canvas.style.display = 'block';
                } catch (e) {
                    console.error('Exception during QR code generation:', e);
                    showFallback();
                }
            } else {
                console.error('QRious library not loaded or canvas not found');
                console.error('QRious available:', typeof QRious !== 'undefined');
                console.error('Canvas element:', canvas);
                showFallback();
            }

            function showFallback() {
                if (canvas) canvas.style.display = 'none';
                if (fallback) fallback.style.display = 'block';
                console.log('Showing fallback QR code display');
            }
        });
    @endif

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            alert('QR code copied to clipboard!');
        }, function(err) {
            console.error('Could not copy text: ', err);
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            alert('QR code copied to clipboard!');
        });
    }

    function printTicket() {
        window.print();
    }

    @if(auth()->user()->isAdmin())
        function updateStatus(status) {
            if (confirm('Are you sure you want to change this ticket status to "' + status + '"?')) {
                document.getElementById('statusInput').value = status;
                document.getElementById('statusUpdateForm').submit();
            }
        }
    @endif
</script><style>
    @media print {
        .btn, .navbar, .card-header, .breadcrumb, footer {
            display: none !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .qr-code-container {
            border: 2px solid #000;
            padding: 10px;
        }
    }
</style>
@endpush
@endsection
