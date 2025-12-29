@extends('layouts.app')

@section('title', 'Ticket Types')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-0">Ticket Types for: {{ $event->title }}</h1>
        <small class="text-muted">Event Date: {{ $event->event_date?->format('Y-m-d') }}</small>
    </div>
    <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-outline-secondary">Event</a>
  </div>

  @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if($errors->any())
      <div class="alert alert-danger">
          <ul class="mb-0">
              @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
              @endforeach
          </ul>
      </div>
  @endif

  @php
      $allocated = $event->ticketTypes()->whereNotNull('capacity')->sum('capacity');
      $remaining = max(0, $event->capacity - $allocated);
  @endphp

  <div class="row">
      <div class="col-md-5">
          <div class="card mb-4">
              <div class="card-header"><strong>Add Ticket Type</strong></div>
              <div class="card-body">
                  <div class="alert alert-info py-2">
                      <div class="d-flex justify-content-between">
                          <span>Event capacity:</span><strong>{{ $event->capacity }}</strong>
                      </div>
                      <div class="d-flex justify-content-between">
                          <span>Allocated to types:</span><strong>{{ $allocated }}</strong>
                      </div>
                      <div class="d-flex justify-content-between">
                          <span>Remaining to allocate:</span><strong>{{ $remaining }}</strong>
                      </div>
                  </div>
                  <form method="POST" action="{{ route('admin.events.ticket-types.store', $event) }}">
                      @csrf
                      <div class="mb-3">
                          <label class="form-label">Name</label>
                          <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                      </div>
                      <div class="mb-3">
                          <label class="form-label">Description</label>
                          <input type="text" name="description" class="form-control" value="{{ old('description') }}" required>
                      </div>
                      <div class="row">
                          <div class="col-md-6 mb-3">
                              <label class="form-label">Price</label>
                              <input type="number" min="0" step="0.01" name="price" class="form-control" value="{{ old('price') }}" required>
                          </div>
                          <div class="col-md-6 mb-3">
                              <label class="form-label">Capacity</label>
                              <input type="number" min="0" max="{{ $remaining }}" name="capacity" class="form-control" value="{{ old('capacity') }}" required>
                              <div class="form-text">Remaining available: {{ $remaining }}</div>
                          </div>
                      </div>
                      <div class="row">
                          <div class="col-md-6 mb-3">
                              <label class="form-label">Fee Type</label>
                              <select name="fee_type" class="form-select" id="create_fee_type">
                                  <option value="" {{ old('fee_type') === null ? 'selected' : '' }}>No fee</option>
                                  <option value="percentage" {{ old('fee_type')==='percentage' ? 'selected' : '' }}>Percentage %</option>
                                  <option value="fixed" {{ old('fee_type')==='fixed' ? 'selected' : '' }}>Fixed Amount</option>
                              </select>
                          </div>
                          <div class="col-md-6 mb-3">
                              <label class="form-label">Fee Amount</label>
                              <input type="number" step="0.01" min="0" name="fee_amount" class="form-control" value="{{ old('fee_amount') }}" placeholder="e.g. 5 for 5% or 10 EGP" id="create_fee_amount">
                              <div class="form-text">If percentage, value must be 0–100.</div>
                          </div>
                      </div>
                      <div class="form-check form-switch mb-3">
                          <input type="hidden" name="is_active" value="0">
                          <input class="form-check-input" type="checkbox" id="is_active_create" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                          <label class="form-check-label" for="is_active_create">Active</label>
                      </div>
                      <button class="btn btn-primary">Create</button>
                  </form>
              </div>
          </div>
      </div>

      <div class="col-md-7">
          <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                  <strong>Existing Types</strong>
                  <span class="badge bg-secondary">{{ $types->total() }}</span>
              </div>
              <div class="table-responsive">
                  <table class="table table-hover mb-0">
                      <thead>
                          <tr>
                              <th>Name</th>
                              <th>Base Price</th>
                              <th>Fee</th>
                              <th>Total (Incl Fee)</th>
                              <th>Capacity</th>
                              <th>Status</th>
                              <th class="text-end">Actions</th>
                          </tr>
                      </thead>
                      <tbody>
                          @forelse($types as $type)
                              <tr>
                                  <td>
                                      <div class="fw-semibold text-white">{{ $type->name }}</div>
                                      <div class="text-muted small">{{ $type->description }}</div>
                                  </td>
                                  @php
                                      $base = (float)$type->price;
                                      $feeCalc = $type->calculateFee();
                                      $totalWithFee = $base + $feeCalc;
                                  @endphp
                                  <td class="text-white">EGP {{ number_format($base, 2) }}</td>
                                  <td class=text-muted>
                                      @if($type->fee_type)
                                          {{ $type->fee_type === 'percentage' ? $type->fee_amount.'%' : 'EGP '.number_format((float)$type->fee_amount,2) }}
                                          <div class="text-muted small">= EGP {{ number_format($feeCalc,2) }}</div>
                                      @else
                                          <span class="text-muted">— (0)</span>
                                      @endif
                                  </td>
                                  <td><strong class="text-muted">EGP {{ number_format($totalWithFee,2) }}</strong></td>
                                  <td class="text-muted">{{ $type->capacity ?? '—' }}</td>
                                  <td>
                                      <span class="badge {{ $type->is_active ? 'bg-success' : 'bg-secondary' }}">
                                          {{ $type->is_active ? 'Active' : 'Inactive' }}
                                      </span>
                                  </td>
                                  <td class="text-end">
                                      <div class="d-inline-flex gap-1">
                                          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#edit-{{ $type->id }}">Edit</button>
                                          <form method="POST" action="{{ route('admin.events.ticket-types.destroy', [$event, $type]) }}" onsubmit="return confirm('Delete this ticket type?');">
                                              @csrf
                                              @method('DELETE')
                                              <button class="btn btn-sm btn-outline-danger text-danger">Delete</button>
                                          </form>
                                      </div>
                                  </td>
                              </tr>
                              <tr class="collapse" id="edit-{{ $type->id }}">
                                  <td colspan="5">
                                      <form method="POST" action="{{ route('admin.events.ticket-types.update', [$event, $type]) }}" class="border rounded-3 p-3 ticket-type-edit-card">
                                          @csrf
                                          @method('PUT')
                                          <div class="row g-3">
                                              <div class="col-md-4">
                                                  <label class="form-label">Name</label>
                                                  <input type="text" name="name" class="form-control" value="{{ old('name', $type->name) }}" required>
                                              </div>
                                              <div class="col-md-8">
                                                  <label class="form-label">Description</label>
                                                  <input type="text" name="description" class="form-control" value="{{ old('description', $type->description) }}">
                                              </div>
                                              <div class="col-md-3">
                                                  <label class="form-label">Price</label>
                                                  <input type="number" min="0" step="0.01" name="price" class="form-control" value="{{ old('price', $type->price) }}" required>
                                              </div>
                                              <div class="col-md-3">
                                                  <label class="form-label">Capacity</label>
                                                  <input type="number" min="0" max="{{ $remaining + ($type->capacity ?? 0) }}" name="capacity" class="form-control" value="{{ old('capacity', $type->capacity) }}">
                                              </div>
                                              <div class="col-md-3">
                                                  <label class="form-label">Fee Type</label>
                                                  <select name="fee_type" class="form-select">
                                                      <option value="" {{ old('fee_type') === null ? 'selected' : '' }}>No fee</option>
                                                      <option value="percentage" {{ old('fee_type', $type->fee_type)==='percentage' ? 'selected' : '' }}>Percentage %</option>
                                                      <option value="fixed" {{ old('fee_type', $type->fee_type)==='fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                                  </select>
                                              </div>
                                              <div class="col-md-3">
                                                  <label class="form-label">Fee Amount</label>
                                                  <input type="number" step="0.01" min="0" name="fee_amount" class="form-control" value="{{ old('fee_amount', $type->fee_amount) }}" placeholder="0">
                                                  <div class="form-text">% 0–100 if percentage</div>
                                              </div>
                                              <div class="col-md-12 d-flex align-items-end mt-2">
                                                  <div class="form-check form-switch">
                                                      <input type="hidden" name="is_active" value="0">
                                                      <input class="form-check-input" type="checkbox" id="is_active_{{ $type->id }}" name="is_active" value="1" {{ old('is_active', $type->is_active ? 1 : 0) ? 'checked' : '' }}>
                                                      <label class="form-check-label" for="is_active_{{ $type->id }}">Active</label>
                                                  </div>
                                              </div>
                                          </div>
                                          <div class="mt-3">
                                              <button class="btn btn-primary">Save</button>
                                          </div>
                                      </form>
                                  </td>
                              </tr>
                          @empty
                              <tr>
                                  <td colspan="5" class="text-center text-muted py-4">No ticket types yet.</td>
                              </tr>
                          @endforelse
                      </tbody>
                  </table>
              </div>
              <div class="card-footer">{{ $types->links() }}</div>
          </div>
      </div>
  </div>
@endsection
