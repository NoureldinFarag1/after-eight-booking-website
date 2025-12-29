@extends('layouts.app')

@section('title', $event->title)

@section('content')
    @php
    $isAdmin = auth()->check() && auth()->user()->isAdmin();
    $isFinanceOfficer = auth()->check() && auth()->user()->isFinanceOfficer();
    $canViewFinanceInsights = $canViewFinanceInsights ?? false;
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
                                <li><a class="dropdown-item" href="{{ route('admin.events.export', $event) }}"><i class="bi bi-download me-2"></i>Export (Excel)</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><button type="button" class="dropdown-item text-danger" onclick="confirmDelete('{{ $event->id }}')"><i class="bi bi-trash me-2"></i>Delete Event</button></li>
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            @if ($canViewFinanceInsights)
                <h3 class="ae-section-title">Finance KPIs</h3>
                <div class="ae-card p-4 mb-4">
                    @php
                        $ticketsSoldKpi = data_get($insights, 'tickets_sold', 0);
                        $totalRevenueKpi = (float) data_get($insights, 'revenue', 0);
                        $avgTicketValue = $ticketsSoldKpi > 0 ? $totalRevenueKpi / $ticketsSoldKpi : 0;
                    @endphp
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="small text-white-50">Tickets Sold</div>
                            <div class="h4 mb-0">{{ number_format($ticketsSoldKpi) }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-white-50">Total Revenue</div>
                            <div class="h4 mb-0">EGP {{ number_format($totalRevenueKpi, 2) }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-white-50">Avg. Ticket Value</div>
                            <div class="h4 mb-0">EGP {{ number_format($avgTicketValue, 2) }}</div>
                        </div>
                    </div>

                    @php $ticketInsights = collect(data_get($insights, 'ticket_types', [])); @endphp
                    @if ($ticketInsights->isNotEmpty())
                        <hr class="my-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-semibold">Ticket Types Overview</div>
                            <div class="small text-white-50">Includes inactive ticket types</div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-dark table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col">Type</th>
                                        <th scope="col" class="text-center">Status</th>
                                        <th scope="col" class="text-end">Face Value</th>
                                        <th scope="col" class="text-end">Fee / Ticket</th>
                                        <th scope="col" class="text-end">Tickets Sold</th>
                                        <th scope="col" class="text-end">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ticketInsights as $typeInsight)
                                        @php
                                            $feeLabel = '—';
                                            $feeType = $typeInsight['fee_type'] ?? null;
                                            $feeAmount = $typeInsight['fee_amount'] ?? null;
                                            $perTicketFee = $typeInsight['per_ticket_fee'] ?? 0;

                                            if ($feeType === 'fixed' && !is_null($feeAmount)) {
                                                $feeLabel = 'EGP ' . number_format((float) $feeAmount, 2);
                                            } elseif ($feeType === 'percentage' && !is_null($feeAmount)) {
                                                $percentLabel = rtrim(rtrim(number_format((float) $feeAmount, 2), '0'), '.');
                                                $feeLabel = 'EGP ' . number_format((float) $perTicketFee, 2) . ' (' . $percentLabel . '%)';
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $typeInsight['name'] }}</td>
                                            <td class="text-center">
                                                <span class="badge {{ !empty($typeInsight['is_active']) ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ !empty($typeInsight['is_active']) ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="text-end">EGP {{ number_format((float) ($typeInsight['price'] ?? 0), 2) }}</td>
                                            <td class="text-end">{{ $feeLabel }}</td>
                                            <td class="text-end">{{ number_format((int) ($typeInsight['tickets_sold'] ?? 0)) }}</td>
                                            <td class="text-end">EGP {{ number_format((float) ($typeInsight['revenue'] ?? 0), 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <hr class="my-4">
                        <div class="text-white-50">No ticket sales have been recorded for this event yet.</div>
                    @endif
                </div>
            @endif

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
                        <div class="fw-semibold">{{ $event->location ?: 'To be announced' }}</div>
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
                <div class="mb-0 text-white-75 ae-rich-content">{!! $event->description !!}</div>
                @if ($event->terms_conditions)
                    <hr class="my-4">
                    <div>
                        <div class="fw-semibold mb-2">House Rules</div>
                        <div class="small text-white-50">{!! nl2br(e($event->terms_conditions)) !!}</div>
                    </div>
                @endif
            </div>

            @if(!$isFinanceOfficer)
                {{-- Tickets / Requests --}}
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
                                    <div class="text-end" style="min-width: 220px;">
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

                                            // Per-type remaining capacity (if capacity is set)
                                            $remainingForType = null;
                                            $typeSoldOut = false;
                                            if(!is_null($tt->capacity)){
                                                $soldCount = \App\Models\Ticket::where('event_id', $event->id)
                                                    ->where('ticket_type_id', $tt->id)
                                                    ->where('status', '!=', \App\Enums\TicketStatus::CANCELLED)
                                                    ->count();
                                                $remainingForType = max(0, $tt->capacity - $soldCount);
                                                $typeSoldOut = ($remainingForType <= 0);
                                            }
                                        @endphp

                                        @if($remainingForType !== null)
                                            <div class="small text-white-50 mb-2">
                                                @if($typeSoldOut)
                                                    No Tickets Left
                                                @endif
                                            </div>
                                        @endif

                                        @php
                                            $currentUser = auth()->user();
                                            $isStaffUser = $currentUser && $currentUser->isStaff();
                                            $canShowBuy = $event->isBookable() && !$typeSoldOut && !$isStaffUser;
                                        @endphp
                                        @if($canShowBuy)
                                            <a href="{{ route('bookings.create', [$event, 'ticket_type_id' => $tt->id]) }}" class="btn btn-primary btn-sm rounded-pill">Buy Now</a>
                                        @else
                                            <button class="btn btn-primary btn-sm rounded-pill" disabled aria-disabled="true">
                                                @if($isStaffUser)
                                                    Staff are unable to purchase tickets.
                                                @elseif(!$event->isBookable())
                                                    {{ $disabledLabel }}
                                                @else
                                                    Sold Out
                                                @endif
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="ae-card p-4 mb-4 text-white-50">Pricing will be announced.</div>
                    @endif
                @elseif($event->type === 'request')
                    <h3 class="ae-section-title">Request Access</h3>
                    @php $activeTypes = $event->ticketTypes()->where('is_active', true)->orderBy('price')->get(); @endphp
                    @if($activeTypes->count())
                        <div class="d-flex flex-column gap-3 mb-3">
                            @foreach($activeTypes as $tt)
                                <div class="ticket-card d-flex align-items-center p-3 rounded-3">
                                    <div class="flex-fill pe-3">
                                        <div class="fw-semibold">{{ $tt->name }}</div>
                                        @if(!empty($tt->description))
                                            <div class="small text-white-50">{{ $tt->description }}</div>
                                        @endif
                                    </div>
                                    <div class="text-end" style="min-width: 180px;">
                                        <div class="h5 mb-2">
                                            @if(!is_null($tt->price))
                                                EGP {{ number_format((float)$tt->price, 2) }}
                                            @else
                                                —
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @php
                            $isPast = $event->event_date < now()->toDateString();
                            $disabledLabel = null;
                            if(!$event->isBookable()){
                                if($isPast){
                                    $disabledLabel = 'Event Ended';
                                } elseif($event->isSoldOut()){
                                    $disabledLabel = 'Fully Allocated';
                                } elseif($event->status->value !== 'published'){
                                    $disabledLabel = 'Not Available';
                                } else {
                                    $disabledLabel = 'Unavailable';
                                }
                            }
                        @endphp
                        @if($event->isBookable())
                            <a href="{{ route('event-requests.create', $event) }}" class="btn btn-primary rounded-pill">
                                <i class="bi bi-envelope-plus me-1"></i> Request Ticket
                            </a>
                        @else
                            <button class="btn btn-outline-secondary rounded-pill" disabled>{{ $disabledLabel }}</button>
                        @endif
                    @else
                        <div class="ae-card p-4 mb-4 text-white-50">Ticket types will be announced.</div>
                    @endif
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
            @elseif($event->location)
                <h3 class="ae-section-title">Location</h3>
                <div class="ae-card p-4 mb-5">
                    <div class="d-flex align-items-start gap-3">
                        <i data-lucide="map-pin" class="text-white-50"></i>
                        <div>
                            <div class="fw-semibold">{{ $event->location }}</div>
                            @if($event->google_maps_url)
                                <a href="{{ $event->google_maps_url }}" target="_blank" rel="noopener" class="small text-white-50">Open in Google Maps</a>
                            @endif
                        </div>
                    </div>
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
