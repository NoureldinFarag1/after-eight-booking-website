@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Request #{{ $request->id }} - Status: {{ ucfirst($request->status) }}</h2>

    <p><strong>Event:</strong> {{ $request->event->title ?? 'N/A' }}</p>
    <p><strong>Submitted by:</strong> {{ $request->user->name ?? 'N/A' }}</p>
    <hr>
    <h4>Submitted Data</h4>
    <pre>{{ json_encode($request->payload, JSON_PRETTY_PRINT) }}</pre>

    @if(auth()->check() && auth()->id() === $request->user_id)
        <a href="{{ route('event_requests.index') }}" class="btn btn-secondary">My Requests</a>
    @endif
</div>
@endsection
