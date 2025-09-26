@extends('layouts.app')

@section('title', 'Access Denied')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card text-center">
                <div class="card-body p-5">
                    <div class="display-4 mb-3 text-danger"><i class="bi bi-shield-lock"></i></div>
                    <h1 class="h3 mb-3">Access Denied</h1>
                    <p class="text-muted mb-4">{{ $message ?? 'You do not have permission to perform this action.' }}</p>
                    @auth
                        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('home') }}" class="btn btn-primary me-2">
                            <i class="bi bi-arrow-left me-1"></i>Go Back
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary me-2"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary"><i class="bi bi-person-plus me-1"></i>Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
