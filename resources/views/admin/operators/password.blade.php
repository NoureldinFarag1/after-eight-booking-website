@extends('layouts.app')

@section('title', 'Reset Operator Password')

@section('content')
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-key me-2"></i>Reset Password</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Operator:</strong> {{ $user->name }} ({{ $user->email }})
                </div>
                <form method="POST" action="{{ route('admin.operators.password.update', $user) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" required>
                        @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2 me-1"></i>Update Password
                        </button>
                        <a href="{{ route('admin.operators.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
