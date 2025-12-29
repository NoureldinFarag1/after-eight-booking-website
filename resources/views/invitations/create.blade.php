@extends('layouts.app')

@section('title', 'Send Invitation')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Send Event Invitation</h1>
        <a href="{{ route('invitations.index') }}" class="btn btn-secondary">
            <i data-lucide="arrow-left" class="me-1"></i> Invitations
        </a>
    </div>

    @if($events->isEmpty())
        <div class="alert alert-warning">
            <i data-lucide="alert-triangle" class="me-2"></i>
            <strong>No events available!</strong> You need to create an event first before sending invitations.
            <a href="{{ route('admin.events.create') }}" class="btn btn-sm btn-warning ms-2">
                <i data-lucide="plus-circle" class="me-1"></i> Create Event
            </a>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="mail-plus" class="me-2"></i>
                    New Invitation Details
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('invitations.store') }}" method="POST">
                    @csrf

                    <!-- Event Select (Required) -->
                    <div class="mb-3">
                        <label for="event_id" class="form-label">
                            <strong>Select Event <span class="text-danger">*</span></strong>
                        </label>
                        <select name="event_id" id="event_id" class="form-select" required {{ $selectedEventId ? 'disabled' : '' }}>
                            <option value="">-- Choose an Event --</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" {{ (old('event_id', $selectedEventId ?? '') == $event->id) ? 'selected' : '' }}>
                                    {{ $event->title }} - {{ $event->event_date->format('M j, Y g:i A') }} at {{ $event->location }}
                                </option>
                            @endforeach
                        </select>
                        @if($selectedEventId)
                            <input type="hidden" name="event_id" value="{{ $selectedEventId }}">
                        @endif
                        @error('event_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        <div class="form-text">Select the event for which you're sending this invitation.</div>
                    </div>

                    <!-- Recipient Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            <strong>Recipient Name <span class="text-danger">*</span></strong>
                        </label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required
                               placeholder="Enter recipient's full name">
                        @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <!-- Recipient Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <strong>Recipient Email <span class="text-danger">*</span></strong>
                        </label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required
                               placeholder="Enter recipient's email address">
                        @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        <div class="form-text">The invitation with QR code will be sent to this email address.</div>
                    </div>

                    <!-- Personal Message -->
                    <div class="mb-4">
                        <label for="message" class="form-label">
                            <strong>Personal Message</strong> <small class="text-muted">(optional)</small>
                        </label>
                        <textarea class="form-control" id="message" name="message" rows="4"
                                  placeholder="Add a personal message to include with the invitation...">{{ old('message') }}</textarea>
                        @error('message')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        <div class="form-text">This message will be included in the invitation email along with the event details.</div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="send" class="me-1"></i> Send Invitation with QR Code
                        </button>
                        <a href="{{ route('invitations.index') }}" class="btn btn-secondary">
                            <i data-lucide="x-circle" class="me-1"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Info Card -->
        <div class="card mt-4 bg-light">
            <div class="card-body">
                <h6 class="card-title">
                    <i data-lucide="info" class="me-2"></i>How it works:
                </h6>
                <ul class="mb-0">
                    <li>Select an event and enter recipient details</li>
                    <li>A unique QR code will be generated for this invitation</li>
                    <li>The recipient will receive an email with event details and QR code attachment</li>
                    <li>They can use the QR code for event entry verification</li>
                </ul>
            </div>
        </div>
    @endif
</div>

<script>
// Show event details when event is selected
document.getElementById('event_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        // You can add logic here to show more event details if needed
        console.log('Event selected:', selectedOption.text);
    }
});
</script>
@endsection
