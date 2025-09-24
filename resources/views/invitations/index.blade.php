@extends('layouts.app')

@section('title', 'Invitations')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Invitations</h1>
        <a href="{{ route('invitations.create') }}" class="btn btn-warning">
            <i class="bi bi-envelope-plus me-1"></i> New Invitation
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invitations as $invitation)
                        <tr>
                            <td>{{ $invitation->id }}</td>
                            <td>{{ $invitation->name }}</td>
                            <td>{{ $invitation->email }}</td>
                            <td>
                                @if($invitation->status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($invitation->status === 'sent')
                                    <span class="badge bg-primary">Sent</span>
                                @elseif($invitation->status === 'accepted')
                                    <span class="badge bg-success">Accepted</span>
                                @elseif($invitation->status === 'declined')
                                    <span class="badge bg-danger">Declined</span>
                                @endif
                            </td>
                            <td>{{ $invitation->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No invitations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection