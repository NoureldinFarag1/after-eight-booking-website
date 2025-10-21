@extends('layouts.app')

@section('title','Add Artist')

@section('content')
<div class="row">
  <div class="col-lg-8">
    <div class="ae-card">
      <div class="ae-card-header"><h5 class="mb-0">New Artist</h5></div>
      <div class="ae-card-body">
        <form method="POST" action="{{ route('admin.artists.store') }}" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Photo (optional)</label>
            <input type="file" name="photo" class="form-control" accept="image/*">
            @error('photo')<div class="text-danger small">{{ $message }}</div>@enderror
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Create</button>
            <a href="{{ route('admin.artists.index') }}" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
