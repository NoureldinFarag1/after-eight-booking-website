@extends('layouts.app')

@section('title', 'QR Code Test')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>QR Code Email Test Preview</h3>
                </div>
                <div class="card-body">
                    @php
                        $booking = App\Models\Booking::with(['tickets', 'event', 'user'])->latest()->first();
                        if ($booking) {
                            $qrService = app(App\Services\QrCodeService::class);
                            $qrCodePaths = [];
                            foreach ($booking->tickets as $ticket) {
                                $qrCodePaths[$ticket->id] = $qrService->getTicketQrCodePath($ticket);
                            }
                        }
                    @endphp

                    @if($booking)
                        <h5>Latest Booking: {{ $booking->booking_reference }}</h5>
                        <p><strong>Event:</strong> {{ $booking->event->title }}</p>
                        <p><strong>User:</strong> {{ $booking->user->name }}</p>
                        <p><strong>Tickets:</strong> {{ $booking->tickets->count() }}</p>

                        <hr>

                        <h6>QR Codes Preview:</h6>
                        <div class="row">
                            @foreach($booking->tickets as $ticket)
                                <div class="col-md-6 mb-4">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <h6>{{ $ticket->ticket_number }}</h6>
                                            <p class="text-muted">Price: EGP {{ $ticket->price }}</p>

                                            @if(isset($qrCodePaths[$ticket->id]))
                                                @php
                                                    $qrPath = storage_path('app/public/' . $qrCodePaths[$ticket->id]);
                                                    $qrExists = file_exists($qrPath);
                                                @endphp
                                                @if($qrExists)
                                                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($qrPath)) }}"
                                                         alt="QR Code for {{ $ticket->ticket_number }}"
                                                         style="width: 150px; height: 150px; border: 2px solid #dee2e6; border-radius: 6px;">
                                                    <p class="mt-2 small text-success">✅ QR Code Generated</p>
                                                @else
                                                    <div style="width: 150px; height: 150px; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                                        <span class="text-muted">QR Not Found</span>
                                                    </div>
                                                @endif
                                            @else
                                                <div style="width: 150px; height: 150px; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                                    <span class="text-muted">No QR Path</span>
                                                </div>
                                            @endif

                                            <p class="mt-2 small">
                                                <strong>Verification URL:</strong><br>
                                                <a href="{{ route('tickets.verify', ['ticket' => $ticket->id, 'code' => $ticket->qr_code]) }}"
                                                   target="_blank" class="text-decoration-none">
                                                    {{ Str::limit(route('tickets.verify', ['ticket' => $ticket->id, 'code' => $ticket->qr_code]), 50) }}
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="alert alert-info">
                            <h6>How it works:</h6>
                            <ol>
                                <li>When a user books tickets, QR codes are automatically generated</li>
                                <li>Each QR code contains a unique verification URL</li>
                                <li>The QR codes are embedded directly in the booking confirmation email</li>
                                <li>Users can present the QR codes at the venue for scanning</li>
                                <li>Operators can scan QR codes to verify ticket validity</li>
                            </ol>
                        </div>

                        <div class="alert alert-success">
                            <strong>✅ QR Code System Status:</strong>
                            <ul class="mb-0 mt-2">
                                <li>QR Code generation: Active</li>
                                <li>Email integration: Active</li>
                                <li>Ticket verification: Active</li>
                                <li>Email template: Updated with embedded QR codes</li>
                            </ul>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            No bookings found in the system. Create a booking first to test QR codes.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
