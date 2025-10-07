@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <div class="d-flex align-items-center gap-3 mb-2">
            <a href="{{ route('user.profile.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Profile
            </a>
            <h1 class="h3 mb-0">Edit Profile</h1>
        </div>
        <p class="text-muted mb-0">Update your personal information</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-person-gear me-2"></i>Personal Information
                </h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('user.profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- Name -->
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $user->name) }}"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="email"
                                   name="email"
                                   value="{{ old('email', $user->email) }}"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   id="phone"
                                   name="phone"
                                   value="{{ old('phone', $user->phone) }}"
                                   placeholder="e.g., +20 123 456 7890">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Birthday -->
                        <div class="col-md-6 mb-3">
                            <label for="birthday" class="form-label">Birthday <span class="text-danger">*</span></label>
                            <input type="date"
                                   class="form-control @error('birthday') is-invalid @enderror"
                                   id="birthday"
                                   name="birthday"
                                   value="{{ old('birthday', $user->birthday?->format('Y-m-d')) }}"
                                   required
                                   max="{{ now()->subYears(13)->format('Y-m-d') }}">
                            <div class="form-text">You must be at least 13 years old.</div>
                            @error('birthday')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>                        <!-- Gender -->
                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select @error('gender') is-invalid @enderror"
                                    id="gender"
                                    name="gender">
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Account Info (Read-only) -->
                    <hr class="my-4">
                    <h6 class="text-muted mb-3">Account Information</h6>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Member Since</label>
                            <input type="text"
                                   class="form-control-plaintext"
                                   value="{{ $user->created_at->format('F j, Y') }}"
                                   readonly>
                        </div>
                        @if($user->birthday)
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Current Age</label>
                                <input type="text"
                                       class="form-control-plaintext"
                                       value="{{ $user->getDisplayAge() }} years old"
                                       readonly>
                            </div>
                        @endif
                        <div class="col-md-{{ $user->birthday ? '4' : '6' }} mb-3">
                            <label class="form-label">Last Updated</label>
                            <input type="text"
                                   class="form-control-plaintext"
                                   value="{{ $user->updated_at->format('F j, Y g:i A') }}"
                                   readonly>
                        </div>
                    </div>                    <div class="d-flex gap-2 pt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Save Changes
                        </button>
                        <a href="{{ route('user.profile.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-gear me-2"></i>Account Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-key text-warning fs-4"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Change Password</h6>
                                <p class="text-muted small mb-2">Update your account password for security</p>
                                <a href="{{ route('user.profile.password.edit') }}" class="btn btn-sm btn-outline-warning">
                                    Change Password
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-calendar-event text-primary fs-4"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">View Activity</h6>
                                <p class="text-muted small mb-2">Check your bookings, tickets, and invitations</p>
                                <a href="{{ route('user.profile.index') }}" class="btn btn-sm btn-outline-primary">
                                    View Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
