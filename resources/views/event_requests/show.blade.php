@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1">Request #{{ $eventRequest->id }}</h2>
            <div class="text-muted">Submitted {{ $eventRequest->created_at->diffForHumans() }}</div>
            @if($eventRequest->status !== 'pending' && $eventRequest->admin)
                <div class="text-muted small mt-1">
                    @if($eventRequest->status === 'approved')
                        <i class="bi bi-check-circle-fill text-success"></i>
                    @else
                        <i class="bi bi-x-circle-fill text-danger"></i>
                    @endif
                    Decided by {{ $eventRequest->admin->name }}
                    <span title="{{ $eventRequest->updated_at }}">{{ $eventRequest->updated_at->diffForHumans() }}</span>
                </div>
            @endif
        </div>
        @php
            $badge = match($eventRequest->status){
                'approved' => 'success',
                'declined' => 'danger',
                'awaiting_payment' => 'info',
                'paid' => 'success',
                'expired' => 'secondary',
                default => 'warning'
            };
        @endphp
        <span class="badge bg-{{ $badge }} px-3 py-2">{{ str_replace('_',' ',ucfirst($eventRequest->status)) }}</span>
    </div>

    @include('event_requests.partials.request_core', ['eventRequest' => $eventRequest, 'showJson' => auth()->check() && auth()->user()->role === \App\Enums\Role::ADMIN])

    @php
        $isAwaiting = $eventRequest->status === 'awaiting_payment';
        $hasExpiry = !is_null($eventRequest->expires_at);
        $isExpired = $hasExpiry ? $eventRequest->expires_at->isPast() : false;
        $expiresEpochMs = $hasExpiry ? ($eventRequest->expires_at->timezone('UTC')->timestamp * 1000) : null;
    @endphp

    @if($isAwaiting)
        @if($hasExpiry)
            @if($isExpired)
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div>
                        This request has expired and can no longer be paid. Please submit a new request.
                    </div>
                </div>
            @else
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <i class="bi bi-stopwatch me-2"></i>
                    <div>
                        Please complete the payment within
                        <strong id="paymentCountdown">--:--:--</strong>.
                        Expires at {{ $eventRequest->expires_at->format('D M j, g:i A') }}.
                    </div>
                </div>
            @endif
        @else
            <div class="alert alert-info" role="alert">
                Please complete the payment as soon as possible to secure your spot.
            </div>
        @endif
    @endif

    <div class="d-flex gap-2">
        @if(auth()->check() && auth()->id() === $eventRequest->user_id)
            <a href="{{ route('event_requests.index') }}" class="btn btn-secondary">Requests</a>
        @endif
        @if(auth()->check() && auth()->user()->role === \App\Enums\Role::ADMIN && $eventRequest->status === 'pending')
            <form method="POST" action="{{ route('admin.event_requests.approve', $eventRequest->id) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success"><i class="bi bi-check2"></i> Approve</button>
            </form>
            <form method="POST" action="{{ route('admin.event_requests.decline', $eventRequest->id) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-danger"><i class="bi bi-x"></i> Decline</button>
            </form>
        @endif

        @if(auth()->check() && auth()->id() === $eventRequest->user_id && $eventRequest->status === 'awaiting_payment' && !($isExpired ?? false))
            <form method="POST" action="{{ route('event_requests.pay', $eventRequest->id) }}" class="d-inline" id="payForm">
                @csrf
                <button type="submit" class="btn btn-primary" id="payNowBtn"><i class="bi bi-credit-card"></i> Pay Now</button>
            </form>
        @endif
    </div>

    @if($isAwaiting && $hasExpiry && !$isExpired)
        <script>
        (function(){
            const expiryMs = {{ $expiresEpochMs }};
            const $count = document.getElementById('paymentCountdown');
            const $payBtn = document.getElementById('payNowBtn');
            if(!expiryMs || !$count) return;

            function format(ms){
                if(ms <= 0) return '00:00:00';
                let s = Math.floor(ms/1000);
                const h = Math.floor(s/3600); s%=3600;
                const m = Math.floor(s/60); s%=60;
                const pad = (n)=> String(n).padStart(2,'0');
                return `${pad(h)}:${pad(m)}:${pad(s)}`;
            }

            function tick(){
                const now = Date.now();
                const remaining = expiryMs - now;
                if(remaining <= 0){
                    $count.textContent = '00:00:00';
                    if($payBtn){ $payBtn.disabled = true; $payBtn.classList.remove('btn-primary'); $payBtn.classList.add('btn-outline-secondary'); }
                    clearInterval(timer);
                    return;
                }
                $count.textContent = format(remaining);
            }
            const timer = setInterval(tick, 1000);
            tick();
        })();
        </script>
    @endif
</div>
@endsection
