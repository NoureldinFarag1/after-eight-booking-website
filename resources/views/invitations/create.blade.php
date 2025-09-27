@extends('layouts.app')

@section('title', 'Send Invitation')

@section('content')
<div class="container">
    <h1 class="mb-4">Send Invitation</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('invitations.store') }}" method="POST">
                @csrf

                <!-- Event Select -->
                <div class="mb-3">
                    <label for="event_id" class="form-label">Event (optional)</label>
                    <select name="event_id" id="event_id" class="form-select">
                        <option value="">-- Select an Event (optional) --</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" {{ (old('event_id', $selectedEventId ?? '') == $event->id) ? 'selected' : '' }}>
                                {{ $event->title }} - {{ $event->event_date->format('M j, Y') }}
                            </option>
                        @endforeach
                    </select>
                    @error('event_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <!-- Name -->
                <div class="mb-3">
                    <label for="name" class="form-label">Recipient Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">Recipient Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <!-- Message -->
                <div class="mb-3">
                    <label for="message" class="form-label">Message (optional)</label>
                    <textarea class="form-control" id="message" name="message" rows="4">{{ old('message') }}</textarea>
                    @error('message')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <!-- Submit -->
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send me-1"></i>Send Invitation
                </button>
                <a href="{{ route('invitations.index') }}" class="btn btn-secondary ms-2">
                    Cancel
                </a>
            </form>
        </div>
    </div>
</div>
@endsection
