@extends('layouts.app')

@section('title', 'Too Many Requests')

@section('content')
    @php
        $homeUrl = Route::has('home') ? route('home') : url('/');
        $supportEmail = config('mail.from.address');
        $retryAfterSeconds = isset($retryAfter) ? (int) $retryAfter : 60;
    @endphp
    <script>
        (function () {
            var seconds = @json($retryAfterSeconds);
            var display = null;
            function updateText() {
                if (!display) {
                    display = document.getElementById('retry-countdown');
                    if (!display) {
                        return;
                    }
                }
                if (seconds <= 0) {
                    display.textContent = 'You can safely try again now.';
                    return;
                }
                display.textContent = 'Please wait ' + seconds + ' second' + (seconds === 1 ? '' : 's') + ' before trying again.';
            }
            updateText();
            var timer = setInterval(function () {
                seconds--;
                updateText();
                if (seconds <= 0) {
                    clearInterval(timer);
                }
            }, 1000);
        })();
    </script>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-7">
                <div class="ae-card text-center p-5">
                    <div class="display-4 mb-3 text-warning"><i class="bi bi-speedometer"></i></div>
                    <h1 class="h2 mb-2">Too Many Requests</h1>
                    <p class="text-white-50 mb-4">Looks like things got a little busy. Take a brief pause and then try again.</p>
                    <div id="retry-countdown" class="small text-white-50 mb-3" aria-live="polite"></div>
                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mb-3">
                        <button type="button" class="btn btn-outline-primary" onclick="window.location.reload()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Try Again
                        </button>
                        <a href="{{ $homeUrl }}" class="btn btn-primary">
                            <i class="bi bi-house-door me-1"></i>Back to Home
                        </a>
                    </div>
                    <div class="small text-white-50">
                        Still having trouble?
                        @if ($supportEmail)
                            Contact <a href="mailto:{{ $supportEmail }}" class="text-decoration-none">{{ $supportEmail }}</a>
                        @else
                            Reach out to support
                        @endif
                        so we can help.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
