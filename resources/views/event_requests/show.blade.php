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
        @php($badge = $eventRequest->status === 'approved' ? 'success' : ($eventRequest->status === 'declined' ? 'danger' : 'warning'))
        <span class="badge bg-{{ $badge }} px-3 py-2">{{ ucfirst($eventRequest->status) }}</span>
    </div>

    @include('event_requests.partials.request_core', ['eventRequest' => $eventRequest, 'showJson' => auth()->check() && auth()->user()->role === \App\Enums\Role::ADMIN])

    <div class="d-flex gap-2">
        <a href="{{ route('admin.event_requests.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Requests</a>
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
    </div>
</div>
@endsection
