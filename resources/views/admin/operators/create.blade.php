@extends('layouts.app')

@section('title', 'Create Operator')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Create Operator</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.operators.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input type="text" class="form-control" value="Auto-generated from name (e.g. operator-create-test@aftereight.com)" disabled>
                        <div class="form-text">Email will be generated automatically after you submit.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone (optional)</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                        @error('phone')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                        @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>

                    <div class="d-flex gap-2">
                        
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select name="role" id="role" class="form-control" required>
                                <option value="admin">Operator</option>
                                <option value="approval_officer">Approval Officer</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2 me-1"></i>Create
                        </button>
                        <a href="{{ route('admin.operators.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
