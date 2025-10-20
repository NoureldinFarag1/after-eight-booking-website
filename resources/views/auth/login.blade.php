@extends('layouts.app')
@section('title','Login')
@section('body_class','auth-body')
@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-title">
            <i data-lucide="log-in" class="text-primary" style="font-size:1.4rem;"></i>
            Login
        </div>
        <div class="auth-subtitle">Access your account to manage bookings and tickets.</div>

        <form method="POST" action="{{ route('login') }}" class="auth-form" novalidate>
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3 position-relative">
                <label for="password" class="form-label d-flex justify-content-between align-items-center">Password
                    <a href="{{ route('password.request') }}" class="small text-decoration-none" style="font-weight:600; color: var(--ae-primary);">Forgot?</a>
                </label>
                <div class="input-group password-toggle-group">
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="••••••••" required>
                    <button type="button" class="btn btn-outline-primary toggle-password" data-target="#password" aria-label="Show password"><i data-lucide="eye"></i></button>
                </div>
                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label for="remember" class="form-check-label small">Remember me</label>
            </div>
            <div class="auth-actions d-grid mb-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i data-lucide="log-in" class="me-1"></i>Sign In
                </button>
            </div>
        </form>

        <div class="auth-divider"><span>Or continue with</span></div>
        <div class="d-grid mb-2">
            <a href="{{ route('auth.google.redirect') }}" class="btn btn-outline-primary social-btn">
                <i data-lucide="mail" class="me-1"></i> Google
            </a>
        </div>

        <div class="mt-3">
            <div class="auth-footer m-0 p-0">Don't have an account? <a href="{{ route('register') }}">Register</a></div>
        </div>
    </div>
</div>
@endsection
