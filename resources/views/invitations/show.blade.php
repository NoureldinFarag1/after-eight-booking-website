@extends('layouts.app')

@section('title', 'Invitation Details')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Invitation Details</h1>
        <a href="{{ route('invitations.index') }}" class="btn btn-secondary">
            <i data-lucide="arrow-left" class="me-1"></i> Invitations
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Invitation Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Recipient Name:</strong><br>
                            {{ $invitation->name }}
                        </div>
                        <div class="col-md-6">
                            <strong>Email:</strong><br>
                            {{ $invitation->email }}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Status:</strong><br>
                            @if($invitation->status === 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($invitation->status === 'sent')
                                <span class="badge bg-primary">Sent</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>QR Status:</strong><br>
                            @if($invitation->qr_status === 'valid')
                                <span class="badge bg-success">Valid</span>
                            @elseif($invitation->qr_status === 'used')
                                <span class="badge bg-secondary">Used</span>
                            @elseif($invitation->qr_status === 'cancelled')
                                <span class="badge bg-danger">Cancelled</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Sent by:</strong><br>
                            {{ $invitation->sender->name ?? 'Unknown' }}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Created:</strong><br>
                            {{ $invitation->created_at->format('F j, Y g:i A') }}
                        </div>
                        <div class="col-md-6">
                            <strong>Updated:</strong><br>
                            {{ $invitation->updated_at->format('F j, Y g:i A') }}
                        </div>
                    </div>

                    @if($invitation->message)
                        <hr>
                        <div>
                            <strong>Personal Message:</strong><br>
                            <div class="bg-light p-3 rounded">
                                {{ $invitation->message }}
                            </div>
                        </div>
                    @endif

                    <hr>
                    <div>
                        <strong>Event Details:</strong><br>
                        @if($invitation->event)
                            <div class="bg-light p-3 rounded">
                                <h6>{{ $invitation->event->title }}</h6>
                                <p class="mb-1"><strong>Date:</strong> {{ $invitation->event->event_date->format('F j, Y') }}</p>
                                <p class="mb-1"><strong>Time:</strong> {{ $invitation->event->event_time->format('g:i A') }}</p>
                                <p class="mb-0"><strong>Location:</strong> {{ $invitation->event->location }}</p>
                        @else
                            <div class="bg-warning p-3 rounded">
                                <p class="mb-0 text-dark">No event associated with this invitation.</p>
                        @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            @if($invitation->qr_code_path)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">QR Code</h5>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ asset('storage/' . $invitation->qr_code_path) }}"
                             alt="Invitation QR Code"
                             class="img-fluid"
                             style="max-width: 200px;">
                        <div class="mt-3">
                            <small class="text-muted">
                                Scan this QR code to accept the invitation
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0">QR Code Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('invitations.verify', ['invitation' => $invitation->id, 'code' => $invitation->qr_code]) }}"
                               class="btn btn-primary btn-sm"
                               target="_blank">
                                <i data-lucide="qr-code" class="me-1"></i> Test Verification URL
                            </a>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            This is the URL that the QR code points to for verification.
                        </small>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center">
                        <i data-lucide="triangle-alert" class="text-warning" style="width:2rem;height:2rem;"></i>
                        <p class="mt-2 mb-0">No QR code generated for this invitation.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
