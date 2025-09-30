@extends('layouts.app')

@section('title', 'Edit Event')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-pencil me-2"></i>Edit Event: {{ $event->title }}
                </h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.events.update', $event) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="title" class="form-label">Event Title *</label>
                            <input type="text"
                                   class="form-control @error('title') is-invalid @enderror"
                                   id="title"
                                   name="title"
                                   value="{{ old('title', $event->title) }}"
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
                                <option value="draft" {{ old('status', $event->status->value) === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status', $event->status->value) === 'published' ? 'selected' : '' }}>Published</option>
                                <option value="cancelled" {{ old('status', $event->status->value) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
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
                                  required>{{ old('description', $event->description) }}</textarea>
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
                               value="{{ old('location', $event->location) }}"
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
                                   value="{{ old('event_date', $event->event_date->format('Y-m-d')) }}"
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
                                   value="{{ old('event_time', $event->event_time->format('H:i')) }}"
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
                                   value="{{ old('capacity', $event->capacity) }}"
                                   min="1"
                                   required>
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($event->bookings->sum('quantity') > 0)
                                <div class="form-text text-warning">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    {{ $event->bookings->sum('quantity') }} seats already booked
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pricing</label>
                            <div class="form-text">
                                Pricing is managed via Ticket Types. Use <a href="{{ route('admin.events.ticket-types.index', $event) }}">Manage Ticket Types</a> to set prices and capacities. You can leave the base price blank.
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="type">Event Type</label>
                        <select name="type" id="type" class="form-control" required>
                            <option value="booking" {{ $event->type == 'booking' ? 'selected' : '' }}>Booking</option>
                            <option value="request" {{ $event->type == 'request' ? 'selected' : '' }}>Request</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Event Image</label>
                        @if($event->image_url)
                            <div class="mb-2">
                                <img src="{{ Storage::url($event->image_url) }}"
                                     alt="Current image"
                                     class="img-thumbnail"
                                     style="max-height: 200px;">
                                <div class="form-text">Current image</div>
                            </div>
                        @endif
                        <input type="file"
                               class="form-control @error('image') is-invalid @enderror"
                               id="image"
                               name="image"
                               accept="image/*">
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            @if($event->image_url)
                                Upload a new image to replace the current one.
                            @endif
                            Recommended size: 800x600 pixels. Max file size: 2MB
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="terms_conditions" class="form-label">Terms & Conditions</label>
                        <textarea class="form-control @error('terms_conditions') is-invalid @enderror"
                                  id="terms_conditions"
                                  name="terms_conditions"
                                  rows="3">{{ old('terms_conditions', $event->terms_conditions) }}</textarea>
                        @error('terms_conditions')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($event->bookings->count() > 0)
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Note:</strong> This event has {{ $event->bookings->count() }} booking(s).
                            Some changes may affect existing bookings.
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Finance Officer</label>
                        <select name="finance_officer_id" class="form-control">
                            <option value="">-- None --</option>
                            @foreach($financeOfficers as $officer)
                                <option value="{{ $officer->id }}" {{ old('finance_officer_id', isset($event) ? $event->finance_officer_id : '') == $officer->id ? 'selected' : '' }}>
                                    {{ $officer->name }} ({{ $officer->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('events.show', $event) }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Back to Event
                        </a>

                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Update Event
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const container = document.getElementById('ticket-types-container');
        const addBtn = document.getElementById('add-ticket-type');

        function toggleTypes() {
            const isBooking = typeSelect.value === 'booking';
            container.style.display = isBooking ? '' : 'none';
        }
        if (typeSelect) {
            typeSelect.addEventListener('change', toggleTypes);
            toggleTypes();
        }

        if (addBtn) {
            addBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const list = document.getElementById('ticket-types-list');
                const idx = list.children.length;
                const row = document.createElement('div');
                row.className = 'row g-2 align-items-end mb-2';
                row.innerHTML = `
                    <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="ticket_types[${idx}][name]" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Price</label>
                        <input type="number" name="ticket_types[${idx}][price]" class="form-control" min="0" step="0.01" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Capacity (optional)</label>
                        <input type="number" name="ticket_types[${idx}][capacity]" class="form-control" min="0">
                    </div>
                    <div class="col-md-2">
                        <div class="form-check form-switch">
                            <input type="hidden" name="ticket_types[${idx}][is_active]" value="0">
                            <input class="form-check-input" type="checkbox" name="ticket_types[${idx}][is_active]" value="1" checked>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                    <div class="col-md-1 text-end">
                        <button class="btn btn-outline-danger btn-sm" onclick="this.closest('.row').remove()" title="Remove">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                `;
                list.appendChild(row);
            });
        }
    });
</script>
@endpush

@push('after-content')
<div class="row justify-content-center mt-3" id="ticket-types-container" style="display:none;">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Add Ticket Types</strong>
                <button id="add-ticket-type" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus"></i> Add Type
                </button>
            </div>
            <div class="card-body">
                <div id="ticket-types-list"></div>
                <div class="form-text">Existing types can be managed from the “Manage Ticket Types” page.</div>
            </div>
        </div>
    </div>
</div>
@endpush
@endsection
