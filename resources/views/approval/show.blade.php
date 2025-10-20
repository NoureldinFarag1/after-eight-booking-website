@extends('layouts.app')

@section('title', 'Request #'.$eventRequest->id)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1">Request #{{ $eventRequest->id }}</h2>
            <div class="text-muted">Submitted {{ $eventRequest->created_at->diffForHumans() }}</div>
        </div>
    @php($badge = $eventRequest->status === 'approved' ? 'success' : ($eventRequest->status === 'declined' ? 'danger' : 'warning'))
        <span class="badge bg-{{ $badge }} px-3 py-2">{{ ucfirst($eventRequest->status) }}</span>
    </div>

    @include('event_requests.partials.request_core', ['eventRequest' => $eventRequest, 'showJson' => !empty($eventRequest->payload)])

    <div class="d-flex gap-2">
    <a href="{{ route('approval.index') }}" class="btn btn-outline-secondary"><i data-lucide="arrow-left"></i> Approvals</a>
        @if($eventRequest->status === 'pending')
            <form method="POST" action="{{ route('approval.approve', $eventRequest->id) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success"><i data-lucide="check"></i> Approve</button>
            </form>
            <form method="POST" action="{{ route('approval.reject', $eventRequest->id) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-danger"><i data-lucide="x"></i> Reject</button>
            </form>
        @endif
    </div>
</div>
@endsection
