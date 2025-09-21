@extends('layouts.app')

@section('content')
<div class="container">
    <h2>My Requests</h2>

    @if($requests->isEmpty())
        <p>No requests yet.</p>
    @else
        <table class="table">
            <thead><tr><th>ID</th><th>Event</th><th>Status</th><th>Submitted At</th><th></th></tr></thead>
            <tbody>
            @foreach($requests as $r)
                <tr>
                    <td>{{ $r->id }}</td>
                    <td>{{ $r->event->title ?? 'N/A' }}</td>
                    <td>{{ ucfirst($r->status) }}</td>
                    <td>{{ $r->created_at }}</td>
                    <td><a href="{{ route('event_requests.show', $r->id) }}" class="btn btn-sm btn-primary">View</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
