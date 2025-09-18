@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4">
            <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
        </h1>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Events</h6>
                                <h2 class="mb-0">{{ $totalEvents }}</h2>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-calendar-event" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Published Events</h6>
                                <h2 class="mb-0">{{ $publishedEvents }}</h2>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Upcoming Events</h6>
                                <h2 class="mb-0">{{ $upcomingEvents }}</h2>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-clock" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Bookings</h6>
                                <h2 class="mb-0">{{ $totalBookings }}</h2>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-ticket-perforated" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Create Event
                            </a>
                            <a href="{{ route('events.index') }}" class="btn btn-outline-primary">
                                <i class="bi bi-list me-1"></i>View All Events
                            </a>
                            <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-ticket-perforated me-1"></i>View All Bookings
                            </a>
                            <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-qr-code me-1"></i>View All Tickets
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Events -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Events</h5>
                        <a href="{{ route('events.index') }}" class="btn btn-sm btn-outline-primary">
                            View All
                        </a>
                    </div>
                    <div class="card-body">
                        @if($recentEvents->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Event</th>
                                            <th>Date & Time</th>
                                            <th>Location</th>
                                            <th>Capacity</th>
                                            <th>Bookings</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentEvents as $event)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($event->image_url)
                                                            <img src="{{ Storage::url($event->image_url) }}"
                                                                 alt="{{ $event->title }}"
                                                                 class="rounded me-2"
                                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                                        @else
                                                            <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center"
                                                                 style="width: 40px; height: 40px;">
                                                                <i class="bi bi-image text-muted"></i>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <strong>{{ $event->title }}</strong>
                                                            <br>
                                                            <small class="text-muted">
                                                                ${{ number_format($event->price, 2) }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>{{ $event->event_date->format('M j, Y') }}</div>
                                                    <small class="text-muted">{{ $event->event_time->format('g:i A') }}</small>
                                                </td>
                                                <td>{{ Str::limit($event->location, 30) }}</td>
                                                <td>
                                                    <div>{{ $event->getAvailableSeatsAttribute() }} / {{ $event->capacity }}</div>
                                                    <small class="text-muted">available</small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        {{ $event->bookings_count ?? 0 }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge status-badge
                                                        @if($event->status->value === 'published') bg-success
                                                        @elseif($event->status->value === 'draft') bg-secondary
                                                        @elseif($event->status->value === 'cancelled') bg-danger
                                                        @else bg-warning @endif">
                                                        {{ ucfirst($event->status->value) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('events.show', $event) }}"
                                                           class="btn btn-outline-primary">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.events.edit', $event) }}"
                                                           class="btn btn-outline-secondary">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-calendar-x display-4 text-muted"></i>
                                <p class="mt-2 text-muted">No events created yet.</p>
                                <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-1"></i>Create Your First Event
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
