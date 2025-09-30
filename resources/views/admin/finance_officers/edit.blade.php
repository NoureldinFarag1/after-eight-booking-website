@extends('layouts.app')

@section('title', 'Edit Finance Officer')

@section('content')
<div class="container">
    <h1>Edit Finance Officer</h1>
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.finance_officers.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password (leave blank to keep)</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password Confirmation</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
                <button class="btn btn-primary">Save</button>
                <a href="{{ route('admin.finance_officers.index') }}" class="btn btn-secondary ms-2">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
