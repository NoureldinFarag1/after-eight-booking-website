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
                                <small>{{ $upcomingEvents }} upcoming</small>
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
                                <h6 class="card-title">Total Bookings</h6>
                                <h2 class="mb-0">{{ $totalBookings }}</h2>
                                <small>{{ $totalTickets }} tickets</small>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-ticket-perforated" style="font-size: 2rem;"></i>
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
                                <h6 class="card-title">Scanned Tickets</h6>
                                <h2 class="mb-0">{{ $scannedTickets }}</h2>
                                <small>{{ $totalTickets > 0 ? round(($scannedTickets / $totalTickets) * 100, 1) : 0 }}% scan rate</small>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-qr-code-scan" style="font-size: 2rem;"></i>
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
                                <h6 class="card-title">Total Revenue</h6>
                                <h2 class="mb-0">${{ number_format($totalRevenue, 0) }}</h2>
                                <small>{{ $activeOperators }}/{{ $totalOperators }} operators active</small>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-currency-dollar" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Scan Activity Chart -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Daily Scan Activity (Last 7 Days)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="dailyScanChart" width="400" height="150"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Stats</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 border-end">
                                <h4 class="text-primary">{{ $publishedEvents }}</h4>
                                <small class="text-muted">Published Events</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-success">{{ $validTickets }}</h4>
                                <small class="text-muted">Valid Tickets</small>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6 border-end">
                                <h4 class="text-info">{{ $pastEvents }}</h4>
                                <small class="text-muted">Past Events</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-warning">{{ $totalOperators }}</h4>
                                <small class="text-muted">Total Operators</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Operator Performance -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Operator Performance</h5>
                        <span class="badge bg-primary">{{ $totalOperators }} Total Operators</span>
                    </div>
                    <div class="card-body">
                        @if($operatorStats->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Operator</th>
                                            <th>Total Scans</th>
                                            <th>Today</th>
                                            <th>This Week</th>
                                            <th>Last Scan</th>
                                            <th>Recent Activity</th>
                                            <th>Member Since</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($operatorStats as $operator)
                                            <tr>
                                                <td>
                                                    <div>
                                                        <strong>{{ $operator['name'] }}</strong>
                                                        <br><small class="text-muted">{{ $operator['email'] }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary fs-6">{{ $operator['total_scans'] }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $operator['today_scans'] > 0 ? 'bg-success' : 'bg-light text-dark' }}">
                                                        {{ $operator['today_scans'] }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $operator['week_scans'] > 0 ? 'bg-info' : 'bg-light text-dark' }}">
                                                        {{ $operator['week_scans'] }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($operator['last_scan'])
                                                        <small>{{ $operator['last_scan']->diffForHumans() }}</small>
                                                    @else
                                                        <small class="text-muted">Never</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($operator['recent_scans']->count() > 0)
                                                        <div class="small">
                                                            @foreach($operator['recent_scans']->take(2) as $scan)
                                                                <div class="text-muted">
                                                                    {{ $scan->event->title ?? 'Unknown Event' }}
                                                                    <small>({{ $scan->scanned_at->format('M j, g:i A') }})</small>
                                                                </div>
                                                            @endforeach
                                                            @if($operator['recent_scans']->count() > 2)
                                                                <small class="text-muted">+{{ $operator['recent_scans']->count() - 2 }} more</small>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <small class="text-muted">No recent activity</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small>{{ $operator['member_since']->format('M j, Y') }}</small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-person-x display-4 text-muted"></i>
                                <p class="mt-2 text-muted">No operators found.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Performance -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Event Performance</h5>
                        <a href="{{ route('events.index') }}" class="btn btn-sm btn-outline-primary">
                            View All Events
                        </a>
                    </div>
                    <div class="card-body">
                        @if($eventPerformance->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Event</th>
                                            <th>Date</th>
                                            <th>Capacity</th>
                                            <th>Tickets Sold</th>
                                            <th>Scanned</th>
                                            <th>Attendance Rate</th>
                                            <th>Revenue</th>
                                            <th>Performance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($eventPerformance->take(10) as $event)
                                            <tr>
                                                <td>
                                                    <strong>{{ Str::limit($event['title'], 25) }}</strong>
                                                </td>
                                                <td>{{ $event['date'] }}</td>
                                                <td>{{ $event['capacity'] }}</td>
                                                <td>
                                                    <span class="badge bg-primary">{{ $event['tickets_sold'] }}</span>
                                                    <small class="text-muted">
                                                        ({{ $event['capacity'] > 0 ? round(($event['tickets_sold'] / $event['capacity']) * 100, 1) : 0 }}%)
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">{{ $event['tickets_scanned'] }}</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress me-2" style="width: 60px; height: 8px;">
                                                            <div class="progress-bar {{ $event['attendance_rate'] >= 80 ? 'bg-success' : ($event['attendance_rate'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                                 style="width: {{ $event['attendance_rate'] }}%"></div>
                                                        </div>
                                                        <small>{{ $event['attendance_rate'] }}%</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong>${{ number_format($event['revenue'], 0) }}</strong>
                                                </td>
                                                <td>
                                                    @php
                                                        $soldRate = $event['capacity'] > 0 ? ($event['tickets_sold'] / $event['capacity']) * 100 : 0;
                                                    @endphp
                                                    @if($soldRate >= 90)
                                                        <span class="badge bg-success">Excellent</span>
                                                    @elseif($soldRate >= 70)
                                                        <span class="badge bg-primary">Good</span>
                                                    @elseif($soldRate >= 50)
                                                        <span class="badge bg-warning">Average</span>
                                                    @else
                                                        <span class="badge bg-danger">Poor</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-graph-down display-4 text-muted"></i>
                                <p class="mt-2 text-muted">No event performance data available.</p>
                            </div>
                        @endif
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
                            <a href="{{ route('tickets.scan') }}" class="btn btn-outline-success">
                                <i class="bi bi-upc-scan me-1"></i>Scan Tickets
                            </a>

                            {{-- Send Invitation Button (redirects to Create Invitation page) --}}
                            @if(Auth::check() && Auth::user()->role === \App\Enums\Role::ADMIN)
                                <a href="{{ route('invitations.store') }}" class="btn btn-success">
                                    <i class="bi bi-envelope-open me-1"></i>Send Invitation
                                </a>
                            @endif
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
                                            <th>Scan Rate</th>
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
                                                                @php
                                                                    $types = $event->ticketTypes()->where('is_active', true)->orderBy('price')->get();
                                                                @endphp
                                                                @if($types->count() > 0)
                                                                    From ${{ number_format($types->min('price'), 2) }}
                                                                @else
                                                                    Pricing TBA
                                                                @endif
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
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress me-2" style="width: 50px; height: 6px;">
                                                            <div class="progress-bar {{ $event->scan_rate >= 80 ? 'bg-success' : ($event->scan_rate >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                                 style="width: {{ $event->scan_rate }}%"></div>
                                                        </div>
                                                        <small>{{ $event->scan_rate }}%</small>
                                                    </div>
                                                    <small class="text-muted">{{ $event->scanned_tickets_count }}/{{ $event->tickets_count }}</small>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Daily Scan Activity Chart
    const dailyScanData = @json($dailyScanActivity);
    const ctx = document.getElementById('dailyScanChart').getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dailyScanData.map(d => d.date),
            datasets: [{
                label: 'Tickets Scanned',
                data: dailyScanData.map(d => d.scans),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
</script>
@endpush
@endsection
