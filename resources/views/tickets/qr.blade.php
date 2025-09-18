@extends('layouts.app')

@section('title', 'QR Code - ' . $ticket->ticket_number)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="card text-center">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-qr-code me-2"></i>Entry QR Code
                </h4>
                <small class="text-muted">{{ $ticket->event->title }}</small>
            </div>
            <div class="card-body p-4">
                @if($ticket->status->value === 'valid')
                    <!-- QR Code Display -->
                    <div class="qr-code-container mb-4">
                        <canvas id="qrcode" class="mx-auto"></canvas>
                    </div>

                    <!-- Ticket Information -->
                    <div class="ticket-info">
                        <h5 class="mb-3">{{ $ticket->ticket_number }}</h5>

                        <div class="row text-start mb-3">
                            <div class="col-sm-6">
                                <p class="mb-1"><strong>Holder:</strong></p>
                                <p class="text-muted">{{ $ticket->user->name }}</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-1"><strong>Event Date:</strong></p>
                                <p class="text-muted">{{ $ticket->event->event_date->format('M j, Y') }}</p>
                            </div>
                        </div>

                        <div class="row text-start mb-3">
                            <div class="col-sm-6">
                                <p class="mb-1"><strong>Time:</strong></p>
                                <p class="text-muted">{{ $ticket->event->event_time->format('g:i A') }}</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-1"><strong>Location:</strong></p>
                                <p class="text-muted">{{ $ticket->event->location }}</p>
                            </div>
                        </div>

                        @if($ticket->seat_number)
                            <div class="alert alert-info">
                                <i class="bi bi-geo-alt me-1"></i>
                                <strong>Seat Number:</strong> {{ $ticket->seat_number }}
                            </div>
                        @endif

                        <div class="mt-4">
                            <span class="badge bg-success fs-6 px-3 py-2">
                                <i class="bi bi-check-circle me-1"></i>Valid for Entry
                            </span>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6><i class="bi bi-info-circle me-1"></i>Instructions</h6>
                        <ul class="list-unstyled text-start mb-0">
                            <li><i class="bi bi-arrow-right me-2 text-primary"></i>Show this QR code at the event entrance</li>
                            <li><i class="bi bi-arrow-right me-2 text-primary"></i>Keep your screen brightness up</li>
                            <li><i class="bi bi-arrow-right me-2 text-primary"></i>Arrive 15 minutes before event time</li>
                            <li><i class="bi bi-arrow-right me-2 text-primary"></i>Have your ID ready if required</li>
                        </ul>
                    </div>
                @else
                    <!-- Invalid Ticket Status -->
                    <div class="text-center py-5">
                        <i class="bi bi-exclamation-triangle display-1 text-warning mb-3"></i>
                        <h4>QR Code Not Available</h4>
                        <p class="text-muted mb-4">
                            This ticket is not valid for entry.
                        </p>

                        <div class="alert alert-warning">
                            <strong>Ticket Status:</strong>
                            <span class="badge
                                @if($ticket->status->value === 'used') bg-primary
                                @elseif($ticket->status->value === 'cancelled') bg-danger
                                @elseif($ticket->status->value === 'expired') bg-warning text-dark
                                @else bg-secondary @endif">
                                {{ ucfirst($ticket->status->value) }}
                            </span>
                        </div>

                        @if($ticket->status->value === 'used')
                            <div class="alert alert-info">
                                <h6><i class="bi bi-check-circle me-1"></i>Already Used</h6>
                                <p class="mb-1">This ticket was scanned on:</p>
                                <p class="mb-0"><strong>{{ $ticket->scanned_at?->format('l, F j, Y g:i A') }}</strong></p>
                            </div>
                        @elseif($ticket->status->value === 'cancelled')
                            <div class="alert alert-danger">
                                <h6><i class="bi bi-x-circle me-1"></i>Ticket Cancelled</h6>
                                <p class="mb-0">This ticket has been cancelled and is no longer valid.</p>
                            </div>
                        @elseif($ticket->status->value === 'expired')
                            <div class="alert alert-warning">
                                <h6><i class="bi bi-calendar-x me-1"></i>Event Passed</h6>
                                <p class="mb-0">This event has already taken place.</p>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Details
                    </a>

                    @if($ticket->status->value === 'valid')
                        <div>
                            <button type="button" class="btn btn-outline-primary me-2" onclick="shareTicket()">
                                <i class="bi bi-share me-1"></i>Share
                            </button>
                            <button type="button" class="btn btn-primary" onclick="downloadQR()">
                                <i class="bi bi-download me-1"></i>Save QR
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Event Quick Reference -->
        <div class="card mt-3">
            <div class="card-body">
                <div class="row align-items-center">
                    @if($ticket->event->image_url)
                        <div class="col-3">
                            <img src="{{ Storage::url($ticket->event->image_url) }}"
                                 alt="{{ $ticket->event->title }}"
                                 class="img-fluid rounded">
                        </div>
                        <div class="col-9">
                    @else
                        <div class="col-12">
                    @endif
                        <h6 class="mb-1">{{ $ticket->event->title }}</h6>
                        <p class="text-muted mb-1">
                            <i class="bi bi-calendar me-1"></i>
                            {{ $ticket->event->event_date->format('l, F j, Y') }} at {{ $ticket->event->event_time->format('g:i A') }}
                        </p>
                        <p class="text-muted mb-0">
                            <i class="bi bi-geo-alt me-1"></i>
                            {{ $ticket->event->location }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Generate QR Code
    @if($ticket->status->value === 'valid')
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('qrcode');
            if (canvas) {
                QRCode.toCanvas(canvas, '{{ $ticket->qr_code }}', {
                    width: 280,
                    height: 280,
                    margin: 3,
                    color: {
                        dark: '#000000',
                        light: '#FFFFFF'
                    }
                }, function (error) {
                    if (error) console.error(error);
                });
            }
        });

        function downloadQR() {
            const canvas = document.getElementById('qrcode');
            const link = document.createElement('a');
            link.download = 'ticket-{{ $ticket->ticket_number }}-qr.png';
            link.href = canvas.toDataURL();
            link.click();
        }

        function shareTicket() {
            if (navigator.share) {
                navigator.share({
                    title: 'Event Ticket - {{ $ticket->event->title }}',
                    text: 'My ticket for {{ $ticket->event->title }} on {{ $ticket->event->event_date->format("F j, Y") }}',
                    url: window.location.href
                }).catch(console.error);
            } else {
                // Fallback: copy link to clipboard
                navigator.clipboard.writeText(window.location.href).then(function() {
                    alert('Ticket link copied to clipboard!');
                }, function() {
                    alert('Unable to copy link. Please share the URL manually.');
                });
            }
        }
    @endif
</script>

<style>
    .qr-code-container {
        padding: 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .ticket-info {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin: 20px 0;
    }

    @media (max-width: 576px) {
        .card-body {
            padding: 1rem !important;
        }

        .qr-code-container canvas {
            max-width: 100%;
            height: auto;
        }
    }

    @media print {
        .btn, .navbar, .card:last-child {
            display: none !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .qr-code-container {
            border: 2px solid #000;
        }
    }
</style>
@endpush
@endsection
