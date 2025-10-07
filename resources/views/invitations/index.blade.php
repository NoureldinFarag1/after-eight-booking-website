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
                        <th>Event</th>
                        <th>Status</th>
                        <th>QR Status</th>
                        <th>QR Code</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invitations as $invitation)
                        <tr>
                            <td>{{ $invitation->id }}</td>
                            <td>{{ $invitation->name }}</td>
                            <td>{{ $invitation->email }}</td>
                            <td>
                                @if($invitation->event)
                                    <div>
                                        <strong>{{ $invitation->event->title }}</strong><br>
                                        <small class="text-muted">
                                            {{ $invitation->event->event_date->format('M j, Y') }} at {{ $invitation->event->location }}
                                        </small>
                                    </div>
                                @else
                                    <div class="text-danger">
                                        <small><i class="bi bi-exclamation-triangle me-1"></i>No event associated</small>
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($invitation->status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($invitation->status === 'sent')
                                    <span class="badge bg-primary">Sent</span>
                                @endif
                            </td>
                            <td>
                                @if($invitation->qr_status === 'valid')
                                    <span class="badge bg-success">Valid</span>
                                @elseif($invitation->qr_status === 'used')
                                    <span class="badge bg-secondary">Used</span>
                                @elseif($invitation->qr_status === 'cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </td>
                            <td>
                                @if($invitation->qr_code_path)
                                    <i class="bi bi-check-circle text-success" title="QR Code Generated"></i>
                                @else
                                    <i class="bi bi-x-circle text-muted" title="No QR Code"></i>
                                @endif
                            </td>
                            <td>{{ $invitation->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('invitations.show', $invitation) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">No invitations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
