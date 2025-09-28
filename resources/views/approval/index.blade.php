@extends('layouts.app')

@section('title','Approval Requests')

@section('content')
<div class="container">
    <h1 class="mb-4">Approval Requests</h1>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    @if($requests->count())
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead><tr><th>ID</th><th>User</th><th>Event</th><th>Submitted</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                @foreach($requests as $req)
                    <tr>
                        <td>{{ $req->id }}</td>
                        <td>{{ $req->user->name ?? 'N/A' }}</td>
                        <td>{{ $req->event->title ?? 'N/A' }}</td>
                        <td>{{ $req->created_at->format('M j, Y') }}</td>
                        <td>{{ ucfirst($req->status) }}</td>
                        <td>
                            <form method="POST" action="{{ route('approval.approve', $req->id) }}" class="d-inline">@csrf
                                <button class="btn btn-success btn-sm">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('approval.reject', $req->id) }}" class="d-inline">@csrf
                                <button class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            {{ $requests->links() }}
        </div>
    </div>
    @else
        <p>No pending requests.</p>
    @endif
</div>
@endsection
