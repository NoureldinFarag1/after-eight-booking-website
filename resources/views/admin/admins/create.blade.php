@extends('layouts.app')

@section('title', 'Create Admin')

@section('content')
<div class="container">
    <h1>Create Admin</h1>
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.admins.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password Confirmation</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <button class="btn btn-primary">Create</button>
                <a href="{{ route('admin.admins.index') }}" class="btn btn-secondary ms-2">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
