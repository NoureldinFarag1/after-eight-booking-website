@extends('layouts.app')

@section('title', 'Edit Request #'.$eventRequest->id)

@section('content')
<div class="container">
    <h2 class="mb-3">Edit Your Event Request</h2>
    <div class="mb-3">
        <a href="{{ route('event_requests.show', $eventRequest->id) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Request</a>
    </div>

    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header fw-semibold">Event</div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <div class="fw-semibold">{{ $event->title }}</div>
                    <div class="text-muted small">{{ $event->event_date->format('M j, Y') }} • {{ $event->event_time->format('H:i') }}</div>
                </div>
                <a href="{{ route('events.show', $event->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-up-right"></i> View Event</a>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('event_requests.update', $eventRequest->id) }}" id="editRequestForm">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-header fw-semibold">Primary Attendee</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Name</label>
                    <input type="text" name="primary_name" value="{{ old('primary_name', $eventRequest->primary_name) }}" class="form-control" required>
                    @error('primary_name')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="primary_email" value="{{ old('primary_email', $eventRequest->primary_email) }}" class="form-control" required>
                    @error('primary_email')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Social URL (Instagram/Facebook)</label>
                    <input type="url" name="primary_social_url" value="{{ old('primary_social_url', $eventRequest->primary_social_url) }}" class="form-control" required>
                    @error('primary_social_url')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ticket Type</label>
                    <select name="primary_ticket_type_id" class="form-select" required>
                        <option value="">Select type</option>
                        @foreach($ticketTypes as $tt)
                            <option value="{{ $tt->id }}" {{ (int)old('primary_ticket_type_id', $eventRequest->primary_ticket_type_id) === $tt->id ? 'selected' : '' }}>{{ $tt->name }} @if(!is_null($tt->price)) - ${{ number_format($tt->price,2) }} @endif</option>
                        @endforeach
                    </select>
                    @error('primary_ticket_type_id')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        @include('event_requests.partials._guests_form', ['context' => 'edit', 'guestsData' => $eventRequest->guests])

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Save Changes</button>
            <a href="{{ route('event_requests.show', $eventRequest->id) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
    (function(){
        const addBtn = document.getElementById('addGuestBtn');
        const container = document.getElementById('guestsContainer');
        const template = document.getElementById('guestTemplate').innerHTML;

        function currentCount(){
            return container.querySelectorAll('.guest-item').length;
        }
        function nextIndex(){
                                        <label class="form-label small mb-1">Email</label>
