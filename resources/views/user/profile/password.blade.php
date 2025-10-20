@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <div class="d-flex align-items-center gap-3 mb-2">
            <a href="{{ route('user.profile.index') }}" class="btn btn-outline-secondary">
                <i data-lucide="arrow-left" class="me-1"></i>Profile
            </a>
            <h1 class="h3 mb-0">Change Password</h1>
        </div>
        <p class="text-muted mb-0">Update your account password for security</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="shield" class="me-2"></i>Password Security
                </h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i data-lucide="check-circle" class="me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Security Info -->
                <div class="alert alert-info mb-4">
                    <i data-lucide="info" class="me-2"></i>
                    <strong>Password Requirements:</strong>
                    <ul class="mb-0 mt-2">
                        <li>At least 8 characters long</li>
                        <li>Must include letters and numbers</li>
                        <li>Cannot be too common or easily guessed</li>
                    </ul>
                </div>

                <form method="POST" action="{{ route('user.profile.password.update') }}">
                    @csrf
                    @method('PUT')

                    <!-- Current Password -->
                    <div class="mb-3">
                        <label for="current_password" class="form-label">
                            Current Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password"
                                   class="form-control @error('current_password') is-invalid @enderror"
                                   id="current_password"
                                   name="current_password"
                                   required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#current_password" aria-label="Show password">
                                <i data-lucide="eye"></i>
                            </button>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- New Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            New Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   id="password"
                                   name="password"
                                   required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#password" aria-label="Show password">
                                <i data-lucide="eye"></i>
                            </button>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Confirm New Password -->
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">
                            Confirm New Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password"
                                   class="form-control @error('password_confirmation') is-invalid @enderror"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#password_confirmation" aria-label="Show password">
                                <i data-lucide="eye"></i>
                            </button>
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i data-lucide="shield-check" class="me-1"></i>Update Password
                        </button>
                        <a href="{{ route('user.profile.index') }}" class="btn btn-outline-secondary">
                            <i data-lucide="x" class="me-1"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Security Tips -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i data-lucide="lightbulb" class="me-2"></i>Security Tips
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex align-items-start">
                            <i data-lucide="check-circle" class="text-success me-2 mt-1"></i>
                            <div>
                                <strong>Use a strong password:</strong> Combine letters, numbers, and symbols for better security.
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex align-items-start">
                            <i data-lucide="check-circle" class="text-success me-2 mt-1"></i>
                            <div>
                                <strong>Keep it unique:</strong> Don't reuse passwords from other accounts.
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex align-items-start">
                            <i data-lucide="check-circle" class="text-success me-2 mt-1"></i>
                            <div>
                                <strong>Update regularly:</strong> Change your password every few months for maximum security.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// No inline JS needed; global toggle-password handler in app.js handles it
</script>
@endpush
