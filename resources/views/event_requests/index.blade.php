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
                                @if($req->status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($req->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($req->status === 'declined')
                                    <span class="badge bg-danger">Declined</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('event_requests.show', $req->id) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                @if($req->status === 'pending')
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
