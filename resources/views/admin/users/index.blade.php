@extends('layouts.app')

@section('title', 'Users Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Users Management</h1>
        <p class="text-muted mb-0">Manage regular users and their activity</p>
    </div>
</div>

<!-- Info Alert -->
<div class="alert alert-info d-flex align-items-center mb-4">
    <i class="bi bi-info-circle fs-5 me-3"></i>
    <div>
        <strong>User Status Information:</strong>
        <ul class="mb-0 mt-1">
            <li><strong>Active users</strong> can log in and access their accounts normally</li>
            <li><strong>Inactive users</strong> are immediately logged out and cannot access their accounts until reactivated</li>
            <li><strong>Deleted users</strong> are soft-deleted and can be restored if needed</li>
        </ul>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-people text-primary fs-2"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="fw-bold h4 mb-0">{{ number_format($stats['total_users']) }}</div>
                        <div class="text-muted small">Total Users</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-person-check text-success fs-2"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="fw-bold h4 mb-0">{{ number_format($stats['active_users']) }}</div>
                        <div class="text-muted small">Active Users</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-ticket-perforated text-info fs-2"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="fw-bold h4 mb-0">{{ number_format($stats['users_with_bookings']) }}</div>
                        <div class="text-muted small">Users with Bookings</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-person-plus text-warning fs-2"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="fw-bold h4 mb-0">{{ number_format($stats['new_users_this_month']) }}</div>
                        <div class="text-muted small">New This Month</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
            <!-- Search -->
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text"
                           class="form-control"
                           id="search"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Name, email, or phone...">
                </div>
            </div>

            <!-- Status Filter -->
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="deleted" {{ request('status') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                </select>
            </div>

            <!-- Booking Activity Filter -->
            <div class="col-md-2">
                <label for="booking_activity" class="form-label">Booking Activity</label>
                <select class="form-select" id="booking_activity" name="booking_activity">
                    <option value="">All Users</option>
                    <option value="has_bookings" {{ request('booking_activity') === 'has_bookings' ? 'selected' : '' }}>Has Bookings</option>
                    <option value="no_bookings" {{ request('booking_activity') === 'no_bookings' ? 'selected' : '' }}>No Bookings</option>
                </select>
            </div>

            <!-- Date Range -->
            <div class="col-md-2">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date"
                       class="form-control"
                       id="date_from"
                       name="date_from"
                       value="{{ request('date_from') }}">
            </div>

            <div class="col-md-2">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date"
                       class="form-control"
                       id="date_to"
                       name="date_to"
                       value="{{ request('date_to') }}">
            </div>

            <!-- Actions -->
            <div class="col-12">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i>Apply Filters
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Sort Options -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-3">
        <small class="text-muted">Sort by:</small>
        <div class="btn-group btn-group-sm" role="group">
            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_direction' => request('sort_direction') === 'asc' ? 'desc' : 'asc']) }}"
               class="btn btn-outline-secondary {{ request('sort_by') === 'name' ? 'active' : '' }}">
                Name
                @if(request('sort_by') === 'name')
                    <i class="bi bi-arrow-{{ request('sort_direction') === 'asc' ? 'up' : 'down' }}"></i>
                @endif
            </a>
            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_direction' => request('sort_direction') === 'asc' ? 'desc' : 'asc']) }}"
               class="btn btn-outline-secondary {{ request('sort_by') === 'created_at' || !request('sort_by') ? 'active' : '' }}">
                Date Joined
                @if(request('sort_by') === 'created_at' || !request('sort_by'))
                    <i class="bi bi-arrow-{{ request('sort_direction') === 'asc' ? 'up' : 'down' }}"></i>
                @endif
            </a>
            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'bookings_count', 'sort_direction' => request('sort_direction') === 'asc' ? 'desc' : 'asc']) }}"
               class="btn btn-outline-secondary {{ request('sort_by') === 'bookings_count' ? 'active' : '' }}">
                Bookings
                @if(request('sort_by') === 'bookings_count')
                    <i class="bi bi-arrow-{{ request('sort_direction') === 'asc' ? 'up' : 'down' }}"></i>
                @endif
            </a>
        </div>
    </div>
    <div class="text-muted small">
        Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-body p-0">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-black">
                        <tr>
                            <th class="text-white">User</th>
                            <th class="text-white">Contact</th>
                            <th class="text-center text-white">Status</th>
                            <th class="text-center text-white">Bookings</th>
                            <th class="text-center text-white">Tickets</th>
                            <th class="text-center text-white">Joined</th>
                            <th class="text-center text-white">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <!-- User Info -->
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center"
                                                 style="width: 40px; height: 40px;">
                                                <span class="text-white fw-bold">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="fw-semibold text-white">{{ $user->name }}</div>
                                            <div class="text-muted small">ID: {{ $user->id }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Contact -->
                                <td>
                                    <div class="text-white">{{ $user->email }}</div>
                                    @if($user->phone)
                                        <div class="text-muted small">{{ $user->phone }}</div>
                                    @endif
                                </td>

                                <!-- Status -->
                                <td class="text-center">
                                    @if($user->deleted_at)
                                        <span class="badge bg-dark">Deleted</span>
                                    @elseif($user->active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>

                                <!-- Bookings -->
                                <td class="text-center">
                                    <div class="fw-bold text-muted">{{ $user->bookings_count }}</div>
                                    @if($user->bookings_count > 0)
                                        <div class="text-muted small">
                                            Latest: {{ $user->bookings->first()?->created_at->format('M j') }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Tickets -->
                                <td class="text-center text-muted">
                                    <div class="fw-bold">{{ $user->tickets_count }}</div>
                                    @if($user->tickets_count > 0)
                                        <div class="text-muted small">tickets</div>
                                    @endif
                                </td>

                                <!-- Joined -->
                                <td class="text-center text-muted">
                                    <div>{{ $user->created_at->format('M j, Y') }}</div>
                                    <div class="text-muted small">{{ $user->created_at->diffForHumans() }}</div>
                                </td>

                                <!-- Actions -->
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.users.show', $user) }}"
                                           class="btn btn-outline-primary btn-sm"
                                           title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @if(!$user->deleted_at)
                                            <button type="button"
                                                    class="btn btn-outline-{{ $user->active ? 'warning' : 'success' }} btn-sm toggle-status-btn"
                                                    data-user-id="{{ $user->id }}"
                                                    data-current-status="{{ $user->active ? 'active' : 'inactive' }}"
                                                    title="{{ $user->active ? 'Deactivate user (they will be logged out immediately)' : 'Activate user (allow login access)' }}">
                                                <i class="bi bi-{{ $user->active ? 'pause' : 'play' }}"></i>
                                            </button>

                                            <button type="button"
                                                    class="btn btn-outline-danger btn-sm delete-user-btn text-danger"
                                                    data-user-id="{{ $user->id }}"
                                                    data-user-name="{{ $user->name }}"
                                                    title="Delete User">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @else
                                            <button type="button"
                                                    class="btn btn-outline-info btn-sm restore-user-btn"
                                                    data-user-id="{{ $user->id }}"
                                                    data-user-name="{{ $user->name }}"
                                                    title="Restore User">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer bg-black">
                <div class="ae-pagination d-flex justify-content-center">
                    {{ $users->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3 text-muted">No Users Found</h5>
                <p class="text-muted">No users match your current filters.</p>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-clockwise me-1"></i>Reset Filters
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
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

<!-- Restore Confirmation Modal -->
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
            const isActivating = currentStatus === 'inactive';

            try {
                this.disabled = true;
                this.innerHTML = '<i class="bi bi-hourglass-split"></i>';

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
                    // Show success notification
                    if (window.__notyfInstance) {
                        window.__notyfInstance.success(data.message);
                    }

                    // Reload page to update the UI
                    window.location.reload();
                } else {
                    throw new Error(data.error || 'Failed to update user status');
                }
            } catch (error) {
                if (window.__notyfInstance) {
                    window.__notyfInstance.error(error.message);
                }

                // Reset button
                this.disabled = false;
                this.innerHTML = `<i class="bi bi-${currentStatus === 'active' ? 'pause' : 'play'}"></i>`;
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
                window.location.reload();
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
