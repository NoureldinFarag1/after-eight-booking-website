@extends('layouts.app')

@section('title', 'Access Denied')

@section('content')
@php
    $homeUrl = Route::has('home') ? route('home') : url('/');
    $supportEmail = config('mail.from.address');
@endphp
<script>
    // Redirect to home after a brief delay so users can read the message
    (function(){
        var target = @json($homeUrl);
        setTimeout(function(){
            try { window.location.replace(target); } catch (e) { window.location.href = target; }
        }, 2000);
    })();
</script>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="ae-card text-center">
                <div class="card-body p-5">
                    <div class="display-4 mb-3 text-danger"><i class="bi bi-shield-lock-fill"></i></div>
                    <h1 class="h3 mb-3">Access Denied</h1>
                    <p class="text-white-50 mb-4">{{ $message ?? 'You do not have permission to perform this action or view this page.' }}</p>
                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mb-3">
                        @auth
                            <a href="{{ $homeUrl }}" class="btn btn-primary">
                                <i class="bi bi-house-door me-1"></i>Back to Home
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a>
                            <a href="{{ route('register') }}" class="btn btn-outline-primary"><i class="bi bi-person-plus me-1"></i>Register</a>
                        @endauth
                    </div>
                    <div class="small text-white-50" aria-live="polite">You will be redirected to the homepage in about 2 seconds…</div>
                    <noscript>
                        <div class="alert alert-info mt-3" role="alert">
                            Redirecting… If you are not redirected automatically, <a href="{{ $homeUrl }}" class="alert-link">click here to go home</a>.
                        </div>
                    </noscript>
                    @if($supportEmail)
                        <div class="small text-white-50">Need help? Contact <a href="mailto:{{ $supportEmail }}" class="text-decoration-none">{{ $supportEmail }}</a>.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
