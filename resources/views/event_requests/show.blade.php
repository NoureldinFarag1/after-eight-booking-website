@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1">Request #{{ $eventRequest->id }}</h2>
            <div class="text-muted">Submitted {{ $eventRequest->created_at->diffForHumans() }}</div>
            @php
                $statusEnum = \App\Enums\EventRequestStatus::tryFrom($eventRequest->status);
                $statusLabel = $statusEnum ? $statusEnum->label() : ucfirst(str_replace('_', ' ', $eventRequest->status));
                $badgeClass = match($statusEnum) {
                    \App\Enums\EventRequestStatus::PENDING => 'bg-warning text-dark',
                    \App\Enums\EventRequestStatus::AWAITING_PAYMENT => 'bg-info text-dark',
                    \App\Enums\EventRequestStatus::PAID => 'bg-success',
                    \App\Enums\EventRequestStatus::DECLINED => 'bg-danger',
                    \App\Enums\EventRequestStatus::EXPIRED => 'bg-secondary',
                    \App\Enums\EventRequestStatus::APPROVED => 'bg-success',
                    default => 'bg-secondary',
                };
                $decisionMeta = match($statusEnum) {
                    \App\Enums\EventRequestStatus::DECLINED => ['icon' => 'x-circle', 'class' => 'text-danger', 'label' => 'Declined'],
                    \App\Enums\EventRequestStatus::AWAITING_PAYMENT, \App\Enums\EventRequestStatus::PAID, \App\Enums\EventRequestStatus::APPROVED => ['icon' => 'check-circle-2', 'class' => 'text-success', 'label' => 'Approved'],
                    default => null,
                };
            @endphp
            @if($decisionMeta && $eventRequest->admin)
                <div class="text-muted small mt-1 d-flex align-items-center gap-1">
                    <i data-lucide="{{ $decisionMeta['icon'] }}" class="{{ $decisionMeta['class'] }}" style="width:14px;height:14px;"></i>
                    <span>{{ $decisionMeta['label'] }} by {{ $eventRequest->admin->name }} · <span title="{{ $eventRequest->updated_at }}">{{ $eventRequest->updated_at->diffForHumans() }}</span></span>
                </div>
            @endif
            @if($statusEnum === \App\Enums\EventRequestStatus::EXPIRED && $eventRequest->expires_at)
                <div class="text-muted small mt-1">Expired {{ $eventRequest->expires_at->diffForHumans() }}.</div>
            @endif
        </div>
        <span class="badge {{ $badgeClass }} px-3 py-2">{{ $statusLabel }}</span>
    </div>

    @include('event_requests.partials.request_core', ['eventRequest' => $eventRequest, 'showJson' => auth()->check() && auth()->user()->role === \App\Enums\Role::ADMIN])

    @php
        $isAwaiting = $statusEnum === \App\Enums\EventRequestStatus::AWAITING_PAYMENT;
        $hasExpiry = !is_null($eventRequest->expires_at);
        $isExpired = $hasExpiry ? $eventRequest->expires_at->isPast() : false;
        $expiresEpochMs = $hasExpiry ? ($eventRequest->expires_at->timezone('UTC')->timestamp * 1000) : null;
        $authUser = auth()->user();
        $isOwner = $authUser && $authUser->id === $eventRequest->user_id;
        $isAdminOrApproval = $authUser && in_array($authUser->role, [\App\Enums\Role::ADMIN, \App\Enums\Role::APPROVAL_OFFICER], true);
    @endphp

    @if($isAwaiting)
        @if($hasExpiry)
            @if($isExpired)
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i data-lucide="alert-triangle" class="me-2" style="width:18px;height:18px;"></i>
                    <div>
                        This request has expired and can no longer be paid. Please submit a new request.
                    </div>
                </div>
            @else
                @if($isOwner)
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i data-lucide="hourglass" class="me-2" style="width:18px;height:18px;"></i>
                        <div>
                            Please complete the payment within
                            <strong id="paymentCountdown">--:--:--</strong>.
                            Expires at {{ $eventRequest->expires_at->format('D M j, g:i A') }}.
                        </div>
                    </div>
                @elseif($isAdminOrApproval)
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i data-lucide="timer" class="me-2" style="width:18px;height:18px;"></i>
                        <div>
                            Payment window closes in <strong id="paymentCountdown">--:--:--</strong>. Expires at {{ $eventRequest->expires_at->format('D M j, g:i A') }}.
                        </div>
                    </div>
                @endif
            @endif
        @else
            @if($isOwner)
                <div class="alert alert-info d-flex align-items-center" role="alert">
                    <i data-lucide="info" class="me-2" style="width:18px;height:18px;"></i>
                    <div>Please complete the payment as soon as possible to secure your spot.</div>
                </div>
            @elseif($isAdminOrApproval)
                <div class="alert alert-secondary d-flex align-items-center" role="alert">
                    <i data-lucide="alert-circle" class="me-2" style="width:18px;height:18px;"></i>
                    <div>Awaiting payment. No expiration timestamp has been set for this request.</div>
                </div>
            @endif
        @endif
    @endif

    <div class="d-flex gap-2">
        @if(auth()->check() && auth()->id() === $eventRequest->user_id)
            <a href="{{ route('event_requests.index') }}" class="btn btn-secondary">Requests</a>
        @endif
        @if(auth()->check() && in_array(auth()->user()->role, [\App\Enums\Role::ADMIN, \App\Enums\Role::APPROVAL_OFFICER], true) && $statusEnum === \App\Enums\EventRequestStatus::PENDING)
            <form method="POST" action="{{ route('admin.event_requests.approve', $eventRequest->id) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-1">
                    <i data-lucide="check" style="width:16px;height:16px;"></i>
                    <span>Approve</span>
                </button>
            </form>
            <form method="POST" action="{{ route('admin.event_requests.decline', $eventRequest->id) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-danger d-inline-flex align-items-center gap-1">
                    <i data-lucide="x" style="width:16px;height:16px;"></i>
                    <span>Decline</span>
                </button>
            </form>
        @endif

    @if(auth()->check() && auth()->id() === $eventRequest->user_id && $statusEnum === \App\Enums\EventRequestStatus::AWAITING_PAYMENT && !$isExpired)
            <form method="POST" action="{{ route('event_requests.pay', $eventRequest->id) }}" class="d-inline" id="payForm">
                @csrf
                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1" id="payNowBtn">
                    <i data-lucide="credit-card" style="width:16px;height:16px;"></i>
                    <span>Pay Now</span>
                </button>
            </form>
        @endif
        @if($statusEnum === \App\Enums\EventRequestStatus::EXPIRED && auth()->check() && auth()->id() === $eventRequest->user_id)
            <form method="POST" action="{{ route('event_requests.remake', $eventRequest->id) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-primary d-inline-flex align-items-center gap-1">
                    <i data-lucide="refresh-ccw" style="width:16px;height:16px;"></i>
                    <span>Remake Request</span>
                </button>
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
