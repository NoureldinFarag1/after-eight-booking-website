@extends('layouts.app')

@section('title', 'Staff Roles')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0"><i class="bi bi-people me-2"></i>Staff Members</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.staff.index', ['status' => !empty($showDeleted) ? null : 'deleted', 'q' => $q ?? null]) }}" class="btn btn-outline-secondary">
            <i class="bi bi-archive me-1"></i>{{ !empty($showDeleted) ? 'Show Active' : 'Show Deleted' }}
        </a>
        @empty($showDeleted)
            <a href="{{ route('admin.staff.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i>Create Staff
            </a>
        @endempty
    </div>

</div>

<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" action="{{ route('admin.staff.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label for="q" class="form-label mb-0 small text-muted">Search</label>
                <input type="text" name="q" id="q" value="{{ $q ?? '' }}" class="form-control" placeholder="Search name or email">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label mb-0 small text-muted">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="" {{ empty($status) ? 'selected' : '' }}>All (Active + Inactive)</option>
                    <option value="active" {{ ($status ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ ($status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="deleted" {{ ($status ?? '') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="role" class="form-label mb-0 small text-muted">Role</label>
                <select name="role" id="role" class="form-select">
                    <option value="">All Roles</option>
                    @foreach($manageableRoles as $r)
                        <option value="{{ $r->value }}" {{ ($roleFilter ?? '') === $r->value ? 'selected' : '' }}>{{ $r->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i>Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 staff-table">
                <thead>
                    <tr>
                        <th class="fw-semibold">Name</th>
                        <th class="fw-semibold">Email</th>
                        <th class="fw-semibold">Phone</th>
                        <th class="fw-semibold">Role</th>
                        <th class="fw-semibold">Status</th>
                        <th class="fw-semibold">Created</th>
                        <th class="text-end fw-semibold" style="width:320px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($operators as $op)
                        <tr class="staff-row">
                            <td class="text-truncate" style="max-width:160px">{{ $op->name }}</td>
                            <td class="text-truncate" style="max-width:200px">{{ $op->email }}</td>
                            <td class="text-truncate" style="max-width:110px">{{ $op->phone ?? '-' }}</td>
                            <td><span class="badge bg-info text-dark small">{{ $op->role->label() }}</span></td>
                            <td class="whitespace-nowrap">
                                @if(!empty($showDeleted))
                                    <span class="badge bg-dark" @if($op->deleted_at) title="Deleted {{ $op->deleted_at->diffForHumans() }}" @endif>Deleted</span>
                                @elseif($op->active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-muted small" style="white-space:nowrap">{{ $op->created_at?->diffForHumans() }}</td>
                            <td class="text-end text-nowrap" style="white-space:nowrap">
                                @if(!empty($showDeleted))
                                    <form action="{{ route('admin.operators.restore', $op->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-success mb-1">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>Restore
                                        </button>
                                    </form>
                                @else
                                <form action="{{ route('admin.staff.toggle-status', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi {{ $user->is_active ? 'bi-x-circle' : 'bi-check-circle' }} me-1"></i>
                                            {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.staff.password', $user) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-key me-1"></i>
                                        Password
                                    </a>
                                    <form action="{{ route('admin.staff.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash me-1"></i>
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No staff found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(method_exists($operators, 'links'))
        <div class="card-footer">{{ $operators->links() }}</div>
    @endif
</div>
@endsection
