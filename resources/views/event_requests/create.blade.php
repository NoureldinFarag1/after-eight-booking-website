@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">Submit Request for: {{ $event->title }}</h2>
    <a href="{{ route('events.show', $event->id) }}" class="btn btn-outline-secondary btn-sm mb-3"><i class="bi bi-arrow-left"></i> Back to Event</a>

    <form method="POST" action="{{ route('event-requests.store', $event->id) }}" id="createRequestForm">
        @csrf

        <div class="card mb-4">
            <div class="card-header fw-semibold">Primary Attendee</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Name</label>
                    <input type="text" name="primary_name" value="{{ old('primary_name') }}" class="form-control" required>
                    @error('primary_name')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="primary_email" value="{{ old('primary_email') }}" class="form-control" required>
                    @error('primary_email')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Social URL (Instagram/Facebook)</label>
                    <input type="url" name="primary_social_url" value="{{ old('primary_social_url') }}" class="form-control" required>
                    @error('primary_social_url')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ticket Type</label>
                    <select name="primary_ticket_type_id" class="form-select" required>
                        <option value="">Select type</option>
                        @foreach($ticketTypes as $tt)
                            <option value="{{ $tt->id }}" {{ (int)old('primary_ticket_type_id') === $tt->id ? 'selected' : '' }}>{{ $tt->name }} @if(!is_null($tt->price)) - EGP {{ number_format($tt->price,2) }} @endif</option>
                        @endforeach
                    </select>
                    @error('primary_ticket_type_id')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        @include('event_requests.partials._guests_form', ['context' => 'create'])

        <button type="submit" class="btn btn-primary"><i class="bi bi-envelope-plus me-1"></i> Submit Request</button>
    </form>
</div>

@endsection
