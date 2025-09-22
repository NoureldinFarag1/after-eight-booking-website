@extends('layouts.app')

@section('title', 'My Requests')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">My Event Requests</h5>
    </div>
    <div class="card-body">
        @if($requests->isEmpty())
            <p>You have not submitted any requests yet.</p>
        @else
            <table class="table table-bordered">
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
                            <td>{{ $req->event->title ?? 'Event Deleted' }}</td>
                            <td>{{ $req->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                @if($req->status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($req->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($req->status === 'declined')
                                    <span class="badge bg-danger">Declined</span>
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
