@extends('layouts.app')

@section('title', 'Ticket Verification')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center">
                    @if($valid)
                        <div class="text-success mb-4">
                            <i class="fas fa-check-circle fa-4x"></i>
                        </div>
                        <h2 class="text-success">Valid Ticket</h2>
                        <p class="lead">{{ $message }}</p>

                        <div class="mt-4">
                            <h5>Ticket Details</h5>
                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Ticket Number:</strong></td>
                                        <td>{{ $ticket->ticket_number }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Event:</strong></td>
                                        <td>{{ $ticket->event->title }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Date:</strong></td>
                                        <td>{{ $ticket->event->event_date->format('F j, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Time:</strong></td>
                                        <td>{{ $ticket->event->event_time }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Location:</strong></td>
                                        <td>{{ $ticket->event->location }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            <span class="badge bg-{{ $ticket->status->value === 'valid' ? 'success' : ($ticket->status->value === 'used' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($ticket->status->value) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @if($ticket->type)
                                    <tr>
                                        <td><strong>Ticket Type:</strong></td>
                                        <td>{{ $ticket->type->name }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td><strong>Holder:</strong></td>
                                        <td>{{ $ticket->booking->user->name }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="text-danger mb-4">
                            <i class="fas fa-times-circle fa-4x"></i>
                        </div>
                        <h2 class="text-danger">Invalid Ticket</h2>
                        <p class="lead">{{ $message }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
