@php
use App\Services\ThemeService;
@endphp

@extends('layouts.app')

@section('title', 'Admins')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Admins</h1>
        <a href="{{ route('admin.admins.create') }}" class="btn btn-primary">Create Admin</a>
    </div>

    @include('partials.filter_card', ['route' => route('admin.admins.index'), 'q' => $q ?? '', 'status' => $status ?? ''])

    <div class="card">
        <div class="card-body">
            @if($admins->count())
                <div class="table-responsive">
                    <table class="table users-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($admins as $admin)
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-initials-avatar" style="background-color: {{ ThemeService::getUserColor($admin->name) }};">
                                                {{ strtoupper(substr($admin->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', $admin->name)[1] ?? '', 0, 1)) }}
                                            </div>
                                            <span class="user-name">{{ $admin->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="contact-info">
                                            <a href="mailto:{{ $admin->email }}">{{ $admin->email }}</a>
                                        </div>
                                    </td>
                                    <td>
                                        @if($admin->trashed())
                                            <span class="status-badge-lg bg-danger-soft text-danger">Deleted</span>
                                        @elseif($admin->is_active)
                                            <span class="status-badge-lg bg-success-soft text-success">Active</span>
                                        @else
                                            <span class="status-badge-lg bg-warning-soft text-warning">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($admin->trashed())
                                            <form action="{{ route('admin.admins.restore', $admin->id) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-success">Restore</button>
                                            </form>
                                        @else
                                            <div class="d-flex justify-content-end gap-2">
                                                <form action="{{ route('admin.admins.toggle', $admin) }}" method="POST" class="d-inline-block">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm {{ $admin->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                                        {{ $admin->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                                <a href="{{ route('admin.admins.edit', $admin) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                                <form action="{{ route('admin.admins.destroy', $admin) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this admin?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center text-muted mt-3">No admins found matching your criteria.</p>
            @endif
        </div>

        <div class="card-footer px-3 border-0">
            {{ $admins->appends(request()->query())->links('partials.pagination') }}
        </div>
    </div>
</div>
@endsection
