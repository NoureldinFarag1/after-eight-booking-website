@extends('layouts.app')

@section('title', 'Admins')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Admins</h1>
        <a href="{{ route('admin.admins.create') }}" class="btn btn-primary">Create Admin</a>
    </div>

    <div class="card">
        <div class="card-body">
            @if($admins->count())
                <table class="table">
                    <thead><tr><th>Name</th><th>Email</th><th>Actions</th></tr></thead>
                    <tbody>
                        @foreach($admins as $admin)
                            <tr>
                                <td>{{ $admin->name }}</td>
                                <td>{{ $admin->email }}</td>
                                <td>
                                    <a href="{{ route('admin.admins.edit', $admin) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form action="{{ route('admin.admins.destroy', $admin) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this admin?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{ $admins->links() }}
            @else
                <p>No admins found.</p>
            @endif
        </div>
    </div>
</div>
@endsection
