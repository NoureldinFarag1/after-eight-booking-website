@php
use App\Services\ThemeService;
@endphp

@extends('layouts.app')

@section('title', 'Administrators')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0"><i class="bi bi-shield-shaded me-2"></i>Administrators</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.admins.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i>Create Admin
            </a>
        </div>
    </div>

    <div class="ae-card">
        <div class="ae-card-body border-bottom">
            <form method="GET" action="{{ route('admin.admins.index') }}" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label for="q" class="form-label mb-0 small text-muted">Search</label>
                    <input type="text" name="q" id="q" value="{{ $q ?? '' }}" class="form-control"
                        placeholder="Search by name or email...">
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label mb-0 small text-muted">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="" {{ empty($status) ? 'selected' : '' }}>All (Active + Inactive)
                        </option>
                        <option value="active" {{ ($status ?? '') === 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="inactive" {{ ($status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive Only
                        </option>
                        <option value="deleted" {{ ($status ?? '') === 'deleted' ? 'selected' : '' }}>Deleted Only
                        </option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary flex-grow-1"><i
                            class="bi bi-funnel me-1"></i>Filter</button>
                    <a href="{{ route('admin.admins.index') }}" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Reset Filters"><i
                            class="bi bi-x-circle"></i></a>
                </div>
            </form>
        </div>
        <div class="ae-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 users-table">
                    <thead>
                        <tr>
                            <th class="fw-semibold">Name</th>
                            <th class="fw-semibold">Contact</th>
                            <th class="fw-semibold">Status</th>
                            <th class="fw-semibold">Created</th>
                            <th class="text-end fw-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($admins as $admin)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-initials-avatar"
                                            style="background-color: {{ ThemeService::getUserColor($admin->name) }};">
                                            {{ substr($admin->name, 0, 1) }}
                                        </div>
                                        <div class="ms-3">
                                            <div class="fw-bold text-nowrap">{{ $admin->name }}</div>
                                            <div class="text-muted small text-truncate" style="max-width: 200px;">{{ $admin->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-nowrap">{{ $admin->phone ?? '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if ($admin->trashed())
                                        <span class="badge bg-dark" data-bs-toggle="tooltip"
                                            title="Deleted {{ $admin->deleted_at->diffForHumans() }}">Deleted</span>
                                    @elseif($admin->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-muted small text-nowrap">{{ $admin->created_at?->diffForHumans() }}</td>
                                <td class="text-end text-nowrap">
                                    @if ($admin->trashed())
                                        <form action="{{ route('admin.admins.restore', $admin->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-success"
                                                data-bs-toggle="tooltip" title="Restore this admin">
                                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.admins.toggle', $admin) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="btn btn-sm btn-icon btn-outline-secondary {{ $admin->id === 1 || $admin->id === auth()->id() ? 'disabled' : '' }}"
                                                @if ($admin->id === 1 || $admin->id === auth()->id()) disabled @endif data-bs-toggle="tooltip"
                                            title="{{ $admin->is_active ? 'Deactivate' : 'Activate' }} this admin">
                                                <i
                                                    class="bi {{ $admin->is_active ? 'bi-x-circle' : 'bi-check-circle' }}"></i>
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.admins.edit', $admin) }}"
                                            class="btn btn-sm btn-icon btn-outline-primary" data-bs-toggle="tooltip"
                                            title="Edit this admin">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.admins.destroy', $admin) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this admin? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-sm btn-icon btn-outline-danger {{ $admin->id === 1 || $admin->id === auth()->id() ? 'disabled' : '' }}"
                                                @if ($admin->id === 1 || $admin->id === auth()->id()) disabled @endif data-bs-toggle="tooltip" title="Delete this admin">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="bi bi-exclamation-triangle fs-2 text-warning"></i>
                                    <p class="mb-0 mt-2">No administrators found matching your criteria.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($admins->hasPages())
            <div class="ae-card-footer">
                {{ $admins->appends(request()->query())->links('partials.pagination') }}
            </div>
        @endif
    </div>
@endsection
