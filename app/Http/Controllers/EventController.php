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
    public function index(Request $request)
    {
        $query = Event::query();

    /** @var User|null $user */
    $user = Auth::user();
        $isAdmin = $user && $user->isAdmin();

        // Base scope for non-admins: only published upcoming events
        if (!$isAdmin) {
            $query->published()->upcoming();
        }

        // Search (title/location)
        if ($search = trim($request->input('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Status filter (admin only)
        if ($isAdmin && ($status = $request->input('status'))) {
            $validStatuses = collect(EventStatus::cases())->pluck('value')->all();
            if (in_array($status, $validStatuses, true)) {
                $query->where('status', $status);
            }
        }

        // Type filter (booking|request)
        if ($type = $request->input('type')) {
            if (in_array($type, ['booking', 'request'], true)) {
                $query->where('type', $type);
            }
        }

        // Date range filters
        if ($from = $request->input('date_from')) {
            $query->whereDate('event_date', '>=', $from);
        }
        if ($to = $request->input('date_to')) {
            $query->whereDate('event_date', '<=', $to);
        }

        // Capacity filters (admin only)
        if ($isAdmin) {
            if ($minCap = $request->input('capacity_min')) {
                if (is_numeric($minCap)) {
                    $query->where('capacity', '>=', (int)$minCap);
                }
            }
            if ($maxCap = $request->input('capacity_max')) {
                if (is_numeric($maxCap)) {
                    $query->where('capacity', '<=', (int)$maxCap);
                }
            }
        }

        // Sorting options
        $sort = $request->input('sort', 'date_asc');
        switch ($sort) {
            case 'date_desc':
                $query->orderBy('event_date', 'desc');
                break;
            case 'created_desc':
                $query->latest();
                break;
            case 'capacity_desc':
                $query->orderBy('capacity', 'desc');
                break;
            case 'capacity_asc':
                $query->orderBy('capacity', 'asc');
                break;
            case 'date_asc':
            default:
                $query->orderBy('event_date', 'asc');
        }

        $events = $query->paginate(12)->appends($request->query());

        $filters = [
            'q' => $request->input('q'),
            'status' => $request->input('status'),
            'type' => $request->input('type'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'capacity_min' => $request->input('capacity_min'),
            'capacity_max' => $request->input('capacity_max'),
            'sort' => $sort,
        ];

        $statusOptions = collect(EventStatus::cases())->pluck('value')->all();

        return view('events.index', compact('events', 'filters', 'statusOptions', 'isAdmin'));
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
            'status' => ['required', Rule::in(array_column(EventStatus::cases(), 'value'))],
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'terms_conditions' => 'nullable|string',
            // Optional ticket types on create
            'ticket_types' => 'nullable|array|max:50',
            'ticket_types.*.name' => 'required_with:ticket_types|string|max:100|distinct',
            'ticket_types.*.price' => 'required_with:ticket_types|numeric|min:0',
            'ticket_types.*.capacity' => 'nullable|integer|min:0',
            'ticket_types.*.is_active' => 'nullable|in:0,1',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image_url'] = $request->file('image')->store('events', 'public');
        }

        // Validate that initial ticket type capacities (if any) do not exceed event capacity
        $types = $request->input('ticket_types', []);
        if (!empty($types) && $request->input('type') === 'booking') {
            $sumCap = collect($types)
                ->sum(function ($t) {
                    return isset($t['capacity']) && $t['capacity'] !== '' ? (int)$t['capacity'] : 0;
                });
            if ($sumCap > (int)$validated['capacity']) {
                return back()
                    ->withErrors(['ticket_types' => 'Sum of ticket type capacities ('. $sumCap .') exceeds event capacity ('. $validated['capacity'] .').'])
                    ->withInput();
            }
        }

        $event = Event::create($validated);

        // Create ticket types if provided and event is bookable type
        $types = $request->input('ticket_types', []);
        if (!empty($types) && $request->input('type') === 'booking') {
            $payload = collect($types)
                ->filter(fn($t) => isset($t['name']) && $t['name'] !== '')
                ->map(function ($t) {
                    return [
                        'name' => $t['name'],
                        'description' => $t['description'] ?? null,
                        'price' => (float)($t['price'] ?? 0),
                        'capacity' => isset($t['capacity']) && $t['capacity'] !== '' ? (int)$t['capacity'] : null,
                        'is_active' => isset($t['is_active']) ? (int)$t['is_active'] : 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->values()->all();

            if (!empty($payload)) {
                $event->ticketTypes()->insert($payload);
            }
        }

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
            'type' => 'required|in:booking,request',
            'status' => ['required', Rule::in(array_column(EventStatus::cases(), 'value'))],
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'terms_conditions' => 'nullable|string',
            // Optional new ticket types to add on update
            'ticket_types' => 'nullable|array|max:50',
            'ticket_types.*.name' => 'required_with:ticket_types|string|max:100|distinct',
            'ticket_types.*.price' => 'required_with:ticket_types|numeric|min:0',
            'ticket_types.*.capacity' => 'nullable|integer|min:0',
            'ticket_types.*.is_active' => 'nullable|in:0,1',
        ]);

        // Prevent lowering event capacity below already booked seats or allocated ticket-type capacity
        $bookedSeats = $event->bookings()->sum('quantity');
        $allocatedCapacity = $event->ticketTypes()->whereNotNull('capacity')->sum('capacity');
        if ((int)$validated['capacity'] < max($bookedSeats, $allocatedCapacity)) {
            return back()
                ->withErrors(['capacity' => 'Capacity cannot be less than already booked seats ('. $bookedSeats .') or allocated ticket type capacity ('. $allocatedCapacity .').'])
                ->withInput();
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($event->image_url) {
                Storage::disk('public')->delete($event->image_url);
            }
            $validated['image_url'] = $request->file('image')->store('events', 'public');
        }

        $event->update($validated);

        // Optionally add new ticket types when editing (manage existing via dedicated page)
        $types = $request->input('ticket_types', []);
        if (!empty($types) && $request->input('type') === 'booking') {
            // Validate that new types do not exceed remaining allocatable capacity
            $allocated = $event->ticketTypes()->whereNotNull('capacity')->sum('capacity');
            $remaining = max(0, (int)$validated['capacity'] - $allocated);
            $sumNew = collect($types)->sum(function ($t) {
                return isset($t['capacity']) && $t['capacity'] !== '' ? (int)$t['capacity'] : 0;
            });
            if ($sumNew > $remaining) {
                return back()
                    ->withErrors(['ticket_types' => 'New ticket type capacities ('. $sumNew .') exceed remaining allocatable capacity ('. $remaining .').'])
                    ->withInput();
            }
            $payload = collect($types)
                ->filter(fn($t) => isset($t['name']) && $t['name'] !== '')
                ->map(function ($t) {
                    return [
                        'name' => $t['name'],
                        'description' => $t['description'] ?? null,
                        'price' => (float)($t['price'] ?? 0),
                        'capacity' => isset($t['capacity']) && $t['capacity'] !== '' ? (int)$t['capacity'] : null,
                        'is_active' => isset($t['is_active']) ? (int)$t['is_active'] : 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->values()->all();

            if (!empty($payload)) {
                $event->ticketTypes()->insert($payload);
            }
        }

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
     * Quick status toggle (admin index action) – publish or revert to draft.
     * Only allowed for draft|published states; cancelled/completed immutable here.
     */
    public function togglePublish(Event $event)
    {
        if (!Auth::user()?->isAdmin()) {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            abort(403);
        }

        if (in_array($event->status->value, [EventStatus::CANCELLED->value, EventStatus::COMPLETED->value], true)) {
            $msg = 'Cannot change status of a '. $event->status->value . ' event here.';
            if (request()->wantsJson()) {
                return response()->json([
                    'message' => $msg,
                    'status' => $event->status->value,
                    'event_id' => $event->id,
                ], 422);
            }
            return back()->with('warning', $msg);
        }

        $event->status = $event->status === EventStatus::PUBLISHED
            ? EventStatus::DRAFT
            : EventStatus::PUBLISHED;
        $event->save();
        $msg = 'Event status updated to '. ucfirst($event->status->value) .'.';
        if (request()->wantsJson()) {
            return response()->json([
                'message' => $msg,
                'status' => $event->status->value,
                'badge_class' => match($event->status->value) {
                    'published' => 'bg-success',
                    'draft' => 'bg-secondary',
                    'cancelled' => 'bg-danger',
                    default => 'bg-warning'
                },
                'event_id' => $event->id,
            ]);
        }
        return back()->with('success', $msg);
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

        // Revenue Statistics (sum of booking totals)
        $totalRevenue = Event::join('bookings', 'events.id', '=', 'bookings.event_id')
            ->sum('bookings.total_amount');

        // Operator Statistics
        $operators = User::where('role', \App\Enums\Role::OPERATOR)
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
                    // Total revenue as sum of ticket prices
                    'revenue' => \App\Models\Ticket::where('event_id', $event->id)->sum('price'),
                    'attendance_rate' => $event->tickets_count > 0
                        ? round(($event->tickets->count() / $event->tickets_count) * 100, 1)
                        : 0
                ];
            });
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
