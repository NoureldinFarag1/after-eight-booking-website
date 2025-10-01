@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Finance Insights</h1>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card"><div class="card-body text-center">
                <h5>Total Tickets</h5>
                <p>{{ $totalTickets }}</p>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card"><div class="card-body text-center">
                <h5>Total Revenue</h5>
                <p>${{ number_format($totalRevenue, 2) }}</p>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card"><div class="card-body text-center">
                <h5>Total Bookings</h5>
                <p>{{ $totalBookings }}</p>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card"><div class="card-body text-center">
                <h5>Total Events</h5>
                <p>{{ $totalEvents }}</p>
            </div></div>
        </div>
    </div>

    <!-- Extra Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card"><div class="card-body text-center">
                <h6>Avg Tickets / Event</h6>
                <p>{{ $avgTicketsPerEvent }}</p>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card"><div class="card-body text-center">
                <h6>Avg Ticket Price</h6>
                <p>${{ $avgTicketPrice }}</p>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card"><div class="card-body text-center">
                <h6>Conversion Rate</h6>
                <p>{{ $conversionRate }}%</p>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card"><div class="card-body text-center">
                <h6>Repeat Customers</h6>
                <p>{{ $repeatCustomers }}</p>
            </div></div>
        </div>
    </div>

    <!-- Highlights -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card"><div class="card-body">
                <h6>Highest Grossing Event</h6>
                <p>
                    {{ $highestGrossingEvent?->title ?? 'N/A' }}
                    ( ${{ number_format($highestGrossingEvent?->tickets_sum_price ?? 0, 2) }} )
                </p>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card"><div class="card-body">
                <h6>Best Attendance Event</h6>
                <p>
                    {{ $topAttendanceEvent?->title ?? 'N/A' }}
                    ( {{ $topAttendanceEvent?->tickets_count ?? 0 }} tickets )
                </p>
            </div></div>
        </div>
    </div>

    <!-- Charts -->
    <div class="card mb-4">
        <div class="card-body">
            <h5>Tickets & Revenue (Last 30 Days)</h5>
            <canvas id="ticketsChart"></canvas>
        </div>
    </div>

    <!-- Top Events -->
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h5>Top 5 Events by Tickets</h5>
                    <ul>
                        @foreach($topEventsByTickets as $event)
                            <li>{{ $event->title }} ({{ $event->tickets_count }} tickets)</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h5>Top 5 Events by Revenue</h5>
                    <ul>
                        @foreach($topEventsByRevenue as $event)
                            <li>{{ $event->title }} (${{ number_format($event->tickets_sum_price, 2) }})</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('ticketsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($dates),
            datasets: [
                {
                    label: 'Tickets',
                    data: @json($tickets),
                    borderColor: 'blue',
                    fill: false
                },
                {
                    label: 'Revenue',
                    data: @json($revenues),
                    borderColor: 'green',
                    fill: false,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            stacked: false,
            scales: {
                y: { type: 'linear', display: true, position: 'left' },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });
</script>
@endsection
