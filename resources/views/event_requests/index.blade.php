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
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                        <tr>
                            <td class="text-muted">
                                @php $eventTitle = $req->event->title ?? 'Event Deleted'; @endphp
                                <a href="{{ route('event_requests.show', $req->id) }}" class="text-decoration-none">
                                    {{ $eventTitle }}
                                </a>
                            </td>
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
                                <div class="d-flex flex-column gap-1">
                                    <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                                    @if($statusEnum === \App\Enums\EventRequestStatus::AWAITING_PAYMENT && $req->expires_at)
                                        <span class="small text-muted">Expires {{ $req->expires_at->diffForHumans() }}</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
