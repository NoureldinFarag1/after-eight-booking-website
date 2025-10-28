@extends('layouts.app')

@section('title', 'Complete Your Profile')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Complete Your Profile</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Welcome!</strong> Please complete your profile information to continue using After Eight Events.
                        This information helps us provide you with a better experience and is required for event bookings.
                    </div>

                    <form method="POST" action="{{ route('profile.complete') }}" class="needs-validation" novalidate>
                        @csrf

                        <!-- Name Field -->
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="fas fa-user me-1"></i>Full Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $user->name) }}"
                                   required
                                   autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email Field (Read Only) -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-1"></i>Email Address
                            </label>
                            <input type="email"
                                   class="form-control"
                                   id="email"
                                   value="{{ $user->email }}"
                                   readonly>
                            <div class="form-text">
                                <i class="fas fa-lock me-1"></i>Email cannot be changed
                            </div>
                        </div>

                        <!-- Phone Field -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">
                                <i class="fas fa-phone me-1"></i>Phone Number <span class="text-danger">*</span>
                            </label>
                            <input type="tel"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   id="phone"
                                   name="phone"
                                   value="{{ old('phone', $user->phone) }}"
                                   required
                                   placeholder="e.g., +1234567890">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Birthday Field -->
                        <div class="mb-3">
                            <label for="birthday" class="form-label">
                                <i class="fas fa-birthday-cake me-1"></i>Date of Birth <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   class="form-control @error('birthday') is-invalid @enderror"
                                   id="birthday"
                                   name="birthday"
                                   value="{{ old('birthday', $user->birthday?->format('Y-m-d')) }}"
                                   max="{{ now()->subYears(13)->format('Y-m-d') }}"
                                   required>
                            @error('birthday')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i><p class="text-white">You must be at least 13 years old</p>
                            </div>
                        </div>

                        <!-- Gender Field -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-user-friends me-1"></i>Gender <span class="text-danger">*</span>
                            </label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input @error('gender') is-invalid @enderror"
                                               type="radio"
                                               name="gender"
                                               id="gender_male"
                                               value="male"
                                               {{ old('gender', $user->gender) == 'male' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label" for="gender_male">
                                            <i class="fas fa-mars me-1"></i>Male
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input @error('gender') is-invalid @enderror"
                                               type="radio"
                                               name="gender"
                                               id="gender_female"
                                               value="female"
                                               {{ old('gender', $user->gender) == 'female' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label" for="gender_female">
                                            <i class="fas fa-venus me-1"></i>Female
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @error('gender')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Privacy Notice -->
                        <div class="alert alert-light border">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1 text-black"></i>
                                <strong class="text-black">Privacy Notice:</strong> <p class="text-black">Your personal information is securely stored and used only for
                                event booking purposes. We do not share your information with third parties.
                                Once submitted, this information cannot be modified through your profile for security reasons.</p>
                            </small>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-check me-2"></i>Complete Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Help Text -->
            <div class="text-center mt-3">
                <small class="text-muted">
                    <i class="fas fa-question-circle me-1"></i>
                    Need help? <a href="mailto:support@aftereightevents.com">Contact Support</a>
                </small>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>
@endpush
@endsection
