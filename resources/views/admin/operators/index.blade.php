@extends('layouts.app')

@section('title', 'Operators')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0"><i class="bi bi-people me-2"></i>Operators</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.operators.index', ['status' => !empty($showDeleted) ? null : 'deleted', 'q' => $q ?? null]) }}" class="btn btn-outline-secondary">
            <i class="bi bi-archive me-1"></i>{{ !empty($showDeleted) ? 'Show Active' : 'Show Deleted' }}
        </a>
        @empty($showDeleted)
            <a href="{{ route('admin.operators.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i>Create Operator
            </a>
        @endempty
    </div>

</div>

<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" action="{{ route('admin.operators.index') }}" class="row g-2 align-items-end">
            <div class="col-md-5">
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
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="{{ route('admin.operators.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i>Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($operators as $op)
                        <tr>
                            <td>{{ $op->name }}</td>
                            <td>{{ $op->email }}</td>
                            <td>{{ $op->phone ?? '-' }}</td>
                            <td>
                                @if(!empty($showDeleted))
                                    <span class="badge bg-dark">Deleted</span>
                                @elseif($op->active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $op->created_at?->diffForHumans() }}</td>
                            <td class="text-end">
                                @if(!empty($showDeleted))
                                    <form action="{{ route('admin.operators.restore', $op->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>Restore
                                        </button>
                                    </form>
                                @else
                                <form action="{{ route('admin.operators.toggle', $op) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $op->active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                        <i class="bi {{ $op->active ? 'bi-slash-circle' : 'bi-check-circle' }} me-1"></i>
                                        {{ $op->active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <a href="{{ route('admin.operators.password.edit', $op) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-key me-1"></i>Reset Password
                                </a>
                                <form action="{{ route('admin.operators.destroy', $op) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete operator {{ $op->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No operators yet.</td>
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
