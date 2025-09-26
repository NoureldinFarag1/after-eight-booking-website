@extends('layouts.app')

@section('content')
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
                    <option value="pending" {{ ($status ?? '')==='pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ ($status ?? '')==='approved' ? 'selected' : '' }}>Approved</option>
                    <option value="declined" {{ ($status ?? '')==='declined' ? 'selected' : '' }}>Declined</option>
                </select>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" type="submit"><i class="bi bi-funnel"></i> Filter</button>
                <a href="{{ route('admin.event_requests.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i> Reset</a>
            </div>
        </form>
    </div>

    @if($requests->count() === 0)
        <div class="text-center text-muted py-5">
            <div class="mb-2"><i class="bi bi-inbox" style="font-size:2rem;"></i></div>
            <div>No requests found.</div>
        </div>
    @else
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Event</th>
                            <th>Primary</th>
                            <th class="text-center">Attendees</th>
                            <th>Tickets Mix</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $r)
                            <tr>
                                <td>{{ $r->id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $r->user->name ?? 'N/A' }}</div>
                                    <div class="text-muted small">{{ $r->user->email ?? '' }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $r->event->title ?? $r->event->name ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $r->primary_name ?? '-' }}</div>
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
                                    @php($badge = $r->status === 'approved' ? 'success' : ($r->status === 'declined' ? 'danger' : 'warning'))
                                    <span class="badge bg-{{ $badge }}">{{ ucfirst($r->status) }}</span>
                                    @if($r->status !== 'pending' && $r->admin)
                                        <div class="text-muted small mt-1">
                                            <i class="bi {{ $r->status === 'approved' ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }}"></i>
                                            by {{ $r->admin->name }}
                                            <span title="{{ $r->updated_at }}">{{ $r->updated_at->diffForHumans() }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $r->created_at->diffForHumans() }}</td>
                                <td class="text-end">
                                    <a href="{{ route('event_requests.show', $r->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i> View</a>
                                    <form method="POST" action="{{ route('admin.event_requests.approve', $r->id) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success" type="submit" {{ $r->status !== 'pending' ? 'disabled' : '' }}><i class="bi bi-check2"></i> Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.event_requests.decline', $r->id) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger" type="submit" {{ $r->status !== 'pending' ? 'disabled' : '' }}><i class="bi bi-x"></i> Decline</button>
                                    </form>
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
