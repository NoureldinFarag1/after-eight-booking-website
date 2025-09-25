@extends('layouts.app')

@section('title', 'Events')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="bi bi-calendar-event me-2"></i>Upcoming Events
            </h1>

            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Create Event
                    </a>
                @endif
            @endauth
        </div>

        @if($events->count() > 0)
            <div class="row">
                @foreach($events as $event)
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 event-card">
                            @if($event->image_url)
                                <img src="{{ Storage::url($event->image_url) }}"
                                     class="card-img-top"
                                     alt="{{ $event->title }}"
                                     style="height: 200px; object-fit: cover;">
                            @else
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                     style="height: 200px;">
                                    <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                </div>
                            @endif

                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title">{{ $event->title }}</h5>
                                    <span class="badge status-badge
                                        @if($event->status->value === 'published') bg-success
                                        @elseif($event->status->value === 'draft') bg-secondary
                                        @elseif($event->status->value === 'cancelled') bg-danger
                                        @else bg-warning @endif">
                                        {{ ucfirst($event->status->value) }}
                                    </span>
                                </div>

                                <p class="card-text text-muted">
                                    {{ Str::limit($event->description, 100) }}
                                </p>

                                <div class="event-details mb-3">
                                    <small class="text-muted d-block">
                                        <i class="bi bi-calendar me-1"></i>
                                        {{ $event->event_date->format('M d, Y') }}
                                    </small>
                                    <small class="text-muted d-block">
                                        <i class="bi bi-clock me-1"></i>
                                        {{ $event->event_time->format('g:i A') }}
                                    </small>
                                    <small class="text-muted d-block">
                                        <i class="bi bi-geo-alt me-1"></i>
                                        {{ $event->location }}
                                    </small>
                                    <small class="text-muted d-block">
                                        <i class="bi bi-people me-1"></i>
                                        {{ $event->getAvailableSeatsAttribute() }} / {{ $event->capacity }} seats available
                                    </small>
                                </div>

                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            @php
                                                $types = $event->ticketTypes()->where('is_active', true)->orderBy('price')->get();
                                            @endphp
                                            @if($types->count() > 0)
                                                <span class="h6 text-muted mb-0">From</span>
                                                <span class="h5 text-primary mb-0">${{ number_format($types->min('price'), 2) }}</span>
                                            @else
                                                <span class="text-muted">Pricing will be announced</span>
                                            @endif
                                        </div>

                                        <div>
                                            <a href="{{ route('events.show', $event) }}"
                                               class="btn btn-outline-primary btn-sm">
                                                View Details
                                            </a>

                                            @auth
                                                @if(auth()->user()->isAdmin())
                                                    <a href="{{ route('admin.events.edit', $event) }}"
                                                       class="btn btn-outline-secondary btn-sm">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                @endif
                                            @endauth
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($event->isSoldOut())
                                <div class="card-footer bg-danger text-white text-center">
                                    <small><i class="bi bi-exclamation-triangle me-1"></i>Sold Out</small>
                                </div>
                            @elseif(!$event->isBookable())
                                <div class="card-footer bg-warning text-dark text-center">
                                    <small><i class="bi bi-exclamation-triangle me-1"></i>Not Available for Booking</small>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $events->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-calendar-x display-1 text-muted"></i>
                <h3 class="mt-3 text-muted">No Events Available</h3>
                <p class="text-muted">Check back later for upcoming events.</p>

                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>Create First Event
                        </a>
                    @endif
                @endauth
            </div>
        @endif
    </div>
</div>
@endsection
