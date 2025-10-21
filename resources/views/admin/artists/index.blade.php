@extends('layouts.app')

@section('title','Artists')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h3 mb-0 d-flex align-items-center"><i data-lucide="music-3" class="me-2"></i>Artists</h1>
      <a href="{{ route('admin.artists.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Add Artist</a>
    </div>

    <div class="ae-card">
      <div class="ae-card-body">
        @if($artists->count())
          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr>
                  <th style="width:80px">Photo</th>
                  <th>Name</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($artists as $artist)
                  <tr>
                    <td>
                      <div class="rounded-circle overflow-hidden" style="width:48px;height:48px;background:#111;border:1px solid rgba(255,255,255,0.12);">
                        @if($artist->photo_url)
                          <img src="{{ Storage::url($artist->photo_url) }}" alt="{{ $artist->name }}" class="w-100 h-100" style="object-fit:cover;" />
                        @endif
                      </div>
                    </td>
                    <td>{{ $artist->name }}</td>
                    <td class="text-end">
                      <a href="{{ route('admin.artists.edit', $artist) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                      <form action="{{ route('admin.artists.destroy', $artist) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this artist?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                      </form>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="mt-3">{{ $artists->links() }}</div>
        @else
          <p class="text-muted mb-0">No artists yet.</p>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
