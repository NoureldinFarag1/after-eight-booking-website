@extends('layouts.app')

@section('title', 'Method Not Allowed')

@section('content')
@php
    $homeUrl = Route::has('home') ? route('home') : url('/');
    $supportEmail = config('mail.from.address');
@endphp
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="ae-card text-center">
                <div class="card-body p-5">
                    <div class="display-4 mb-3 text-warning"><i class="bi bi-exclamation-triangle-fill"></i></div>
                    <h1 class="h3 mb-3">Method Not Allowed</h1>
                    <p class="text-white-50 mb-4">{{ $message ?? 'The action you attempted is not allowed for this page.' }}</p>

                    @if(!empty($showLogoutHelper))
                        <div class="mb-3">
                            <p class="text-white-50">Looks like you tried to log out via a direct link. For your security, please confirm:</p>
                            <form id="logout-fix-form" method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary"><i class="bi bi-box-arrow-right me-1"></i>Log me out</button>
                            </form>
                        </div>
                        <script>
                            // Try to auto-submit the secure POST logout form if possible
                            (function(){
                                try{ document.getElementById('logout-fix-form')?.submit(); }catch(e){}
                            })();
                        </script>
                    @endif

                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mt-3">
                        <a href="{{ $homeUrl }}" class="btn btn-outline-primary">
                            <i class="bi bi-house-door me-1"></i> Back to Home
                        </a>
                        @guest
                            <a href="{{ route('login') }}" class="btn btn-primary"><i class="bi bi-box-arrow-in-right me-1"></i> Login</a>
                            <a href="{{ route('register') }}" class="btn btn-outline-primary"><i class="bi bi-person-plus me-1"></i> Register</a>
                        @endguest
                    </div>

                    @if($supportEmail)
                        <div class="small text-white-50 mt-3">Need help? Contact <a href="mailto:{{ $supportEmail }}" class="text-decoration-none">{{ $supportEmail }}</a>.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
