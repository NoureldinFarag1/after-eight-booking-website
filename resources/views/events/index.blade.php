@extends('layouts.app')

@section('title', 'Events')

@section('content')
@php
    // Count active filters (exclude sort so user sees semantic filters only)
    $activeFilterCount = collect($filters ?? [])->filter(function($v,$k){
        return in_array($k,['q','type','status','date_from','date_to','capacity_min','capacity_max']) && $v !== null && $v !== '';
    })->count();
@endphp

<div class="row">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 d-flex align-items-center">
                <i class="bi bi-calendar-event me-2"></i>Upcoming Events
            </h1>
            <div class="d-flex gap-2">
                <!-- Mobile: Filters button -->
                <button class="btn btn-outline-secondary d-inline-flex d-md-none align-items-center" type="button" data-bs-toggle="offcanvas" data-bs-target="#eventFiltersOffcanvas" aria-controls="eventFiltersOffcanvas">
                    <i class="bi bi-funnel me-1"></i>
                    Filters
                    @if($activeFilterCount>0)
                        <span class="badge bg-primary ms-2">{{ $activeFilterCount }}</span>
                    @endif
                </button>
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.events.create') }}" class="btn btn-primary d-none d-md-inline-flex">
                            <i class="bi bi-plus-circle me-1"></i>Create Event
                        </a>
                    @endif
                @endauth
            </div>
        </div>

        <!-- Desktop / md+ inline filters -->
        <form method="get" class="card mb-4 border-0 shadow-sm d-none d-md-block">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Search</label>
                        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="Title or location">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Type</label>
                        <select name="type" class="form-select">
                            <option value="">Any</option>
                            <option value="booking" @selected(($filters['type'] ?? '')==='booking')>Booking</option>
                            <option value="request" @selected(($filters['type'] ?? '')==='request')>Request</option>
                        </select>
                    </div>
                    @if(($isAdmin ?? false))
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Any</option>
                            @foreach($statusOptions as $st)
                                <option value="{{ $st }}" @selected(($filters['status'] ?? '')===$st)>{{ ucfirst($st) }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Date From</label>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Date To</label>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control">
                    </div>
                    @if(($isAdmin ?? false))
                        <div class="col-md-2">
                            <label class="form-label small text-muted">Min Capacity</label>
                            <input type="number" name="capacity_min" value="{{ $filters['capacity_min'] ?? '' }}" class="form-control" min="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted">Max Capacity</label>
                            <input type="number" name="capacity_max" value="{{ $filters['capacity_max'] ?? '' }}" class="form-control" min="0">
                        </div>
                    @endif
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Sort</label>
                        <select name="sort" class="form-select">
                            <option value="date_asc" @selected(($filters['sort'] ?? '')==='date_asc')>Date ↑</option>
                            <option value="date_desc" @selected(($filters['sort'] ?? '')==='date_desc')>Date ↓</option>
                            <option value="created_desc" @selected(($filters['sort'] ?? '')==='created_desc')>Newest Created</option>
                            <option value="capacity_desc" @selected(($filters['sort'] ?? '')==='capacity_desc')>Capacity ↓</option>
                            <option value="capacity_asc" @selected(($filters['sort'] ?? '')==='capacity_asc')>Capacity ↑</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-primary w-100" type="submit"><i class="bi bi-search me-1"></i>Filter</button>
                        <a href="{{ route('events.index') }}" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                    </div>
                </div>
            </div>
        </form>

        <!-- Mobile Offcanvas Filters -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="eventFiltersOffcanvas" aria-labelledby="eventFiltersOffcanvasLabel">
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title" id="eventFiltersOffcanvasLabel"><i class="bi bi-funnel me-1"></i>Filters</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <form method="get" class="d-flex flex-column gap-3">
                    <div>
                        <label class="form-label small text-muted">Search</label>
                        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="Title or location">
                    </div>
                    <div>
                        <label class="form-label small text-muted">Type</label>
                        <select name="type" class="form-select">
                            <option value="">Any</option>
                            <option value="booking" @selected(($filters['type'] ?? '')==='booking')>Booking</option>
                            <option value="request" @selected(($filters['type'] ?? '')==='request')>Request</option>
                        </select>
                    </div>
                    @if(($isAdmin ?? false))
                        <div>
                            <label class="form-label small text-muted">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Any</option>
                                @foreach($statusOptions as $st)
                                    <option value="{{ $st }}" @selected(($filters['status'] ?? '')===$st)>{{ ucfirst($st) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="d-flex gap-2">
                        <div class="flex-fill">
                            <label class="form-label small text-muted">Date From</label>
                            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control">
                        </div>
                        <div class="flex-fill">
                            <label class="form-label small text-muted">Date To</label>
                            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control">
                        </div>
                    </div>
                    @if(($isAdmin ?? false))
                        <div class="d-flex gap-2">
                            <div class="flex-fill">
                                <label class="form-label small text-muted">Min Capacity</label>
                                <input type="number" name="capacity_min" value="{{ $filters['capacity_min'] ?? '' }}" class="form-control" min="0">
                            </div>
                            <div class="flex-fill">
                                <label class="form-label small text-muted">Max Capacity</label>
                                <input type="number" name="capacity_max" value="{{ $filters['capacity_max'] ?? '' }}" class="form-control" min="0">
                            </div>
                        </div>
                    @endif
                    <div>
                        <label class="form-label small text-muted">Sort</label>
                        <select name="sort" class="form-select">
                            <option value="date_asc" @selected(($filters['sort'] ?? '')==='date_asc')>Date ↑</option>
                            <option value="date_desc" @selected(($filters['sort'] ?? '')==='date_desc')>Date ↓</option>
                            <option value="created_desc" @selected(($filters['sort'] ?? '')==='created_desc')>Newest Created</option>
                            <option value="capacity_desc" @selected(($filters['sort'] ?? '')==='capacity_desc')>Capacity ↓</option>
                            <option value="capacity_asc" @selected(($filters['sort'] ?? '')==='capacity_asc')>Capacity ↑</option>
                        </select>
                    </div>
                    <div class="d-flex gap-2 mt-2">
                        <button class="btn btn-primary w-100" type="submit"><i class="bi bi-search me-1"></i>Apply</button>
                        <a href="{{ route('events.index') }}" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                    </div>
                </form>
            </div>
            <div class="offcanvas-footer border-top p-3 small text-muted d-flex justify-content-between">
                <span>{{ $activeFilterCount }} active {{ Str::plural('filter',$activeFilterCount) }}</span>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="offcanvas">Close</button>
            </div>
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
                                    @if(auth()->check() && auth()->user()->isAdmin())
                                        <small class="text-muted d-block">
                                            <i class="bi bi-people me-1"></i>
                                            {{ $event->getAvailableSeatsAttribute() }} / {{ $event->capacity }} seats available
                                        </small>
                                    @else
                                        @if($event->isSoldOut())
                                            <small class="text-danger d-block"><i class="bi bi-people me-1"></i>Sold Out</small>
                                        @endif
                                    @endif
                                </div>

                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 event-card-footer-actions">
                                        <div class="price-mini d-flex flex-column align-items-start justify-content-center">
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
                                        <div class="ms-auto d-flex flex-wrap gap-2 align-items-center justify-content-end action-buttons" style="min-width: 180px;">
                                            <a href="{{ route('events.show', $event) }}"
                                               class="btn btn-outline-primary btn-sm @if($event->isSoldOut() && !auth()->user()?->isAdmin()) disabled @endif"
                                               @if($event->isSoldOut() && !auth()->user()?->isAdmin())
                                                   tabindex="-1" aria-disabled="true"
                                               @endif
                                            >
                                                View
                                            </a>

                                            @auth
                                                @if(auth()->user()->isAdmin())
                                                    <a href="{{ route('admin.events.edit', $event) }}"
                                                       class="btn btn-outline-secondary btn-sm" title="Edit Event">
                                                        <i class="bi bi-pencil me-1"></i><span class="d-none d-xl-inline">Edit</span>
                                                    </a>
                                                    @if(in_array($event->status->value, ['draft','published']))
                                                        <form method="POST" action="{{ route('admin.events.toggle-publish', $event) }}" class="d-inline publish-toggle-form" data-event-id="{{ $event->id }}" data-current-status="{{ $event->status->value }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-sm publish-toggle-btn {{ $event->status->value==='draft' ? 'btn-success' : 'btn-outline-warning' }}" data-publish-btn
                                                                    data-status="{{ $event->status->value }}"
                                                                    aria-live="polite"
                                                                    aria-label="{{ $event->status->value==='draft' ? 'Publish event' : 'Revert event to draft' }}">
                                                                <span class="btn-label" data-label-publish="Publish" data-label-draft="Revert">
                                                                    @if($event->status->value==='draft')
                                                                        <i class="bi bi-upload me-1"></i>Publish
                                                                    @else
                                                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Revert
                                                                    @endif
                                                                </span>
                                                                <span class="spinner-border spinner-border-sm d-none align-middle" role="status" aria-hidden="true"></span>
                                                            </button>
                                                        </form>
                                                    @endif
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
            <div class="d-flex justify-content-center mt-4">
                <x-pagination :paginator="$events->appends(request()->query())" />
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
