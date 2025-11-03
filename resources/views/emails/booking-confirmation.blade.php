<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    @extends('layouts.email')

    @section('title', 'Booking Confirmation')

    @section('badge', 'Booking Confirmed')

    @section('content')
        <h1>Thank you for your booking!</h1>
        <p>
            Your booking for <strong>{{ $event->title }}</strong> is confirmed. Your tickets are ready below.
        </p>

        <div class="panel">
            <h2>Event Details</h2>
            <div class="detail-row">
                <span class="label">Event</span>
                <span class="value">{{ $event->title }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Date</span>
                <span class="value">{{ $event->event_date->format('D, M j, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Time</span>
                <span class="value">{{ $event->event_time->format('g:i A') }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Location</span>
                <span class="value">{{ $event->location }}</span>
            </div>
        </div>

        <div class="panel">
            <h2>Booking Summary</h2>
            <div class="detail-row">
                <span class="label">Booking Reference</span>
                <span class="value">{{ $booking->booking_reference ?? ('BK-' . $booking->id) }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Booked By</span>
                <span class="value">{{ $user->name }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Number of Tickets</span>
                <span class="value">{{ $tickets->count() }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Total Amount</span>
                <span class="value">€{{ number_format($booking->total_amount, 2) }}</span>
            </div>
        </div>

        <h2>Your Tickets</h2>
        <p>Please present the QR code on your ticket at the event entrance for scanning.</p>

        @foreach ($tickets as $ticket)
            <div class="panel">
                <div class="detail-row">
                    <span class="label">Ticket ID</span>
                    <span class="value">{{ $ticket->ticket_id ?? $ticket->id }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Ticket Type</span>
                    <span class="value">{{ $ticket->ticketType->name ?? 'General' }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Attendee</span>
                    <span class="value">{{ $ticket->attendee_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Status</span>
                    <span class="value" style="color:#28a745;">{{ $ticket->status->name ?? 'Active' }}</span>
                </div>
                <div class="cta-wrapper" style="margin-top:12px;">
                    @if(isset($qrDataUrls[$ticket->id]))
                        <img src="{{ $qrDataUrls[$ticket->id] }}" alt="Ticket QR Code" style="width:160px;height:160px;" />
                    @elseif(isset($qrCodePaths[$ticket->id]))
                        <img src="{{ config('app.url') . \Illuminate\Support\Facades\Storage::url($qrCodePaths[$ticket->id]) }}" alt="Ticket QR Code" style="width:160px;height:160px;" />
                    @endif
                </div>
            </div>
        @endforeach

        <p style="margin-top: 24px;">
            You can view your booking details online at any time by clicking the button below.
        </p>
        <div class="cta-wrapper">
            <a href="{{ route('bookings.show', $booking) }}" class="cta">View Booking</a>
        </div>
    @endsection

    @section('footer-link', route('bookings.show', $booking))
                                         style="width: 150px; height: 150px; border: 2px solid #dee2e6; border-radius: 6px;">
                                </div>
                            @else
                                <div class="qr-placeholder">
                                    QR Code attached as<br>
                                    ticket-{{ $ticket->id }}-qr.png
                                </div>
                            @endif
                        @else
                            <div class="qr-placeholder">
                                QR Code attached as<br>
                                ticket-{{ $ticket->id }}-qr.png
                            </div>
                        @endif
                        <p style="margin-top: 10px; font-size: 12px; color: #6c757d;">
                            Present this QR code at the venue for entry
                        </p>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Important Information -->
            <div class="important-info">
                <h4>📌 Important Information</h4>
                <ul>
                    <li>Please arrive at least 15 minutes before the event start time</li>
                    <li>Present your QR code (attached images) at the entrance for scanning</li>
                    <li>Keep your booking reference number for any inquiries</li>
                    <li>Tickets are non-transferable and non-refundable</li>
                    @if($event->terms_conditions)
                    <li>Please review the event terms and conditions</li>
                    @endif
                </ul>
            </div>

            <!-- Contact Information -->
            <div style="text-align: center; padding: 20px; background-color: #f8f9fa; border-radius: 6px;">
                <h4 style="color: #495057; margin-bottom: 10px;">Need Help?</h4>
                <p style="color: #6c757d;">
                    If you have any questions about your booking or the event,<br>
                    please contact our support team.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>After Eight Events</strong></p>
            <p>Thank you for choosing us for your event experience!</p>
            <div class="social-links">
                <a href="#">📧 Email</a>
                <a href="#">📱 Phone</a>
                <a href="#">🌐 Website</a>
            </div>
        </div>
    </div>
</body>
</html>
