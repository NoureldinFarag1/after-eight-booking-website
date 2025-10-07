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

        if (!$isAdmin) {
            $query->published()->upcoming();
        }

        if ($user?->isFinanceOfficer()) {
            $query->where('finance_officer_id', $user->id);
        }

        if ($search = trim($request->input('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($isAdmin && ($status = $request->input('status'))) {
            $validStatuses = collect(EventStatus::cases())->pluck('value')->all();
            if (in_array($status, $validStatuses, true)) {
                $query->where('status', $status);
            }
        }

        if ($type = $request->input('type')) {
            if (in_array($type, ['booking', 'request'], true)) {
                $query->where('type', $type);
            }
        }

        if ($from = $request->input('date_from')) {
            $query->whereDate('event_date', '>=', $from);
        }
        if ($to = $request->input('date_to')) {
            $query->whereDate('event_date', '<=', $to);
        }

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
        $financeOfficers = User::where('role', \App\Enums\Role::FINANCE_OFFICER)->get();
        $operators = User::where('role', \App\Enums\Role::OPERATOR)->where('is_active', true)->get();
        return view('events.create', compact('financeOfficers', 'operators'));
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
            'artists' => 'nullable|string|max:500',
            'event_date' => 'required|date',
            'event_time' => 'required|date_format:H:i',
            'capacity' => 'required|integer|min:1',
            'type' => 'required|in:booking,request',
            'status' => 'required|in:draft,published,cancelled',
            'image' => 'nullable|image|max:2048',
            'terms_conditions' => 'nullable|string',
            'finance_officer_id' => 'nullable|exists:users,id',
            'operators' => 'nullable|array',
            'operators.*' => 'exists:users,id',
            'ticket_types' => 'nullable|array',
            'ticket_types.*.name' => 'required_with:ticket_types|string|max:255|distinct',
            'ticket_types.*.price' => 'required_with:ticket_types|numeric|min:0',
            'ticket_types.*.fee_type' => 'nullable|in:fixed,percentage',
            'ticket_types.*.fee_amount' => 'nullable|numeric|min:0|max:1000',
            'ticket_types.*.capacity' => 'nullable|integer|min:0',
            'ticket_types.*.is_active' => 'nullable|in:0,1',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_url'] = $request->file('image')->store('event_images', 'public');
        }

        $event = Event::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'location' => $validated['location'],
            'event_date' => $validated['event_date'],
            'event_time' => $validated['event_time'],
            'capacity' => $validated['capacity'],
            'type' => $validated['type'],
            'status' => $validated['status'],
            'image_url' => $validated['image_url'] ?? null,
            'terms_conditions' => $validated['terms_conditions'],
            'finance_officer_id' => $validated['finance_officer_id'],
            // Fees intentionally excluded at creation time; managed later with tickets.
            'artists' => $validated['artists'] ?? null,
            'initial_capacity' => $validated['capacity'], // Store the original capacity
        ]);

        if (!empty($validated['operators'])) {
            $event->operators()->sync($validated['operators']);
        }

        $types = $request->input('ticket_types', []);
        if (!empty($types) && $request->input('type') === 'booking') {
            $payload = collect($types)
                ->filter(fn($t) => isset($t['name']) && $t['name'] !== '')
                ->map(fn($t) => [
                    'name' => $t['name'],
                    'description' => $t['description'] ?? null,
                    'price' => (float)($t['price'] ?? 0),
                    'fee_type' => $t['fee_type'] ?? null,
                    'fee_amount' => isset($t['fee_amount']) && $t['fee_amount'] !== '' ? (float)$t['fee_amount'] : null,
                    'capacity' => isset($t['capacity']) && $t['capacity'] !== '' ? (int)$t['capacity'] : null,
                    'is_active' => isset($t['is_active']) ? (int)$t['is_active'] : 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->values()->all();

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
        $event->load(['bookings.user', 'tickets', 'invitations.sender']);

        $user = Auth::user();

                // Finance Officer Insights
        $insights = [];
        if ($user && $user->role === \App\Enums\Role::FINANCE_OFFICER) {
            // Total revenue (from bookings)
            $totalRevenue = $event->bookings()->sum('total_amount');

            // Number of tickets sold
            $ticketsSold = $event->tickets()->count();

            // Number of requests submitted (if event type is request)
            $requestsSubmitted = $event->bookings()
                ->when($event->type === 'request', fn($q) => $q)
                ->count();

            // Invitations count
            $totalInvitations = $event->invitations()->count();

            // Invitations grouped by admin sender
            $invitationsByAdmin = $event->invitations()
                ->with('sender:id,name')
                ->get()
                ->groupBy('sender_id')
                ->map(fn($group) => [
                    'admin' => $group->first()->sender->name ?? 'Unknown',
                    'count' => $group->count()
                ])->values();

            $insights = [
                'revenue' => $totalRevenue,
                'tickets_sold' => $ticketsSold,
                'requests' => $requestsSubmitted,
                'invitations_total' => $totalInvitations,
                'invitations_by_admin' => $invitationsByAdmin,
            ];
        }

        return view('events.show', compact('event', 'insights'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        $financeOfficers = User::where('role', \App\Enums\Role::FINANCE_OFFICER)->get();
        $operators = User::where('role', \App\Enums\Role::OPERATOR)->where('is_active', true)->get();
        return view('events.edit', compact('event', 'financeOfficers', 'operators'));
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
            'artists' => 'nullable|string|max:500',
            'event_date' => 'required|date',
            'event_time' => 'required|date_format:H:i',
            'capacity' => 'required|integer|min:1',
            'type' => 'required|in:booking,request',
            'status' => 'required|in:draft,published,cancelled',
            'image' => 'nullable|image|max:2048',
            'terms_conditions' => 'nullable|string',
            'finance_officer_id' => 'nullable|exists:users,id',
            'operators' => 'nullable|array',
            'operators.*' => 'exists:users,id',
            'ticket_types' => 'nullable|array',
            'ticket_types.*.id' => 'nullable|integer|exists:ticket_types,id',
            'ticket_types.*.name' => 'required_with:ticket_types|string|max:100|distinct',
            'ticket_types.*.price' => 'required_with:ticket_types|numeric|min:0',
            'ticket_types.*.fee_type' => 'nullable|in:fixed,percentage',
            'ticket_types.*.fee_amount' => 'nullable|numeric|min:0|max:1000',
            'ticket_types.*.capacity' => 'nullable|integer|min:0',
            'ticket_types.*.is_active' => 'nullable|in:0,1',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($event->image_url) {
                Storage::disk('public')->delete($event->image_url);
            }
            $validated['image_url'] = $request->file('image')->store('event_images', 'public');
        }

        $event->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'location' => $validated['location'],
            'event_date' => $validated['event_date'],
            'event_time' => $validated['event_time'],
            'capacity' => $validated['capacity'],
            'type' => $validated['type'],
            'status' => $validated['status'],
            'image_url' => $validated['image_url'] ?? $event->image_url,
            'terms_conditions' => $validated['terms_conditions'],
            // Fees remain unchanged here; will be managed via ticket management UI
            'artists' => $validated['artists'] ?? $event->artists,
            'finance_officer_id' => $request->finance_officer_id,
        ]);

        if (!empty($validated['operators'])) {
            $event->operators()->sync($validated['operators']);
        } else {
            $event->operators()->detach();
        }

        $types = $request->input('ticket_types', []);
        if (!empty($types) && $request->input('type') === 'booking') {
            $allocated = $event->ticketTypes()->whereNotNull('capacity')->sum('capacity');
            $remaining = max(0, (int)$validated['capacity'] - $allocated);
            $sumNew = collect($types)->sum(fn($t) => isset($t['capacity']) && $t['capacity'] !== '' ? (int)$t['capacity'] : 0);

            if ($sumNew > $remaining) {
                return back()
                    ->withErrors(['ticket_types' => 'New ticket type capacities ('. $sumNew .') exceed remaining allocatable capacity ('. $remaining .').'])
                    ->withInput();
            }

            $payload = collect($types)
                ->filter(fn($t) => isset($t['name']) && $t['name'] !== '')
                ->map(fn($t) => [
                    'name' => $t['name'],
                    'description' => $t['description'] ?? null,
                    'price' => (float)($t['price'] ?? 0),
                    'fee_type' => $t['fee_type'] ?? null,
                    'fee_amount' => isset($t['fee_amount']) && $t['fee_amount'] !== '' ? (float)$t['fee_amount'] : null,
                    'capacity' => isset($t['capacity']) && $t['capacity'] !== '' ? (int)$t['capacity'] : null,
                    'is_active' => isset($t['is_active']) ? (int)$t['is_active'] : 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->values()->all();

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
        if ($event->bookings()->count() > 0) {
            return redirect()
                ->route('events.index')
                ->with('error', 'Cannot delete event with existing bookings.');
        }

        if ($event->image_url) {
            Storage::disk('public')->delete($event->image_url);
        }

        $event->delete();

        return redirect()
            ->route('events.index')
            ->with('success', 'Event deleted successfully!');
    }

    public function togglePublish(Event $event)
    {
        if (!Auth::user() || Auth::user()->role !== \App\Enums\Role::ADMIN) {
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

    public function dashboard()
    {
        $totalEvents = Event::query()->count();
        $publishedEvents = Event::query()->published()->count();
        $upcomingEvents = Event::query()->upcoming()->count();
        $pastEvents = Event::query()->past()->count();

        $totalBookings = Event::withCount('bookings')->get()->sum('bookings_count');
        $totalTickets = \App\Models\Ticket::count();
        $scannedTickets = \App\Models\Ticket::whereNotNull('scanned_at')->count();
        $validTickets = \App\Models\Ticket::where('status', \App\Enums\TicketStatus::VALID)->count();

        $totalRevenue = Event::join('bookings', 'events.id', '=', 'bookings.event_id')
            ->sum('bookings.total_amount');

        $operators = User::where('role', \App\Enums\Role::OPERATOR)
            ->withCount(['scannedTickets as total_scans'])
            ->get();
        $totalOperators = $operators->count();
        $activeOperators = $operators->where('total_scans', '>', 0)->count();

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
