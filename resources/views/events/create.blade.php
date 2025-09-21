@extends('layouts.app')

@section('title', 'Create Event')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-plus-circle me-2"></i>Create New Event
                </h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="title" class="form-label">Event Title *</label>
                            <input type="text"
                                   class="form-control @error('title') is-invalid @enderror"
                                   id="title"
                                   name="title"
                                   value="{{ old('title') }}"
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status"
                                    name="status"
                                    required>
                                <option value="">Select Status</option>
                                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                                <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description"
                                  name="description"
                                  rows="4"
                                  required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="location" class="form-label">Location *</label>
                        <input type="text"
                               class="form-control @error('location') is-invalid @enderror"
                               id="location"
                               name="location"
                               value="{{ old('location') }}"
                               required>
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="event_date" class="form-label">Event Date *</label>
                            <input type="date"
                                   class="form-control @error('event_date') is-invalid @enderror"
                                   id="event_date"
                                   name="event_date"
                                   value="{{ old('event_date') }}"
                                   min="{{ date('Y-m-d') }}"
                                   required>
                            @error('event_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="event_time" class="form-label">Event Time *</label>
                            <input type="time"
                                   class="form-control @error('event_time') is-invalid @enderror"
                                   id="event_time"
                                   name="event_time"
                                   value="{{ old('event_time') }}"
                                   required>
                            @error('event_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="capacity" class="form-label">Capacity *</label>
                            <input type="number"
                                   class="form-control @error('capacity') is-invalid @enderror"
                                   id="capacity"
                                   name="capacity"
                                   value="{{ old('capacity') }}"
                                   min="1"
                                   required>
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price ($) *</label>
                            <input type="number"
                                   class="form-control @error('price') is-invalid @enderror"
                                   id="price"
                                   name="price"
                                   value="{{ old('price') }}"
                                   min="0"
                                   step="0.01"
                                   required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Enter 0 for free events</div>
                        </div>
                    </div>

                
                    <div class="form-group">
                        <label for="type">Event Type</label>
                        <select name="type" id="type" class="form-control" required>
                            <option value="booking">Booking</option>
                            <option value="request">Request</option>
                        </select>
                    </div>    

                    <div class="mb-3">
                        <label for="image" class="form-label">Event Image</label>
                        <input type="file"
                               class="form-control @error('image') is-invalid @enderror"
                               id="image"
                               name="image"
                               accept="image/*">
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Recommended size: 800x600 pixels. Max file size: 2MB</div>
                    </div>

                    <div class="mb-3">
                        <label for="terms_conditions" class="form-label">Terms & Conditions</label>
                        <textarea class="form-control @error('terms_conditions') is-invalid @enderror"
                                  id="terms_conditions"
                                  name="terms_conditions"
                                  rows="3">{{ old('terms_conditions') }}</textarea>
                        @error('terms_conditions')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('events.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Back to Events
                        </a>

                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Create Event
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
