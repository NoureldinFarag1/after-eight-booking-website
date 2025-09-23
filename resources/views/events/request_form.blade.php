@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Request to attend: {{ $event->name }}</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('event-requests.store', $event) }}" method="POST">
        @csrf

        <div class="mb-4">
            <h5 class="mb-2">Primary attendee</h5>
            <p class="text-muted small mb-3">The primary attendee will receive up to 5 QR codes by email.</p>
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="primary_name" class="form-label">Full name</label>
                    <input type="text" name="primary_name" id="primary_name"
                           class="form-control @error('primary_name') is-invalid @enderror"
                           value="{{ old('primary_name') }}" required>
                    @error('primary_name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="primary_email" class="form-label">Email</label>
                    <input type="email" name="primary_email" id="primary_email"
                           class="form-control @error('primary_email') is-invalid @enderror"
                           value="{{ old('primary_email') }}" required>
                    @error('primary_email')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="primary_social_url" class="form-label">Instagram/Facebook URL</label>
                    <input type="url" name="primary_social_url" id="primary_social_url" required
                           class="form-control @error('primary_social_url') is-invalid @enderror"
                           value="{{ old('primary_social_url') }}" placeholder="https://instagram.com/username">
                    @error('primary_social_url')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mb-4">
            <h5 class="mb-2">Additional guests (up to 4)</h5>
            <div class="row g-3">
                @for($i = 0; $i < 4; $i++)
                    <div class="col-md-4">
                        <label class="form-label">Guest {{ $i+1 }} name</label>
                        <input type="text" name="guests[{{ $i }}][name]" class="form-control @error('guests.'.$i.'.name') is-invalid @enderror" value="{{ old('guests.'.$i.'.name') }}" placeholder="Optional">
                        @error('guests.'.$i.'.name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Guest {{ $i+1 }} Instagram/Facebook URL</label>
                        <input type="url" name="guests[{{ $i }}][social_url]" class="form-control @error('guests.'.$i.'.social_url') is-invalid @enderror" value="{{ old('guests.'.$i.'.social_url') }}" placeholder="https://instagram.com/guest">
                        @error('guests.'.$i.'.social_url')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                @endfor
            </div>
        </div>

        <button type="submit" class="btn btn-warning w-100">
            <i class="bi bi-send"></i> Submit Request
        </button>
    </form>
</div>
@endsection
