@extends('layouts.app')

@section('title', 'Page Not Found')

@section('content')
    @php
        $homeUrl = Route::has('home') ? route('home') : url('/');
    @endphp
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-7">
                <div class="ae-card text-center p-5">
                    <div class="display-4 mb-3 text-warning"><i class="bi bi-compass"></i></div>
                    <h1 class="h2 mb-2">Page Not Found</h1>
                    <p class="text-white-50 mb-4">The page you're looking for might have been removed, renamed, or is temporarily unavailable.</p>
                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mb-4">
                        <button type="button" class="btn btn-outline-primary" onclick="window.history.length > 1 ? history.back() : window.location.assign('{{ $homeUrl }}')">
                            <i class="bi bi-arrow-left me-1"></i>Previous Page
                        </button>
                        <a href="{{ $homeUrl }}" class="btn btn-primary">
                            <i class="bi bi-house-door me-1"></i>Back to Home
                        </a>
                    </div>
                    <div class="small text-white-50">If you believe this is an error, please reach out to support so we can help you find the right spot.</div>
                </div>
            </div>
        </div>
    </div>
@endsection
