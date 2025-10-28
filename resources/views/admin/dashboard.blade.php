@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('admin.events.create') }}" class="btn btn-cta">
                                <i data-lucide="plus-circle" class="me-1"></i>Create Event
                            </a>
                            <a href="{{ route('events.index') }}" class="btn btn-outline-primary">
                                <i data-lucide="list" class="me-1"></i>View All Events
                            </a>
                            <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary">
                                <i data-lucide="ticket" class="me-1"></i>View All Bookings
                            </a>
                            <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">
                                <i data-lucide="qr-code" class="me-1"></i>View All Tickets
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-red h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-muted mb-2">Total Events</h6>
                                <div class="metric-value text-primary mb-1">{{ $totalEvents }}</div>
                                <div class="metric-subtext">{{ $upcomingEvents }} upcoming</div>
                            </div>
                            <div class="align-self-center">
                                <div class="bg-gradient-red-light rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i data-lucide="calendar" class="icon-xl text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-red h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-muted mb-2">Total Bookings</h6>
                                <div class="metric-value text-primary mb-1">{{ $totalBookings }}</div>
                                <div class="metric-subtext">{{ $totalTickets }} tickets</div>
                            </div>
                            <div class="align-self-center">
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i data-lucide="ticket" class="icon-xl text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-muted mb-2">Scanned Tickets</h6>
                                <div class="metric-value mb-1">{{ $scannedTickets }}</div>
                                <div class="metric-subtext">{{ $totalTickets > 0 ? round(($scannedTickets / $totalTickets) * 100, 1) : 0 }}% scan rate</div>
                            </div>
                            <div class="align-self-center">
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i data-lucide="qr-code" class="icon-xl text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-muted mb-2">Total Revenue</h6>
                                <div class="metric-value mb-1">EGP {{ number_format($totalRevenue, 0) }}</div>
                                <div class="metric-subtext">{{ $activeOperators }}/{{ $totalOperators }} operators active</div>
                            </div>
                            <div class="align-self-center">
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i data-lucide="wallet" class="icon-xl text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Scan Activity Chart -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 text-dark">Daily Scan Activity (Last 7 Days)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="dailyScanChart" width="400" height="150"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 text-dark">Quick Stats</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 border-end">
                                <h4 class="text-dark mb-1">{{ $publishedEvents }}</h4>
                                <small class="text-muted">Published Events</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-dark mb-1">{{ $validTickets }}</h4>
                                <small class="text-muted">Valid Tickets</small>
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="row text-center">
                            <div class="col-6 border-end">
                                <h4 class="text-dark mb-1">{{ $pastEvents }}</h4>
                                <small class="text-muted">Past Events</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-dark mb-1">{{ $totalOperators }}</h4>
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
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-dark">Operator Performance</h5>
                        <span class="badge bg-primary">{{ $totalOperators }} Total Operators</span>
                    </div>
                    <div class="card-body">
                        @if($operatorStats->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover event-performance-table">
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
                                            <tr class="position-relative">
                                                <td class="position-relative">
                                                    <a href="{{ route('admin.staff.show', $operator['id']) }}" class="stretched-link" aria-label="View {{ $operator['name'] }}"></a>
                                                    <div>
                                                        <strong class="text-white">{{ $operator['name'] }}</strong>
                                                        <br><small class="text-muted">{{ $operator['email'] }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary fs-6">{{ $operator['total_scans'] }}</span>
                                                </td>
                                                <td>
                                                    @if($operator['last_scan'])
                                                        <small class="text-muted">{{ $operator['last_scan']->diffForHumans() }}</small>
                                                    @else
                                                        <small class="text-muted">Never</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($operator['recent_scans']->count() > 0)
                                                        <div class="small text-muted">
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
                                                    <small class="text-white">{{ $operator['member_since']->format('M j, Y') }}</small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i data-lucide="user-x" class="display-4 text-muted"></i>
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
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-dark">Event Performance</h5>
                        <a href="{{ route('events.index') }}" class="btn btn-sm btn-outline-primary">
                            View All Events
                        </a>
                    </div>
                    <div class="card-body">
                        @if($eventPerformance->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover event-performance-table">
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
                                        @foreach($eventPerformance->take(5) as $event)
                                            <tr>
                                                <td>
                                                    <strong class="text-white">{{ Str::limit($event['title'], 25) }}</strong>
                                                </td>
                                                <td class="text-white">{{ $event['date'] }}</td>
                                                <td class="text-white">{{ $event['capacity'] }}</td>
                                                <td>
                                                    <span class="badge metric-badge metric-badge--primary">{{ $event['tickets_sold'] }}</span>
                                                    <small class="text-muted">
                                                        ({{ $event['capacity'] > 0 ? round(($event['tickets_sold'] / $event['capacity']) * 100, 1) : 0 }}%)
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge metric-badge metric-badge--dark">{{ $event['tickets_scanned'] }}</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress me-2" style="width: 60px; height: 8px;">
                                                            <div class="progress-bar text-white {{ $event['attendance_rate'] >= 80 ? 'bg-success' : ($event['attendance_rate'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                                 style="width: {{ $event['attendance_rate'] }}%"></div>
                                                        </div>
                                                        <small class="text-white">{{ $event['attendance_rate'] }}%</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong class="text-white">EGP {{ number_format($event['revenue'], 0) }}</strong>
                                                </td>
                                                <td>
                                                    @php
                                                        $soldRate = $event['capacity'] > 0 ? ($event['tickets_sold'] / $event['capacity']) * 100 : 0;
                                                        $performanceLabel = 'Poor';
                                                        $performanceClass = 'performance-badge performance-badge--poor';

                                                        if ($soldRate >= 90) {
                                                            $performanceLabel = 'Excellent';
                                                            $performanceClass = 'performance-badge performance-badge--excellent';
                                                        } elseif ($soldRate >= 70) {
                                                            $performanceLabel = 'Good';
                                                            $performanceClass = 'performance-badge performance-badge--good';
                                                        } elseif ($soldRate >= 50) {
                                                            $performanceLabel = 'Average';
                                                            $performanceClass = 'performance-badge performance-badge--average';
                                                        }
                                                    @endphp
                                                    <span class="badge {{ $performanceClass }}">{{ $performanceLabel }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i data-lucide="trending-down" class="display-4 text-muted"></i>
                                <p class="mt-2 text-muted">No event performance data available.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Events -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-dark">Recent Events</h5>
                        <a href="{{ route('events.index') }}" class="btn btn-sm btn-outline-primary">
                            View All
                        </a>
                    </div>
                    <div class="card-body">
                        @if($recentEvents->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover recent-events-table">
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
                                                                 class="thumb me-2"
                                                                 >
                                                        @else
                                                            <div class="bg-light thumb-placeholder me-2 d-flex align-items-center justify-content-center">
                                                                <i class="bi bi-image text-muted"></i>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <strong class="text-white">{{ $event->title }}</strong>
                                                            <br>
                                                            <small class="text-muted">
                                                                @php
                                                                    $types = $event->ticketTypes()->where('is_active', true)->orderBy('price')->get();
                                                                @endphp
                                                                @if($types->count() > 0)
                                                                    From EGP {{ number_format($types->min('price'), 2) }}
                                                                @else
                                                                    Pricing TBA
                                                                @endif
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-white">{{ $event->event_date->format('M j, Y') }}</div>
                                                    <small class="text-muted">{{ $event->event_time->format('g:i A') }}</small>
                                                </td>
                                                <td class="text-muted">{{ Str::limit($event->location, 30) }}</td>
                                                <td>
                                                    <div class="text-white">{{ $event->getAvailableSeatsAttribute() }} / {{ $event->capacity }}</div>
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
                                                        <small class="text-muted">{{ $event->scan_rate }}%</small>
                                                    </div>
                                                    <small class="text-muted">{{ $event->scanned_tickets_count }}/{{ $event->tickets_count }}</small>
                                                </td>
                                                <td>
                                                    @php
                                                        $status = $event->status->value;
                                                        $pillClass = match($status) {
                                                            'published' => 'status-pill status-pill--published',
                                                            'draft' => 'status-pill status-pill--draft',
                                                            'cancelled' => 'status-pill status-pill--cancelled',
                                                            default => 'status-pill status-pill--pending'
                                                        };
                                                    @endphp
                                                    <span class="{{ $pillClass }}">
                                                        <span class="status-dot" aria-hidden="true"></span>
                                                        {{ ucfirst($status) }}
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
                                <a href="{{ route('admin.events.create') }}" class="btn btn-cta">
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
<script>
    // Daily Scan Activity Chart
    const dailyScanData = @json($dailyScanActivity);
    const ctx = document.getElementById('dailyScanChart').getContext('2d');
    const mqScheme = window.matchMedia('(prefers-color-scheme: dark)');
    const getChartColors = () => {
        const dark = mqScheme.matches;
        return {
            borderColor: 'rgb(37,99,235)', // blue-600
            backgroundColor: 'rgba(37,99,235, 0.15)', // area fill
            grid: dark ? 'rgba(255,255,255,0.12)' : 'rgba(0,0,0,0.08)',
            ticks: dark ? 'rgba(255,255,255,0.75)' : 'rgba(17,24,39,0.75)',
        };
    };
    const baseColors = getChartColors();

    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dailyScanData.map(d => d.date),
            datasets: [{
                label: 'Tickets Scanned',
                data: dailyScanData.map(d => d.scans),
                borderColor: baseColors.borderColor,
                backgroundColor: baseColors.backgroundColor,
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
                        stepSize: 1,
                        color: baseColors.ticks
                    },
                    grid: {
                        color: baseColors.grid
                    }
                },
                x: {
                    ticks: {
                        color: baseColors.ticks
                    },
                    grid: {
                        color: baseColors.grid
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

    // React to color scheme changes
    if (mqScheme.addEventListener) {
        mqScheme.addEventListener('change', () => {
            const c = getChartColors();
            chart.options.scales.x.ticks.color = c.ticks;
            chart.options.scales.y.ticks.color = c.ticks;
            chart.options.scales.x.grid.color = c.grid;
            chart.options.scales.y.grid.color = c.grid;
            chart.update();
        });
    } else if (mqScheme.addListener) {
        mqScheme.addListener(() => {
            const c = getChartColors();
            chart.options.scales.x.ticks.color = c.ticks;
            chart.options.scales.y.ticks.color = c.ticks;
            chart.options.scales.x.grid.color = c.grid;
            chart.options.scales.y.grid.color = c.grid;
            chart.update();
        });
    }
</script>
@endpush
@endsection
