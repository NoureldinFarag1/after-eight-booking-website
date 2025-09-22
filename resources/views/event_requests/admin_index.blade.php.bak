@extends('layouts.app')

@section('content')
<div class="container">
    <h2>All Event Requests (Admin)</h2>

    @if($requests->isEmpty())
        <p>No requests found.</p>
    @else
        <table class="table">
            <thead><tr><th>ID</th><th>User</th><th>Event</th><th>Status</th><th>Submitted At</th><th>Actions</th></tr></thead>
            <tbody>
            @foreach($requests as $r)
                <tr>
                    <td>{{ $r->id }}</td>
                    <td>{{ $r->user->name ?? 'N/A' }}</td>
                    <td>{{ $r->event->title ?? 'N/A' }}</td>
                    <td>{{ ucfirst($r->status) }}</td>
                    <td>{{ $r->created_at }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.event_requests.approve', $r->id) }}" style="display:inline">
                            @csrf
                            <button class="btn btn-sm btn-success" type="submit">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('admin.event_requests.decline', $r->id) }}" style="display:inline">
                            @csrf
                            <button class="btn btn-sm btn-danger" type="submit">Decline</button>
                        </form>
                        <a href="{{ route('event_requests.show', $r->id) }}" class="btn btn-sm btn-secondary">View</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{ $requests->links() }}
    @endif
</div>
@endsection
