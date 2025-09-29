@php
    $guests = $eventRequest->guests ?? [];
    // Single query mapping for ticket type names (avoid per-row find calls)
    $ticketTypeIds = collect($guests)->pluck('ticket_type_id')->filter()->unique();
    $ticketTypeNames = $ticketTypeIds->isNotEmpty()
        ? \App\Models\TicketType::whereIn('id', $ticketTypeIds)->pluck('name','id')
        : collect();
@endphp

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Event</div>
                <div class="fw-semibold fs-5">{{ $eventRequest->event->title ?? $eventRequest->event->name ?? 'N/A' }}</div>
                <div class="mt-2 small text-muted">Attendees in this request:
                    <span class="badge bg-dark">{{ $eventRequest->attendee_count ?? (1 + (is_array($guests) ? count($guests) : 0)) }}</span>
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
                                <td>{{ isset($g['ticket_type_id']) ? ($ticketTypeNames[$g['ticket_type_id']] ?? '-') : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- @if(($showJson ?? false) && !empty($eventRequest->payload))
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Submitted JSON Snapshot</span>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#payloadJson" aria-expanded="false" aria-controls="payloadJson">
                Toggle
            </button>
        </div>
        <div id="payloadJson" class="collapse">
            <div class="card-body">
                <pre class="mb-0 small">{{ json_encode($eventRequest->payload, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    </div>
@endif -->
