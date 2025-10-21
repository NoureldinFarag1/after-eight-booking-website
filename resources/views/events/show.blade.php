@extends('layouts.app')

@section('title', $event->title)

@section('content')
    @php
        $types = $event->ticketTypes()->where('is_active', true)->orderBy('price')->get();
        $isAdmin = auth()->check() && auth()->user()->isAdmin();
        $isFinanceOfficer = auth()->check() && auth()->user()->isFinanceOfficer();
    @endphp
    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">
            {{-- HERO --}}
            <div class="mb-4">
                <div class="ae-hero position-relative">
                    @if ($event->image_url)
                        <img src="{{ Storage::url($event->image_url) }}" alt="{{ $event->title }}" class="w-100 h-100">
                    @endif
                </div>
            </div>

            {{-- Header + Actions --}}
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h1 class="h3 mb-1">{{ $event->title }}</h1>
                    <div class="text-white-50 small">Organized by After Eight</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if($event->layout_image_url)
                        <button type="button" class="btn btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#layoutModal">
                            <i class="bi bi-aspect-ratio me-1"></i> Venue Layout
                        </button>
                    @endif
                    @if ($isAdmin)
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="manageEventDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-gear-fill me-1"></i> Manage
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="manageEventDropdown">
                                <li><a class="dropdown-item" href="{{ route('invitations.create', ['event_id' => $event->id]) }}"><i class="bi bi-envelope-open me-2"></i>Send Invitation</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.events.edit', $event) }}"><i class="bi bi-pencil me-2"></i>Edit Event</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.events.ticket-types.index', $event) }}"><i class="bi bi-ticket-detailed me-2"></i>Manage Tickets</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><button type="button" class="dropdown-item text-danger" onclick="confirmDelete('{{ $event->id }}')"><i class="bi bi-trash me-2"></i>Delete Event</button></li>
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Facts --}}
            <div class="ae-card p-3 mb-4">
                <div class="row g-0">
                    <div class="col-md-4 p-3 border-end">
                        <div class="small text-white-50">From</div>
                        <div class="fw-semibold">
                            {{ $event->event_date->format('D M j') }} @ {{ $event->event_time->format('g:i A') }}
                        </div>
                    </div>
                    <div class="col-md-5 p-3 border-end">
                        <div class="small text-white-50">Location</div>
                        <div class="fw-semibold">{{ $event->location }}</div>
                    </div>
                    <div class="col-md-3 p-3">
                        <div class="small text-white-50">Status</div>
                        <span class="badge status-badge @if ($event->status->value === 'published') bg-success @elseif($event->status->value === 'draft') bg-secondary @elseif($event->status->value === 'cancelled') bg-danger @else bg-warning @endif">
                            {{ ucfirst($event->status->value) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- About --}}
            <h3 class="ae-section-title">About Event</h3>
            <div class="ae-card p-4 mb-4">
                <p class="mb-0 text-white-75">{{ $event->description }}</p>
                @if ($event->terms_conditions)
                    <hr class="my-4">
                    <div>
                        <div class="fw-semibold mb-2">House Rules</div>
                        <div class="small text-white-50">{!! nl2br(e($event->terms_conditions)) !!}</div>
                    </div>
                @endif
            </div>

            {{-- Tickets --}}
            @if($event->type === 'booking')
                <h3 class="ae-section-title">Tickets</h3>
                @php $activeTypes = $event->ticketTypes()->where('is_active', true)->orderBy('price')->get(); @endphp
                @if($activeTypes->count())
                    <div class="d-flex flex-column gap-3 mb-4">
                        @foreach($activeTypes as $tt)
                            <div class="ticket-card d-flex align-items-center p-3 rounded-3">
                                <div class="flex-fill pe-3">
                                    <div class="fw-semibold">{{ $tt->name }}</div>
                                    @if(!empty($tt->description))
                                        <div class="small text-white-50">{{ $tt->description }}</div>
                                    @endif
                                </div>
                                <div class="text-end" style="min-width: 180px;">
                                    <div class="h5 mb-2">EGP {{ number_format((float)($tt->price ?? 0), 2) }}</div>
                                    @php
                                        $isPast = $event->event_date < now()->toDateString();
                                        $disabledLabel = null;
                                        if(!$event->isBookable()){
                                            if($isPast){
                                                $disabledLabel = 'Event Ended';
                                            } elseif($event->isSoldOut()){
                                                $disabledLabel = 'Sold Out';
                                            } elseif($event->status->value !== 'published'){
                                                $disabledLabel = 'Not Available';
                                            } else {
                                                $disabledLabel = 'Unavailable';
                                            }
                                        }
                                    @endphp
                                    @if($event->isBookable())
                                        <a href="{{ route('bookings.create', $event) }}" class="btn btn-primary btn-sm rounded-pill">Buy Now</a>
                                    @else
                                        <button class="btn btn-outline-secondary btn-sm rounded-pill" disabled>{{ $disabledLabel }}</button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="ae-card p-4 mb-4 text-white-50">Pricing will be announced.</div>
                @endif
            @endif

            {{-- Lineup --}}
            @php $attachedArtists = $event->getRelationValue('artists') ?? collect(); @endphp
            @if($attachedArtists->count())
                <h3 class="ae-section-title">Lineup</h3>
                <div class="d-flex flex-wrap gap-4 mb-4">
                    @foreach($attachedArtists as $artist)
                        <div class="text-center" style="width:124px;">
                            <div class="rounded-circle overflow-hidden mx-auto mb-2" style="width:96px;height:96px;background:#111;border:1px solid rgba(255,255,255,0.12);">
                                @if($artist->photo_url)
                                    <img src="{{ Storage::url($artist->photo_url) }}" alt="{{ $artist->name }}" class="w-100 h-100" style="object-fit:cover;">
                                @endif
                            </div>
                            <div class="small text-white-75 text-uppercase fw-semibold">{{ $artist->name }}</div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Map --}}
            @php $coords = $event->coordinates; @endphp
            @if($coords)
                <h3 class="ae-section-title">Location on Map</h3>
                <div class="ratio ratio-16x9 ae-card overflow-hidden mb-5">
                    <iframe src="https://www.google.com/maps?q={{ $coords['lat'] }},{{ $coords['lng'] }}&z=15&output=embed" style="border:0;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            @else
                <h3 class="ae-section-title">Location</h3>
                <div class="ae-card p-4 mb-5 text-white-50">
                    Location details are coming soon. Check back later or contact support for directions.
                </div>
            @endif

            {{-- Venue Layout Modal --}}
            @if($event->layout_image_url)
                <div class="modal fade" id="layoutModal" tabindex="-1" aria-labelledby="layoutModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
                        <div class="modal-content bg-black text-white">
                            <div class="modal-header border-0">
                                <h5 class="modal-title" id="layoutModalLabel">Venue Layout</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">
                                <img src="{{ Storage::url($event->layout_image_url) }}" alt="Venue layout for {{ $event->title }}" class="w-100 h-auto d-block" style="max-height:80vh; object-fit:contain;" />
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

    @if ($isAdmin)
        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this event? This action cannot be undone.</p>
                        <p class="text-danger"><strong>Note:</strong> Events with existing bookings cannot be deleted.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form id="deleteForm" method="POST" class="d-inline"
                            action="">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Yes, Delete Event</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                window.confirmDelete = function(eventId) {
                    const form = document.getElementById('deleteForm');
                    form.action = '/admin/events/' + eventId;
                    new bootstrap.Modal(document.getElementById('deleteModal')).show();
                };
            });
        </script>
    @endif
@endsection
