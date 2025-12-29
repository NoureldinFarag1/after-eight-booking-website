@extends('layouts.email')

@section('title', 'Reset Your Password')

@section('content')
    <h1>Password Reset Request</h1>
    <p>
        You are receiving this email because we received a password reset request for your account.
    </p>

    <p>
        This password reset link will expire in {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} minutes.
    </p>

    <div class="cta-wrapper">
        <a href="{{ $url }}" class="cta">Reset Password</a>
    </div>

    <p style="margin-top: 24px;">
        If you did not request a password reset, no further action is required.
    </p>
@endsection

@section('footer-link', $url)
