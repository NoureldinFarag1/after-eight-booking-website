<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    protected $redirectTo = '/login';
    protected $middleware = ['auth', 'role:admin'];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Event::query();

        // Only show published events to non-admin users
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            $query->published()->upcoming();
        }

        $events = $query->orderBy('event_date', 'asc')->paginate(12);

        return view('events.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('events.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'event_date' => 'required|date|after_or_equal:today',
            'event_time' => 'required|date_format:H:i',
            'type' => 'required|in:booking,request',
            'capacity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'status' => ['required', Rule::in(array_column(EventStatus::cases(), 'value'))],
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'terms_conditions' => 'nullable|string',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image_url'] = $request->file('image')->store('events', 'public');
        }

        $event = Event::create($validated);

        return redirect()
            ->route('events.show', $event)
            ->with('success', 'Event created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $event->load(['bookings.user', 'tickets']);

        return view('events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        return view('events.edit', compact('event'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'event_date' => 'required|date|after_or_equal:today',
            'event_time' => 'required|date_format:H:i',
            'capacity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'type' => 'required|in:booking,request',
            'status' => ['required', Rule::in(array_column(EventStatus::cases(), 'value'))],
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'terms_conditions' => 'nullable|string',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($event->image_url) {
                Storage::disk('public')->delete($event->image_url);
            }
            $validated['image_url'] = $request->file('image')->store('events', 'public');
        }

        $event->update($validated);

        return redirect()
            ->route('events.show', $event)
            ->with('success', 'Event updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        // Check if event has bookings
        if ($event->bookings()->count() > 0) {
            return redirect()
                ->route('events.index')
                ->with('error', 'Cannot delete event with existing bookings.');
        }

        // Delete image if exists
        if ($event->image_url) {
            Storage::disk('public')->delete($event->image_url);
        }

        $event->delete();

        return redirect()
            ->route('events.index')
            ->with('success', 'Event deleted successfully!');
    }

    /**
     * Admin dashboard view
     */
    public function dashboard()
    {
        // Basic Event Statistics
        $totalEvents = Event::query()->count();
        $publishedEvents = Event::query()->published()->count();
        $upcomingEvents = Event::query()->upcoming()->count();
        $pastEvents = Event::query()->past()->count();

        // Booking Statistics
        $totalBookings = Event::withCount('bookings')->get()->sum('bookings_count');
        $totalTickets = \App\Models\Ticket::count();
        $scannedTickets = \App\Models\Ticket::whereNotNull('scanned_at')->count();
        $validTickets = \App\Models\Ticket::where('status', \App\Enums\TicketStatus::VALID)->count();

        // Revenue Statistics
        $totalRevenue = \App\Models\Event::join('bookings', 'events.id', '=', 'bookings.event_id')
            ->sum('events.price');

        // Operator Statistics
        $operators = \App\Models\User::where('role', \App\Enums\Role::OPERATOR)
            ->withCount(['scannedTickets as total_scans'])
            ->get();

        $totalOperators = $operators->count();
        $activeOperators = $operators->where('total_scans', '>', 0)->count();

        // Recent Events with enhanced data
        $recentEvents = Event::query()
            ->withCount(['bookings', 'tickets'])
            ->with(['tickets' => function($query) {
                $query->whereNotNull('scanned_at');
            }])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($event) {
                $event->scanned_tickets_count = $event->tickets->count();
                $event->scan_rate = $event->tickets_count > 0
                    ? round(($event->scanned_tickets_count / $event->tickets_count) * 100, 1)
                    : 0;
                return $event;
            });

        // Event Performance Data (for charts)
        $eventPerformance = Event::query()
            ->withCount(['bookings', 'tickets'])
            ->with(['tickets' => function($query) {
                $query->whereNotNull('scanned_at');
            }])
            ->where('event_date', '>=', now()->subMonths(6))
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'date' => \Carbon\Carbon::parse($event->event_date)->format('M j'),
                    'capacity' => $event->capacity,
                    'bookings' => $event->bookings_count,
                    'tickets_sold' => $event->tickets_count,
                    'tickets_scanned' => $event->tickets->count(),
                    'revenue' => $event->price * $event->bookings_count,
                    'attendance_rate' => $event->tickets_count > 0
                        ? round(($event->tickets->count() / $event->tickets_count) * 100, 1)
                        : 0
                ];
            });

        // Operator Scan Statistics
        $operatorStats = \App\Models\User::where('role', \App\Enums\Role::OPERATOR)
            ->select('id', 'name', 'email', 'created_at')
            ->withCount(['scannedTickets as total_scans'])
            ->with(['scannedTickets' => function($query) {
                $query->select('scanned_by', 'scanned_at', 'event_id')
                      ->with('event:id,title')
                      ->latest('scanned_at')
                      ->limit(5);
            }])
            ->get()
            ->map(function ($operator) {
                $recentScans = $operator->scannedTickets;
                $todayScans = $operator->scannedTickets()
                    ->whereDate('scanned_at', today())
                    ->count();
                $thisWeekScans = $operator->scannedTickets()
                    ->whereBetween('scanned_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count();

                return [
                    'id' => $operator->id,
                    'name' => $operator->name,
                    'email' => $operator->email,
                    'total_scans' => $operator->total_scans,
                    'today_scans' => $todayScans,
                    'week_scans' => $thisWeekScans,
                    'recent_scans' => $recentScans,
                    'last_scan' => $recentScans->first()?->scanned_at,
                    'member_since' => $operator->created_at
                ];
            });

        // Daily Scan Activity (last 7 days)
        $dailyScanActivity = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $scans = \App\Models\Ticket::whereDate('scanned_at', $date)->count();
            $dailyScanActivity[] = [
                'date' => $date->format('M j'),
                'scans' => $scans
            ];
        }

        return view('admin.dashboard', compact(
            'totalEvents',
            'publishedEvents',
            'upcomingEvents',
            'pastEvents',
            'totalBookings',
            'totalTickets',
            'scannedTickets',
            'validTickets',
            'totalRevenue',
            'totalOperators',
            'activeOperators',
            'recentEvents',
            'eventPerformance',
            'operatorStats',
            'dailyScanActivity'
        ));
    }
}
