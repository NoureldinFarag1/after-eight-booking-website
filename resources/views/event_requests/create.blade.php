@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Submit Request for Event: {{ $event->title ?? 'Event' }}</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('events.requests.store', $event->id) }}">
        @csrf

        <div class="mb-3">
            <label>Field 1</label>
            <input type="text" name="field_1" value="{{ old('field_1') }}" class="form-control" required>
            @error('field_1')<div class="text-danger">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label>Field 2</label>
            <input type="text" name="field_2" value="{{ old('field_2') }}" class="form-control" required>
            @error('field_2')<div class="text-danger">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label>Field 3</label>
            <textarea name="field_3" class="form-control" required>{{ old('field_3') }}</textarea>
            @error('field_3')<div class="text-danger">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label>Field 4</label>
            <textarea name="field_4" class="form-control" required>{{ old('field_4') }}</textarea>
            @error('field_4')<div class="text-danger">{{ $message }}</div>@enderror
        </div>

        <button class="btn btn-primary">Submit Request</button>
    </form>
</div>
@endsection
