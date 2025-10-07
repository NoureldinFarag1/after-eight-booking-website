@extends('layouts.app')

@section('title', 'User Details - ' . $user->name)

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <div class="d-flex align-items-center gap-3 mb-2">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Users
            </a>
            <h1 class="h3 mb-0">User Details</h1>
        </div>
        <p class="text-muted mb-0">Detailed information for {{ $user->name }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($user->deleted_at)
            <button type="button"
                    class="btn btn-info restore-user-btn"
                    data-user-id="{{ $user->id }}"
                    data-user-name="{{ $user->name }}">
                <i class="bi bi-arrow-clockwise me-1"></i>Restore User
            </button>
        @else
            <button type="button"
                    class="btn btn-{{ $user->active ? 'warning' : 'success' }} toggle-status-btn"
                    data-user-id="{{ $user->id }}"
                    data-current-status="{{ $user->active ? 'active' : 'inactive' }}">
                <i class="bi bi-{{ $user->active ? 'pause' : 'play' }} me-1"></i>
                {{ $user->active ? 'Deactivate' : 'Activate' }}
            </button>
            <button type="button"
                    class="btn btn-outline-danger delete-user-btn"
                    data-user-id="{{ $user->id }}"
                    data-user-name="{{ $user->name }}">
                <i class="bi bi-trash me-1"></i>Delete
            </button>
        @endif
    </div>
</div>

<div class="row">
    <!-- User Information -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-person-circle me-2"></i>User Information
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                         style="width: 80px; height: 80px;">
                        <span class="text-white fw-bold fs-2">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                    </div>
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    @if($user->deleted_at)
                        <span class="badge bg-dark">Deleted User</span>
                    @elseif($user->active)
                        <span class="badge bg-success">Active User</span>
                    @else
                        <span class="badge bg-secondary">Inactive User</span>
                    @endif
                </div>

                <div class="row g-2">
                    <div class="col-4">
                        <div class="text-muted small">User ID</div>
                        <div class="fw-semibold">#{{ $user->id }}</div>
                    </div>
                    <div class="col-8">
                        <div class="text-muted small">Email</div>
                        <div class="fw-semibold">{{ $user->email }}</div>
                    </div>
                    @if($user->phone)
                        <div class="col-12">
                            <div class="text-muted small">Phone</div>
                            <div class="fw-semibold">{{ $user->phone }}</div>
                        </div>
                    @endif
                    <div class="col-6">
                        <div class="text-muted small">Joined</div>
                        <div class="fw-semibold">{{ $user->created_at->format('M j, Y') }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Last Updated</div>
                        <div class="fw-semibold">{{ $user->updated_at->format('M j, Y') }}</div>
                    </div>
                    @if($user->deleted_at)
                        <div class="col-12">
                            <div class="text-muted small">Deleted At</div>
                            <div class="fw-semibold text-danger">{{ $user->deleted_at->format('M j, Y g:i A') }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-graph-up me-2"></i>Quick Stats
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-6">
                        <div class="border rounded p-3">
                            <div class="fs-4 fw-bold text-primary">{{ $bookingStats->total_bookings ?? 0 }}</div>
                            <div class="small text-muted">Total Bookings</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-3">
                            <div class="fs-4 fw-bold text-info">{{ $ticketStats->total_tickets ?? 0 }}</div>
                            <div class="small text-muted">Total Tickets</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-3">
                            <div class="fs-4 fw-bold text-success">
                                {{ number_format($bookingStats->total_spent ?? 0, 2) }} EGP
                            </div>
                            <div class="small text-muted">Total Spent</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-3">
                            <div class="fs-4 fw-bold text-warning">{{ $ticketStats->used_tickets ?? 0 }}</div>
                            <div class="small text-muted">Used Tickets</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Details -->
    <div class="col-lg-8">
        <!-- Bookings -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-ticket-perforated me-2"></i>Bookings
                    <span class="badge bg-primary ms-2">{{ $user->bookings->count() }}</span>
                </h5>
            </div>
            <div class="card-body">
                @if($user->bookings->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Event</th>
                                    <th>Date</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->bookings as $booking)
                                    <tr>
                                        <td>
                                            <a href="{{ route('bookings.show', $booking) }}"
                                               class="text-decoration-none fw-semibold">
                                                {{ $booking->booking_reference }}
                                            </a>
                                        </td>
                                        <td>
                                            <div>{{ $booking->event->title }}</div>
                                            <div class="text-muted small">
                                                {{ $booking->event->event_date->format('M j, Y') }}
                                            </div>
                                        </td>
                                        <td>
                                            <div>{{ $booking->booking_date->format('M j, Y') }}</div>
                                            <div class="text-muted small">{{ $booking->booking_date->format('g:i A') }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark">{{ $booking->quantity }}</span>
                                        </td>
                                        <td class="text-end fw-semibold">
                                            {{ number_format((float)$booking->total_amount, 2) }} EGP
                                        </td>
                                        <td class="text-center">
                                            <span class="badge
                                                @if($booking->status->value === 'confirmed') bg-success
                                                @elseif($booking->status->value === 'pending') bg-warning
                                                @elseif($booking->status->value === 'cancelled') bg-danger
                                                @else bg-secondary @endif">
                                                {{ ucfirst($booking->status->value) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-ticket text-muted mb-3" style="font-size: 2rem;"></i>
                        <p class="text-muted mb-0">No bookings yet</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Tickets -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-qr-code me-2"></i>Tickets
                    <span class="badge bg-info ms-2">{{ $user->tickets->count() }}</span>
                </h5>
                @if($ticketStats->total_tickets > 0)
                    <div class="d-flex gap-2">
                        <small class="badge bg-success">
                            {{ $ticketStats->used_tickets }} Used
                        </small>
                        <small class="badge bg-warning">
                            {{ $ticketStats->unused_tickets }} Unused
                        </small>
                    </div>
                @endif
            </div>
            <div class="card-body">
                @if($user->tickets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Ticket Code</th>
                                    <th>Event</th>
                                    <th>Booking</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Used</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->tickets as $ticket)
                                    <tr>
                                        <td>
                                            <a href="{{ route('tickets.show', $ticket) }}"
                                               class="text-decoration-none fw-semibold font-monospace">
                                                {{ $ticket->qr_code }}
                                            </a>
                                        </td>
                                        <td>
                                            <div>{{ $ticket->event->title }}</div>
                                            <div class="text-muted small">
                                                {{ $ticket->event->event_date->format('M j, Y') }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($ticket->booking)
                                                <a href="{{ route('bookings.show', $ticket->booking) }}"
                                                   class="text-decoration-none">
                                                    {{ $ticket->booking->booking_reference }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge
                                                @if($ticket->status->value === 'active') bg-success
                                                @elseif($ticket->status->value === 'used') bg-info
                                                @elseif($ticket->status->value === 'cancelled') bg-danger
                                                @else bg-secondary @endif">
                                                {{ ucfirst($ticket->status->value) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($ticket->scanned_at)
                                                <div class="text-success fw-semibold">
                                                    <i class="bi bi-check-circle me-1"></i>
                                                    {{ $ticket->scanned_at->format('M j, g:i A') }}
                                                </div>
                                            @else
                                                <span class="text-muted">Not used</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-qr-code text-muted mb-3" style="font-size: 2rem;"></i>
                        <p class="text-muted mb-0">No tickets yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modals (same as in index view) -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-exclamation-triangle text-warning fs-2 me-3"></i>
                    <div>
                        <p class="mb-1">Are you sure you want to delete <strong class="user-name-placeholder"></strong>?</p>
                        <p class="text-muted small mb-0">This action will soft-delete the user. They can be restored later if needed.</p>
                    </div>
                </div>
                <div class="alert alert-warning">
                    <strong>Note:</strong> Users with active bookings cannot be deleted.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteUser">Delete User</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="restoreUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Restore User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to restore <strong class="user-name-placeholder"></strong>?</p>
                <p class="text-muted small">This will reactivate the user account and they will be able to log in again.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info" id="confirmRestoreUser">Restore User</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle user status
    document.querySelectorAll('.toggle-status-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const userId = this.dataset.userId;
            const currentStatus = this.dataset.currentStatus;

            try {
                this.disabled = true;
                const originalHtml = this.innerHTML;
                this.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Processing...';

                const response = await fetch(`/admin/users/${userId}/toggle-status`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    if (window.__notyfInstance) {
                        window.__notyfInstance.success(data.message);
                    }
                    window.location.reload();
                } else {
                    throw new Error(data.error || 'Failed to update user status');
                }
            } catch (error) {
                if (window.__notyfInstance) {
                    window.__notyfInstance.error(error.message);
                }
                this.disabled = false;
                this.innerHTML = originalHtml;
            }
        });
    });

    // Delete user functionality
    let deleteUserId = null;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));

    document.querySelectorAll('.delete-user-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            deleteUserId = this.dataset.userId;
            const userName = this.dataset.userName;
            document.querySelector('#deleteUserModal .user-name-placeholder').textContent = userName;
            deleteModal.show();
        });
    });

    document.getElementById('confirmDeleteUser').addEventListener('click', async function() {
        if (!deleteUserId) return;

        try {
            this.disabled = true;
            this.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Deleting...';

            const response = await fetch(`/admin/users/${deleteUserId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok) {
                deleteModal.hide();
                if (window.__notyfInstance) {
                    window.__notyfInstance.success(data.message);
                }
                window.location.href = '{{ route("admin.users.index") }}';
            } else {
                throw new Error(data.error || 'Failed to delete user');
            }
        } catch (error) {
            if (window.__notyfInstance) {
                window.__notyfInstance.error(error.message);
            }
            this.disabled = false;
            this.innerHTML = 'Delete User';
        }
    });

    // Restore user functionality
    let restoreUserId = null;
    const restoreModal = new bootstrap.Modal(document.getElementById('restoreUserModal'));

    document.querySelectorAll('.restore-user-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            restoreUserId = this.dataset.userId;
            const userName = this.dataset.userName;
            document.querySelector('#restoreUserModal .user-name-placeholder').textContent = userName;
            restoreModal.show();
        });
    });

    document.getElementById('confirmRestoreUser').addEventListener('click', async function() {
        if (!restoreUserId) return;

        try {
            this.disabled = true;
            this.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Restoring...';

            const response = await fetch(`/admin/users/${restoreUserId}/restore`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok) {
                restoreModal.hide();
                if (window.__notyfInstance) {
                    window.__notyfInstance.success(data.message);
                }
                window.location.reload();
            } else {
                throw new Error(data.error || 'Failed to restore user');
            }
        } catch (error) {
            if (window.__notyfInstance) {
                window.__notyfInstance.error(error.message);
            }
            this.disabled = false;
            this.innerHTML = 'Restore User';
        }
    });
});
</script>
@endpush
