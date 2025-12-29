@extends('layouts.app')

@section('title', 'Invitation Response')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    @if($response === 'accepted')
                        <div class="mb-4">
                            <i data-lucide="circle-check" class="text-success" style="width:4rem;height:4rem;"></i>
                        </div>
                        <h2 class="text-success mb-3">Invitation Accepted!</h2>
                        <p class="text-muted mb-4">
                            Thank you for accepting the invitation{{ $invitation->event ? ' to ' . $invitation->event->title : '' }}.
                        </p>
                    @else
                        <div class="mb-4">
                            <i data-lucide="circle-x" class="text-danger" style="width:4rem;height:4rem;"></i>
                        </div>
                        <h2 class="text-danger mb-3">Invitation Declined</h2>
                        <p class="text-muted mb-4">
                            Your response has been recorded. Thank you for letting us know.
                        </p>
                    @endif

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

                    @if($response === 'accepted' && $invitation->event)
                        <div class="mt-4">
                            <p class="text-muted small">
                                <i data-lucide="info" class="me-1"></i>
                                You may receive additional details about the event via email.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
