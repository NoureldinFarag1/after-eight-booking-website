@extends('layouts.app')
@section('title','Set New Password')
@section('body_class','auth-body')
@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-title">
            <i class="bi bi-shield-lock text-primary" style="font-size:1.4rem;"></i>
            New Password
        </div>
        <div class="auth-subtitle">Choose a strong password for your account.</div>
        <form method="POST" action="{{ route('password.store') }}" class="auth-form" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email',$email) }}" class="form-control @error('email') is-invalid @enderror" required readonly>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3 position-relative">
                <label for="password" class="form-label">Password</label>
                <div class="input-group password-toggle-group">
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" required>
                    <button type="button" class="btn btn-outline-primary toggle-password" data-target="#password" aria-label="Show password"><i class="bi bi-eye"></i></button>
                </div>
                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3 position-relative">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <div class="input-group password-toggle-group">
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="••••••••" required>
                    <button type="button" class="btn btn-outline-primary toggle-password" data-target="#password_confirmation" aria-label="Show password"><i class="bi bi-eye"></i></button>
                </div>
            </div>
            <div class="auth-actions d-grid mb-2">
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle me-1"></i>Reset Password</button>
            </div>
        </form>
        <div class="auth-footer">Back to <a href="{{ route('login') }}">Login</a></div>
    </div>
</div>
@endsection
