<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Booking;
use App\Models\Event;
use Carbon\Carbon;
use DB;

class InsightsController extends Controller
{
    public function index(Request $request)
    {
        // Basic finance metrics
        $totalTickets = Ticket::count();
        $totalRevenue = Ticket::sum('price');
        $totalBookings = Booking::count();
        $totalEvents = Event::count();

        // Average tickets per event
        $avgTicketsPerEvent = $totalEvents > 0 ? round($totalTickets / $totalEvents, 2) : 0;

        // Tickets & revenue for last 30 days grouped by date
        $start = Carbon::now()->subDays(29)->startOfDay();
        $ticketsByDayQuery = Ticket::selectRaw("DATE(created_at) as date, COUNT(*) as tickets, COALESCE(SUM(price),0) as revenue")
            ->where('created_at', '>=', $start)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dates = $ticketsByDayQuery->pluck('date')->map(fn($d) => (string)$d)->toArray();
        $tickets = $ticketsByDayQuery->pluck('tickets')->toArray();
        $revenues = $ticketsByDayQuery->pluck('revenue')->toArray();

        // Top 5 events by ticket sales
        $topEventsByTickets = Event::select('id','title')
            ->withCount('tickets')
            ->orderByDesc('tickets_count')
            ->take(5)
            ->get();

        // Top 5 events by revenue
        $topEventsByRevenue = Event::select('id','title')
            ->withSum('tickets','price')
            ->orderByDesc('tickets_sum_price')
            ->take(5)
            ->get();

        // Highest-grossing event
        $highestGrossingEvent = Event::select('id','title')
            ->withSum('tickets','price')
            ->orderByDesc('tickets_sum_price')
            ->first();

        // Event with highest attendance
        $topAttendanceEvent = Event::select('id','title')
            ->withCount('tickets')
            ->orderByDesc('tickets_count')
            ->first();

        // Average ticket price
        $avgTicketPrice = $totalTickets > 0 ? round($totalRevenue / $totalTickets, 2) : 0;

        // Booking conversion rate (tickets/bookings)
        $conversionRate = $totalBookings > 0 ? round(($totalTickets / $totalBookings) * 100, 2) : 0;

        // Repeat customers (users with more than 1 booking)
        $repeatCustomers = Booking::select('user_id', DB::raw('COUNT(*) as bookings'))
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        return view('finance.insights', compact(
            'totalTickets', 'totalRevenue', 'totalBookings', 'totalEvents',
            'avgTicketsPerEvent', 'dates', 'tickets', 'revenues',
            'topEventsByTickets', 'topEventsByRevenue',
            'highestGrossingEvent', 'topAttendanceEvent',
            'avgTicketPrice', 'conversionRate', 'repeatCustomers'
        ));
    }
}
