@extends('layouts.app')
@section('title','Forgot Password')
@section('body_class','auth-body')
@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-title">
            <i class="bi bi-unlock text-primary" style="font-size:1.4rem;"></i>
            Reset Password
        </div>
        <div class="auth-subtitle">Enter your account email and we'll send you a reset link.</div>
        @if(session('success'))
            <div class="alert alert-success py-2 mb-3 small">{{ session('success') }}</div>
        @endif
        <form method="POST" action="{{ route('password.email') }}" class="auth-form" novalidate>
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="you@example.com" required autofocus>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="auth-actions d-grid mb-2">
                <button class="btn btn-primary" type="submit"><i class="bi bi-arrow-repeat me-1"></i>Send Reset Link</button>
            </div>
        </form>
        <div class="auth-footer">Return to <a href="{{ route('login') }}">Login</a></div>
    </div>
</div>
@endsection
