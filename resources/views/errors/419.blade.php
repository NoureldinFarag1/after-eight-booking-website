@extends('layouts.app')

@section('title', 'Page Expired')

@section('content')
    @php
        $homeUrl = Route::has('home') ? route('home') : url('/');
    @endphp
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-7">
                <div class="ae-card text-center p-5">
                    <div class="display-4 mb-3 text-warning"><i class="bi bi-hourglass-split"></i></div>
                    <h1 class="h2 mb-2">Session Expired</h1>
                    <p class="text-white-50 mb-4">Refresh the page to try again, or head back to the homepage to continue browsing.</p>
                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mb-3">
                        <form method="GET" action="{{ url()->current() }}">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-arrow-clockwise me-1"></i>Refresh Page</button>
                        </form>
                        @auth
                            <a href="{{ $homeUrl }}" class="btn btn-outline-primary"><i class="bi bi-house-door me-1"></i>Back to Home</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline-primary"><i class="bi bi-box-arrow-in-right me-1"></i>Login Again</a>
                        @endauth
                    </div>
                    <div class="small text-white-50">Lost progress? After logging back in, revisit the previous page and re-submit your form.</div>
                </div>
            </div>
        </div>
    </div>
@endsection
