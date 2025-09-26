@extends('layouts.app')
@section('title','Register')
@section('body_class','auth-body')
@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-title">
            <i class="bi bi-person-plus text-primary" style="font-size:1.4rem;"></i>
            Create Account
        </div>
        <div class="auth-subtitle">Join After Eight to book events and manage your tickets.</div>

        <form method="POST" action="{{ route('register') }}" class="auth-form" novalidate>
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Jane Doe" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label d-flex justify-content-between">Phone <span class="text-muted small">Optional</span></label>
                <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="+1 555 123 4567">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group password-toggle-group">
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                        <button type="button" class="btn btn-outline-primary toggle-password" data-target="#password" aria-label="Show password"><i class="bi bi-eye"></i></button>
                    </div>
                    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <div class="input-group password-toggle-group">
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        <button type="button" class="btn btn-outline-primary toggle-password" data-target="#password_confirmation" aria-label="Show password"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
            <div class="auth-actions d-grid mb-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-person-plus me-1"></i>Create Account
                </button>
            </div>
        </form>

        <div class="auth-divider"><span>Or continue with</span></div>
        <div class="d-grid mb-2">
            <a href="{{ route('auth.google.redirect') }}" class="btn btn-outline-primary social-btn">
                <i class="bi bi-google me-1"></i> Google
            </a>
        </div>

        <div class="auth-footer mt-3">Already have an account? <a href="{{ route('login') }}">Login</a></div>
    </div>
</div>
@endsection
