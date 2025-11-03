@extends('layouts.email')

@section('title', 'You have been invited!')

@section('badge', 'Invitation')

@section('content')
    <h1>Hello {{ $invitation->name }}!</h1>
    <p>
        {{ $invitation->sender->name }} has invited you to attend the following event. Please present the QR code at the entrance for scanning.
    </p>

    @if($invitation->event)
        <div class="panel">
            <h2>Event Details</h2>
            <div class="detail-row">
                <span class="label">Event</span>
                <span class="value">{{ $invitation->event->title }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Date</span>
                <span class="value">{{ $invitation->event->event_date->format('F j, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Time</span>
                <span class="value">{{ $invitation->event->event_time->format('g:i A') }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Location</span>
                <span class="value">{{ $invitation->event->location }}</span>
            </div>
        </div>
    @endif

    @if($invitation->message)
        <div class="panel">
            <h2>Message from {{ $invitation->sender->name }}</h2>
            <p>{{ $invitation->message }}</p>
        </div>
    @endif

    @if(!empty($qrDataUrl) || !empty($qrUrl))
        <div class="panel" style="text-align: center;">
            <h2>Your QR Code</h2>
            <img src="{{ $qrDataUrl ?? $qrUrl }}" alt="Invitation QR Code" style="width: 180px; height: 180px;" />
            <p style="margin-top: 12px; color: rgba(248, 249, 250, 0.7);">Show this code at the door for entry.</p>
        </div>
    @endif
@endsection
