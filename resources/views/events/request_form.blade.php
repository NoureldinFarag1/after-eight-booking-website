@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Submit a request for: {{ $event->name }}</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('event-requests.store', $event) }}" method="POST">
        @csrf

        {{-- Example placeholders: you’ll replace names once client confirms --}}
        <div class="mb-3">
            <label for="field1" class="form-label">Field 1</label>
            <input type="text" name="field1" id="field1"
                   class="form-control @error('field1') is-invalid @enderror"
                   value="{{ old('field1') }}" required>
            @error('field1')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="field2" class="form-label">Field 2</label>
            <input type="text" name="field2" id="field2"
                   class="form-control @error('field2') is-invalid @enderror"
                   value="{{ old('field2') }}" required>
            @error('field2')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="field3" class="form-label">Field 3</label>
            <input type="text" name="field3" id="field3"
                   class="form-control @error('field3') is-invalid @enderror"
                   value="{{ old('field3') }}" required>
            @error('field3')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="field4" class="form-label">Field 4</label>
            <input type="text" name="field4" id="field4"
                   class="form-control @error('field4') is-invalid @enderror"
                   value="{{ old('field4') }}" required>
            @error('field4')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-warning w-100">
            <i class="bi bi-send"></i> Submit Request
        </button>
    </form>
</div>
@endsection