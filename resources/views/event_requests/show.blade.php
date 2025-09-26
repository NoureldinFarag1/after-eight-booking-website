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

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Event</div>
                    <div class="fw-semibold fs-5">{{ $eventRequest->event->title ?? $eventRequest->event->name ?? 'N/A' }}</div>
                    <div class="mt-2 small text-muted">Attendees in this request:
                        <span class="badge bg-dark">{{ $eventRequest->attendee_count ?? (1 + (is_array($eventRequest->guests) ? count($eventRequest->guests) : 0)) }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Submitted by</div>
                    <div class="fw-semibold">{{ $eventRequest->user->name ?? 'N/A' }}</div>
                    <div class="text-muted small">{{ $eventRequest->user->email ?? '' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header fw-semibold">Primary Attendee</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="text-muted small">Name</div>
                    <div class="fw-semibold">{{ $eventRequest->primary_name ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Email</div>
                    <div class="fw-semibold">{{ $eventRequest->primary_email ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Social</div>
                    @if(!empty($eventRequest->primary_social_url))
                        <a href="{{ $eventRequest->primary_social_url }}" target="_blank" rel="noopener" class="fw-semibold text-decoration-none">
                            {{ $eventRequest->primary_social_url }}
                        </a>
                    @else
                        <div class="fw-semibold">-</div>
                    @endif
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Ticket Type</div>
                    <div class="fw-semibold">{{ optional($eventRequest->primaryTicketType)->name ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header fw-semibold">Guests</div>
        <div class="card-body">
            @php($guests = $eventRequest->guests ?? [])
            @if(empty($guests))
                <div class="text-muted">No additional guests.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr><th>#</th><th>Name</th><th>Email</th><th>Social</th><th>Ticket Type</th></tr>
                        </thead>
                        <tbody>
                            @foreach($guests as $idx => $g)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ $g['name'] ?? '-' }}</td>
                                    <td>{{ $g['email'] ?? '-' }}</td>
                                    <td>
                                        @if(!empty($g['social_url']))
                                            <a href="{{ $g['social_url'] }}" target="_blank" rel="noopener">{{ $g['social_url'] }}</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @php($gt = isset($g['ticket_type_id']) ? \App\Models\TicketType::find($g['ticket_type_id']) : null)
                                        {{ $gt->name ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if(auth()->check() && auth()->user()->role === \App\Enums\Role::ADMIN && !empty($eventRequest->payload))
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Submitted JSON Snapshot</span>
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#payloadJson" aria-expanded="false" aria-controls="payloadJson">
                    Toggle
                </button>
            </div>
            <div id="payloadJson" class="collapse">
                <div class="card-body">
                    <pre class="mb-0">{{ json_encode($eventRequest->payload, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        </div>
    @endif

    <div class="d-flex gap-2">
        <a href="{{ route('admin.event_requests.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to list</a>
        @if(auth()->check() && auth()->id() === $eventRequest->user_id)
            <a href="{{ route('event_requests.index') }}" class="btn btn-secondary">My Requests</a>
        @endif
    </div>
</div>
@endsection
