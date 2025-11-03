@extends('layouts.app')

@section('content')
@php
    $statusOptions = [
        \App\Enums\EventRequestStatus::PENDING->value => \App\Enums\EventRequestStatus::PENDING->label(),
        \App\Enums\EventRequestStatus::AWAITING_PAYMENT->value => \App\Enums\EventRequestStatus::AWAITING_PAYMENT->label(),
        \App\Enums\EventRequestStatus::PAID->value => \App\Enums\EventRequestStatus::PAID->label(),
        \App\Enums\EventRequestStatus::DECLINED->value => \App\Enums\EventRequestStatus::DECLINED->label(),
        \App\Enums\EventRequestStatus::EXPIRED->value => \App\Enums\EventRequestStatus::EXPIRED->label(),
        \App\Enums\EventRequestStatus::APPROVED->value => \App\Enums\EventRequestStatus::APPROVED->label(), // legacy support
    ];
@endphp
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Event Requests</h2>
        <form method="GET" action="{{ route('admin.event_requests.index') }}" class="d-flex gap-2 align-items-end">
            <div>
                <label for="q" class="form-label mb-0 small text-muted">Search</label>
                <input type="text" name="q" id="q" class="form-control" value="{{ $q ?? '' }}" placeholder="User, event, primary name/email">
            </div>
            <div>
                <label for="status" class="form-label mb-0 small text-muted">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="" {{ empty($status) ? 'selected' : '' }}>All</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" {{ ($status ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary d-inline-flex align-items-center gap-1" type="submit">
                    <i data-lucide="filter" class="me-1" style="width:16px;height:16px;"></i>
                    <span>Filter</span>
                </button>
                <a href="{{ route('admin.event_requests.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                    <i data-lucide="x-circle" class="me-1" style="width:16px;height:16px;"></i>
                    <span>Reset</span>
                </a>
            </div>
        </form>
    </div>

    @if($requests->count() === 0)
        <div class="text-center text-muted py-5">
            <div class="mb-2">
                <i data-lucide="inbox" class="text-muted" style="width:2rem;height:2rem;"></i>
            </div>
            <div>No requests found.</div>
        </div>
    @else
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-black">#</th>
                            <th class="text-black">User</th>
                            <th class="text-black">Event</th>
                            <th class="text-black">Primary</th>
                            <th class="text-center text-black">Attendees</th>
                            <th class="text-black">Tickets Mix</th>
                            <th class="text-black">Status</th>
                            <th class="text-black">Submitted</th>
                            <th class="text-end text-black">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $r)
                            <tr>
                                <td class="text-muted">{{ $r->id }}</td>
                                <td>
                                    <div class="fw-semibold text-muted">{{ $r->user->name ?? 'N/A' }}</div>
                                    <div class="text-muted small">{{ $r->user->email ?? '' }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-muted">{{ $r->event->title ?? $r->event->name ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-muted">{{ $r->primary_name ?? '-' }}</div>
                                    <div class="text-muted small">{{ $r->primary_email ?? '' }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-dark">{{ $r->attendee_count ?? (1 + (is_array($r->guests) ? count($r->guests) : 0)) }}</span>
                                </td>
                                <td>
                                    @php
                                        $mix = [];
                                        if ($r->primary_ticket_type_id && isset($ticketTypeMap[$r->primary_ticket_type_id])) {
                                            $name = $ticketTypeMap[$r->primary_ticket_type_id];
                                            $mix[$name] = ($mix[$name] ?? 0) + 1;
                                        }
                                        if (is_array($r->guests)) {
                                            foreach ($r->guests as $g) {
                                                if (isset($g['ticket_type_id']) && isset($ticketTypeMap[$g['ticket_type_id']])) {
                                                    $name = $ticketTypeMap[$g['ticket_type_id']];
                                                    $mix[$name] = ($mix[$name] ?? 0) + 1;
                                                }
                                            }
                                        }
                                    @endphp
                                    @if(empty($mix))
                                        <span class="text-muted small">-</span>
                                    @else
                                        <div class="small d-flex flex-wrap gap-1">
                                            @foreach($mix as $name => $cnt)
                                                <span class="badge bg-secondary">{{ $name }}: {{ $cnt }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusEnum = \App\Enums\EventRequestStatus::tryFrom($r->status);
                                        $statusLabel = $statusEnum ? $statusEnum->label() : ucfirst(str_replace('_', ' ', $r->status));
                                        $badgeClass = match($statusEnum) {
                                            \App\Enums\EventRequestStatus::PENDING => 'bg-warning text-dark',
                                            \App\Enums\EventRequestStatus::AWAITING_PAYMENT => 'bg-info text-dark',
                                            \App\Enums\EventRequestStatus::PAID => 'bg-success',
                                            \App\Enums\EventRequestStatus::DECLINED => 'bg-danger',
                                            \App\Enums\EventRequestStatus::EXPIRED => 'bg-secondary',
                                            \App\Enums\EventRequestStatus::APPROVED => 'bg-success',
                                            default => 'bg-secondary',
                                        };
                                        $decisionIcon = match($statusEnum) {
                                            \App\Enums\EventRequestStatus::DECLINED => ['icon' => 'x-circle', 'class' => 'text-danger'],
                                            \App\Enums\EventRequestStatus::AWAITING_PAYMENT, \App\Enums\EventRequestStatus::PAID, \App\Enums\EventRequestStatus::APPROVED => ['icon' => 'check-circle-2', 'class' => 'text-success'],
                                            default => null,
                                        };
                                    @endphp
                                    <div class="d-flex flex-column">
                                        <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                                        @if($statusEnum === \App\Enums\EventRequestStatus::AWAITING_PAYMENT && $r->expires_at)
                                            <span class="small text-muted">Expires {{ $r->expires_at->diffForHumans() }}</span>
                                        @elseif($statusEnum === \App\Enums\EventRequestStatus::EXPIRED && $r->expires_at)
                                            <span class="small text-muted">Expired {{ $r->expires_at->diffForHumans() }}</span>
                                        @endif
                                        @if($decisionIcon && $r->admin)
                                            <span class="small text-muted d-inline-flex align-items-center gap-1 mt-1">
                                                <i data-lucide="{{ $decisionIcon['icon'] }}" class="{{ $decisionIcon['class'] }}" style="width:14px;height:14px;"></i>
                                                <span>by {{ $r->admin->name }} · {{ $r->updated_at->diffForHumans() }}</span>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-muted">{{ $r->created_at->diffForHumans() }}</td>
                                <td class="text-end">
                                    <a href="{{ route('event_requests.show', $r->id) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                        <i data-lucide="eye" class="me-1" style="width:16px;height:16px;"></i>
                                        <span>Review</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $requests->links() }}</div>
        </div>
    @endif
</div>
@endsection
