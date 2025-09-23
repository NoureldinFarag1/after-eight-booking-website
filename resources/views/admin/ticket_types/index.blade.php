@extends('layouts.app')

@section('title', 'Ticket Types')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-0">Ticket Types for: {{ $event->title }}</h1>
        <small class="text-muted">Event Date: {{ $event->event_date?->format('Y-m-d') }}</small>
    </div>
    <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-outline-secondary">Back to Event</a>
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

  <div class="row">
      <div class="col-md-5">
          <div class="card mb-4">
              <div class="card-header"><strong>Add Ticket Type</strong></div>
              <div class="card-body">
                  <form method="POST" action="{{ route('admin.events.ticket-types.store', $event) }}">
                      @csrf
                      <div class="mb-3">
                          <label class="form-label">Name</label>
                          <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                      </div>
                      <div class="mb-3">
                          <label class="form-label">Description (optional)</label>
                          <input type="text" name="description" class="form-control" value="{{ old('description') }}">
                      </div>
                      <div class="row">
                          <div class="col-md-6 mb-3">
                              <label class="form-label">Price</label>
                              <input type="number" min="0" step="0.01" name="price" class="form-control" value="{{ old('price') }}" required>
                          </div>
                          <div class="col-md-6 mb-3">
                              <label class="form-label">Capacity (optional)</label>
                              <input type="number" min="0" name="capacity" class="form-control" value="{{ old('capacity') }}">
                          </div>
                      </div>
                      <div class="form-check form-switch mb-3">
                          <input type="hidden" name="is_active" value="0">
                          <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                          <label class="form-check-label" for="is_active">Active</label>
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
                              <th>Price</th>
                              <th>Capacity</th>
                              <th>Status</th>
                              <th class="text-end">Actions</th>
                          </tr>
                      </thead>
                      <tbody>
                          @forelse($types as $type)
                              <tr>
                                  <td>
                                      <div class="fw-semibold">{{ $type->name }}</div>
                                      <div class="text-muted small">{{ $type->description }}</div>
                                  </td>
                                  <td>${{ number_format($type->price, 2) }}</td>
                                  <td>{{ $type->capacity ?? '—' }}</td>
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
                                              <button class="btn btn-sm btn-outline-danger">Delete</button>
                                          </form>
                                      </div>
                                  </td>
                              </tr>
                              <tr class="collapse" id="edit-{{ $type->id }}">
                                  <td colspan="5">
                                      <form method="POST" action="{{ route('admin.events.ticket-types.update', [$event, $type]) }}" class="border rounded p-3 bg-light">
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
                                              <div class="col-md-4">
                                                  <label class="form-label">Price</label>
                                                  <input type="number" min="0" step="0.01" name="price" class="form-control" value="{{ old('price', $type->price) }}" required>
                                              </div>
                                              <div class="col-md-4">
                                                  <label class="form-label">Capacity</label>
                                                  <input type="number" min="0" name="capacity" class="form-control" value="{{ old('capacity', $type->capacity) }}">
                                              </div>
                                              <div class="col-md-4 d-flex align-items-end">
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
