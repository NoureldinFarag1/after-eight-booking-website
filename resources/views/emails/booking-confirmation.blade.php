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
            <span class="value">€{{ number_format((float) ($booking->total_amount ?? 0), 2) }}</span>
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
