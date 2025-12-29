@extends('layouts.app')

@section('title','Edit Artist')

@section('content')
<div class="row">
  <div class="col-lg-8">
    <div class="ae-card">
      <div class="ae-card-header"><h5 class="mb-0">Edit Artist</h5></div>
      <div class="ae-card-body">
        <form method="POST" action="{{ route('admin.artists.update', $artist) }}" enctype="multipart/form-data">
          @csrf
          @method('PUT')
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $artist->name) }}" required>
            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Photo</label>
            <div class="d-flex align-items-center gap-3 mb-2">
              <div class="rounded-circle overflow-hidden" style="width:64px;height:64px;background:#111;border:1px solid rgba(255,255,255,0.12);">
                @if($artist->photo_url)
                  <img src="{{ Storage::url($artist->photo_url) }}" alt="{{ $artist->name }}" class="w-100 h-100" style="object-fit:cover;" />
                @endif
              </div>
              @if($artist->photo_url)
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="1" id="remove_photo" name="remove_photo">
                  <label class="form-check-label" for="remove_photo">Remove current photo</label>
                </div>
              @endif
            </div>
            <input type="file" name="photo" class="form-control" accept="image/*">
            @error('photo')<div class="text-danger small">{{ $message }}</div>@enderror
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('admin.artists.index') }}" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
