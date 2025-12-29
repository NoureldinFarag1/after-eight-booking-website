@extends('layouts.app')

@section('title', 'Already Responded')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i data-lucide="triangle-alert" class="text-warning" style="width:4rem;height:4rem;"></i>
                    </div>
                    <h2 class="text-warning mb-3">Already Responded</h2>
                    <p class="text-muted mb-4">
                        You have already {{ $response }} this invitation{{ $invitation->event ? ' to ' . $invitation->event->title : '' }}.
                    </p>

                    @if($invitation->event)
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title">{{ $invitation->event->title }}</h5>
                                <p class="card-text">
                                    <strong>Date:</strong> {{ $invitation->event->event_date->format('F j, Y') }}<br>
                                    <strong>Time:</strong> {{ $invitation->event->event_time->format('g:i A') }}<br>
                                    <strong>Location:</strong> {{ $invitation->event->location }}
                                </p>
                            </div>
                        </div>
                    @endif

                    <div class="mt-4">
                        <p class="text-muted small">
                            <i data-lucide="info" class="me-1"></i>
                            If you need to change your response, please contact the event organizer.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
