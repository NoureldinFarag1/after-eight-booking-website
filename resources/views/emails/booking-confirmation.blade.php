<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 30px 20px;
        }
        .event-details {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .event-details h2 {
            color: #495057;
            font-size: 22px;
            margin-bottom: 15px;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 10px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #6c757d;
        }
        .detail-value {
            color: #495057;
            text-align: right;
        }
        .booking-summary {
            background-color: #e8f5e8;
            border-left: 4px solid #28a745;
            padding: 20px;
            margin-bottom: 25px;
        }
        .booking-summary h3 {
            color: #155724;
            font-size: 18px;
            margin-bottom: 10px;
        }
        .tickets-section {
            margin-bottom: 25px;
        }
        .tickets-section h3 {
            color: #495057;
            font-size: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        .ticket {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #ffffff;
        }
        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        .ticket-number {
            font-weight: 700;
            color: #495057;
            font-size: 16px;
        }
        .ticket-status {
            background-color: #28a745;
            color: #ffffff;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .qr-code-container {
            text-align: center;
            margin-top: 15px;
        }
        .qr-placeholder {
            width: 150px;
            height: 150px;
            border: 2px dashed #dee2e6;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 14px;
            margin: 0 auto;
        }
        .important-info {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 25px;
        }
        .important-info h4 {
            color: #856404;
            margin-bottom: 10px;
        }
        .important-info ul {
            color: #856404;
            margin-left: 20px;
        }
        .footer {
            background-color: #343a40;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .footer p {
            margin-bottom: 5px;
        }
        .social-links {
            margin-top: 15px;
        }
        .social-links a {
            color: #ffffff;
            text-decoration: none;
            margin: 0 10px;
        }
        @media (max-width: 600px) {
            .email-container {
                margin: 0;
                border-radius: 0;
            }
            .content {
                padding: 20px 15px;
            }
            .detail-row {
                flex-direction: column;
                gap: 5px;
            }
            .detail-value {
                text-align: left;
            }
            .ticket-header {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>🎟️ Booking Confirmed!</h1>
            <p>Your tickets are ready</p>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Greeting -->
            <p style="font-size: 16px; margin-bottom: 20px;">
                Hello <strong>{{ $user->name }}</strong>,
            </p>
            <p style="margin-bottom: 25px;">
                Thank you for your booking! Your tickets have been confirmed and are ready for use.
            </p>

            <!-- Booking Summary -->
            <div class="booking-summary">
                <h3>📋 Booking Summary</h3>
                <div class="detail-row">
                    <span class="detail-label">Booking Reference:</span>
                    <span class="detail-value"><strong>{{ $booking->booking_reference }}</strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Amount:</span>
                    <span class="detail-value"><strong>EGP {{ number_format($booking->total_amount, 2) }}</strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Number of Tickets:</span>
                    <span class="detail-value"><strong>{{ $booking->quantity }}</strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Booking Date:</span>
                    <span class="detail-value">{{ $booking->booking_date->format('F j, Y \a\t g:i A') }}</span>
                </div>
            </div>

            <!-- Event Details -->
            <div class="event-details">
                <h2>🎪 Event Details</h2>
                <div class="detail-row">
                    <span class="detail-label">Event Name:</span>
                    <span class="detail-value"><strong>{{ $event->title }}</strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value">{{ $event->event_date->format('l, F j, Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Time:</span>
                    <span class="detail-value">{{ $event->event_time }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Location:</span>
                    <span class="detail-value">{{ $event->location }}</span>
                </div>
                @if($event->description)
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #dee2e6;">
                    <p style="color: #6c757d; font-style: italic;">{{ Str::limit($event->description, 200) }}</p>
                </div>
                @endif
            </div>

            <!-- Tickets Section -->
            <div class="tickets-section">
                <h3>🎫 Your Tickets</h3>
                @foreach($tickets as $ticket)
                <div class="ticket">
                    <div class="ticket-header">
                        <span class="ticket-number">{{ $ticket->ticket_number }}</span>
                        <span class="ticket-status">{{ ucfirst($ticket->status->value) }}</span>
                    </div>

                    @if($ticket->type)
                    <div class="detail-row">
                        <span class="detail-label">Ticket Type:</span>
                        <span class="detail-value">{{ $ticket->type->name }}</span>
                    </div>
                    @endif

                    <div class="detail-row">
                        <span class="detail-label">Price:</span>
                        <span class="detail-value">EGP {{ number_format($ticket->price, 2) }}</span>
                    </div>

                    <div class="qr-code-container">
                        <p style="margin-bottom: 10px; font-size: 14px; color: #6c757d;">
                            <strong>QR Code for Entry</strong>
                        </p>
                        @if(isset($qrCodePaths[$ticket->id]))
                            @php
                                $qrPath = storage_path('app/public/' . $qrCodePaths[$ticket->id]);
                                $qrExists = file_exists($qrPath);
                            @endphp
                            @if($qrExists)
                                <div style="text-align: center; margin: 15px 0;">
                                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($qrPath)) }}"
                                         alt="QR Code for {{ $ticket->ticket_number }}"
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
