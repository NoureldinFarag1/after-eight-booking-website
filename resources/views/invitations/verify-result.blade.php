@extends('layouts.app')

@section('title', 'Invitation Verification')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    @if($success)
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h2 class="text-success mb-3">Invitation Verified!</h2>
                    @else
                        <div class="mb-4">
                            <i class="bi bi-x-circle-fill text-danger" style="font-size: 4rem;"></i>
                        </div>
                        <h2 class="text-danger mb-3">Verification Failed</h2>
                    @endif

                    <p class="text-muted mb-4">{{ $message }}</p>

                    @if(isset($invitation) && $invitation->event)
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title">{{ $invitation->event->title }}</h5>
                                <p class="card-text">
                                    <strong>Date:</strong> {{ $invitation->event->event_date->format('F j, Y') }}<br>
                                    <strong>Time:</strong> {{ $invitation->event->event_time->format('g:i A') }}<br>
                                    <strong>Location:</strong> {{ $invitation->event->location }}
                                </p>
                                @if($success)
                                    <p class="text-success mb-0">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Welcome to the event!
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
