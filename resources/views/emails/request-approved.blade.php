@extends('layouts.email')

@section('title', 'Request Awaiting Payment')

@section('badge', "You're almost there · awaiting payment")

@section('content')
    <h1>Hello {{ $userName }},</h1>
    <p>
        Your access request for <strong>{{ $eventName }}</strong> has been approved.
        Complete the payment to secure your spot before the deadline below.
    </p>

    <div class="panel">
        <h2>Payment Deadline</h2>
        <p style="margin-bottom: 8px;">Complete payment before:</p>
        <div class="timer">{{ $deadlineDisplay }}</div>
        <p style="margin-top: 12px;">
            After this time your request will automatically expire and the seats will be released.
        </p>
    </div>

    <div class="panel">
        <h2>Request Summary</h2>
        <div class="detail-row">
            <span class="label">Request ID</span>
            <span class="value">#{{ $requestId }}</span>
        </div>
        <div class="detail-row">
            <span class="label">Primary Attendee</span>
            <span class="value">{{ $primaryName }}</span>
        </div>
        <div class="detail-row">
            <span class="label">Attendees</span>
            <span class="value">{{ $attendeeCount }}</span>
        </div>
        <div class="detail-row">
            <span class="label">Event Date</span>
            <span class="value">{{ $eventDate }}</span>
        </div>
    </div>

    <p style="margin-top: 24px;">
        You can review the full request details and complete payment using the button below.
    </p>

    <div class="cta-wrapper">
        <a href="{{ $actionUrl }}" class="cta">Pay Now</a>
    </div>

    <p style="margin-top: 24px; font-size: 14px; color: rgba(248, 249, 250, 0.7);">
        Need to make adjustments? You can edit the request while it is still awaiting payment.
    </p>
@endsection

@section('footer-link', $actionUrl)
