@extends('layouts.app')

@section('title', 'Requests')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Event Requests</h5>
    </div>
    <div class="card-body">
        @if($requests->isEmpty())
            <p class="text-muted mb-0">No requests yet.</p>
        @else
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Submitted At</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                        <tr>
                            <td class="text-muted">{{ $req->event->title ?? 'Event Deleted' }}</td>
                            <td class="text-muted">{{ $req->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                @php
                                    $statusEnum = \App\Enums\EventRequestStatus::tryFrom($req->status);
                                    $badgeClass = match($statusEnum) {
                                        \App\Enums\EventRequestStatus::PENDING => 'bg-warning text-dark',
                                        \App\Enums\EventRequestStatus::AWAITING_PAYMENT => 'bg-info text-dark',
                                        \App\Enums\EventRequestStatus::PAID => 'bg-success',
                                        \App\Enums\EventRequestStatus::DECLINED => 'bg-danger',
                                        \App\Enums\EventRequestStatus::EXPIRED => 'bg-secondary',
                                        default => 'bg-secondary',
                                    };
                                    $statusLabel = $statusEnum ? $statusEnum->label() : ucfirst(str_replace('_', ' ', $req->status));
                                @endphp
                                <div class="d-flex flex-column">
                                    <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                                    @if($statusEnum === \App\Enums\EventRequestStatus::AWAITING_PAYMENT && $req->expires_at)
                                        <span class="small text-muted">Expires {{ $req->expires_at->diffForHumans() }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('event_requests.show', $req->id) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                @if($statusEnum === \App\Enums\EventRequestStatus::PENDING)
                                    <a href="{{ route('event_requests.edit', $req->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
