@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Submit a Request for {{ $event->name }}</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('event-requests.store', $event) }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="field1">Field 1</label>
            <input type="text" name="field1" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="field2">Field 2</label>
            <input type="text" name="field2" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="field3">Field 3</label>
            <input type="text" name="field3" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="field4">Field 4</label>
            <input type="text" name="field4" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Submit Request</button>
    </form>
</div>
@endsection