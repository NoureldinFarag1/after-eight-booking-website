@extends('layouts.app')

@section('title', 'Send Invitation')

@section('content')
<div class="container">
    <h1 class="mb-4">Send Invitation</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('invitations.store') }}" method="POST">
                @csrf

                <!-- Name -->
                <div class="mb-3">
                    <label for="name" class="form-label">Recipient Name</label>
                    <input type="text" name="name" id="name" 
                           class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">Recipient Email</label>
                    <input type="email" name="email" id="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Message -->
                <div class="mb-3">
                    <label for="message" class="form-label">Message (optional)</label>
                    <textarea name="message" id="message" rows="4" 
                              class="form-control @error('message') is-invalid @enderror">{{ old('message') }}</textarea>
                    @error('message')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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