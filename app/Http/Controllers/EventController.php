<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\EventStatus;
use App\Enums\Role;
use App\Models\Event;
use App\Models\User;
use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
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

        // Admin-only: filter promoted (featured) events
        if ($isAdmin && $request->boolean('promoted')) {
            $query->where('is_featured', true);
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
            'promoted' => $request->input('promoted'),
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
        $artists = Artist::orderBy('name')->get();
        return view('events.create', compact('financeOfficers', 'operators','artists'));
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
            'google_maps_url' => 'required|url|max:1024',
            'artists' => 'nullable|string|max:500',
            'event_date' => 'required|date',
            'event_time' => 'required|date_format:H:i',
            'capacity' => 'required|integer|min:1',
            'type' => 'required|in:booking,request',
            'status' => 'required|in:draft,published,cancelled',
            'image' => 'nullable|image|max:2048',
            'layout_image' => 'required|image|max:4096',
            'terms_conditions' => 'nullable|string',
            'finance_officer_id' => 'nullable|exists:users,id',
            'operators' => 'nullable|array',
            'operators.*' => 'exists:users,id',
            'artist_ids' => 'nullable|array',
            'artist_ids.*' => 'integer|exists:artists,id',
            'artists_new' => 'nullable|array',
            'artists_new.*.name' => 'required_with:artists_new|string|max:255',
            'artists_new.*.photo' => 'nullable|image|max:2048',
            'ticket_types' => 'nullable|array',
            'ticket_types.*.name' => 'required_with:ticket_types|string|max:255|distinct',
            'ticket_types.*.price' => 'required_with:ticket_types|numeric|min:0',
            'ticket_types.*.fee_type' => 'nullable|in:fixed,percentage',
            'ticket_types.*.fee_amount' => 'nullable|numeric|min:0|max:1000',
            'ticket_types.*.capacity' => 'nullable|integer|min:0',
            'ticket_types.*.is_active' => 'nullable|in:0,1',
            'is_featured' => 'nullable|boolean',
        ]);

        // Business rules for featuring on creation
        if ($request->boolean('is_featured')) {
            // 1) Not allowed to promote past events
            $eventDateStr = \Illuminate\Support\Carbon::parse($validated['event_date'])->toDateString();
            if ($eventDateStr < now()->toDateString()) {
                return back()->withErrors(['is_featured' => 'Cannot promote a past event.'])->withInput();
            }
            // 2) Not allowed to have more than one promoted event at the same time
            if (Event::where('is_featured', true)->exists()) {
                return back()->withErrors(['is_featured' => 'Another event is already promoted. Unpromote it first.'])->withInput();
            }
        }

        if ($request->hasFile('image')) {
            $validated['image_url'] = $request->file('image')->store('event_images', 'public');
        }
        // Store required layout image
        $validated['layout_image_url'] = $request->file('layout_image')->store('event_layouts', 'public');

        $coords = Event::parseCoordinatesFromUrl($validated['google_maps_url'] ?? null);
        $event = Event::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'location' => $validated['location'],
            'google_maps_url' => $validated['google_maps_url'] ?? null,
            'latitude' => $coords['lat'] ?? null,
            'longitude' => $coords['lng'] ?? null,
            'event_date' => $validated['event_date'],
            'event_time' => $validated['event_time'],
            'capacity' => $validated['capacity'],
            'type' => $validated['type'],
            'status' => $validated['status'],
            'is_featured' => $request->boolean('is_featured'),
            'image_url' => $validated['image_url'] ?? null,
            'layout_image_url' => $validated['layout_image_url'] ?? null,
            'terms_conditions' => $validated['terms_conditions'],
            'finance_officer_id' => $validated['finance_officer_id'],
            // Fees intentionally excluded at creation time; managed later with tickets.
            'artists' => $validated['artists'] ?? null,
            'initial_capacity' => $validated['capacity'], // Store the original capacity
        ]);

        // Invalidate homepage featured cache immediately
        Cache::forget('home:featured:' . now()->format('Y-m'));

        if (!empty($validated['operators'])) {
            $event->operators()->sync($validated['operators']);
        }

        // Attach existing artists
        $attachIds = collect($request->input('artist_ids', []))
            ->filter()->map(fn($id) => (int)$id)->values()->all();
        if (!empty($attachIds)) {
            $event->artists()->attach($attachIds);
        }
        // Create and attach new artists
        $newArtists = $request->input('artists_new', []);
        foreach ($newArtists as $idx => $a) {
            if (!empty($a['name'])) {
                $photoPath = null;
                if ($request->hasFile("artists_new.$idx.photo")) {
                    $photoPath = $request->file("artists_new.$idx.photo")->store('artist_images', 'public');
                }
                $artist = Artist::create(['name' => $a['name'], 'photo_url' => $photoPath]);
                $event->artists()->attach($artist->id);
            }
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
        $event->load([
            'bookings.user',
            'tickets.type',
            'invitations.sender',
            'artists',
            'ticketTypes',
        ]);

        $user = Auth::user();
        $insights = [];
        $canViewFinanceInsights = false;

        if ($user && $user->role === Role::FINANCE_OFFICER) {
            if ($event->finance_officer_id !== $user->id) {
                abort(403, 'You are not authorized to view finance insights for this event.');
            }

            $canViewFinanceInsights = true;
            $confirmedTickets = $event->tickets()
                ->whereHas('booking', fn($q) => $q->where('status', BookingStatus::CONFIRMED->value))
                ->with('type')
                ->get();

            $totalRevenue = $confirmedTickets->sum(function ($ticket) {
                if (!is_null($ticket->price)) {
                    return (float) $ticket->price;
                }

                return $ticket->type ? (float) ($ticket->type->price ?? 0) : 0.0;
            });

            $ticketTypesInsights = $event->ticketTypes()
                ->orderByDesc('is_active')
                ->orderBy('price')
                ->get();

            $ticketTypesInsights = $ticketTypesInsights->map(function ($type) use ($confirmedTickets) {
                    $ticketsForType = $confirmedTickets->where('ticket_type_id', $type->id);

                    $revenueForType = $ticketsForType->sum(function ($ticket) use ($type) {
                        if (!is_null($ticket->price)) {
                            return (float) $ticket->price;
                        }

                        return $type->price ? (float) $type->price : 0.0;
                    });

                    return [
                        'id' => $type->id,
                        'name' => $type->name,
                        'is_active' => (bool) $type->is_active,
                        'price' => (float) ($type->price ?? 0),
                        'tickets_sold' => $ticketsForType->count(),
                        'revenue' => $revenueForType,
                        'fee_type' => $type->fee_type,
                        'fee_amount' => $type->fee_amount !== null ? (float) $type->fee_amount : null,
                        'per_ticket_fee' => (float) $type->calculateFee(),
                    ];
                });

            $untypedTickets = $confirmedTickets->whereNull('ticket_type_id');
            if ($untypedTickets->isNotEmpty()) {
                $untypedRevenue = $untypedTickets->sum(function ($ticket) {
                    return (float) ($ticket->price ?? 0);
                });

                $ticketTypesInsights = $ticketTypesInsights->push([
                    'id' => null,
                    'name' => 'Unassigned Tickets',
                    'is_active' => false,
                    'price' => 0.0,
                    'tickets_sold' => $untypedTickets->count(),
                    'revenue' => $untypedRevenue,
                    'fee_type' => null,
                    'fee_amount' => null,
                    'per_ticket_fee' => 0.0,
                ]);
            }

            $insights = [
                'revenue' => $totalRevenue,
                'tickets_sold' => $confirmedTickets->count(),
                'ticket_types' => $ticketTypesInsights->values(),
            ];
        }

        return view('events.show', [
            'event' => $event,
            'insights' => $insights,
            'canViewFinanceInsights' => $canViewFinanceInsights,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        $financeOfficers = User::where('role', \App\Enums\Role::FINANCE_OFFICER)->get();
        $operators = User::where('role', \App\Enums\Role::OPERATOR)->where('is_active', true)->get();
        $artists = Artist::orderBy('name')->get();
        $event->load('artists');
        return view('events.edit', compact('event', 'financeOfficers', 'operators','artists'));
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
            'google_maps_url' => 'required|url|max:1024',
            'artists' => 'nullable|string|max:500',
            'event_date' => 'required|date',
            'event_time' => 'required|date_format:H:i',
            'capacity' => 'required|integer|min:1',
            'type' => 'required|in:booking,request',
            'status' => 'required|in:draft,published,cancelled',
            'image' => 'nullable|image|max:2048',
            'layout_image' => 'nullable|image|max:4096',
            'terms_conditions' => 'nullable|string',
            'finance_officer_id' => 'nullable|exists:users,id',
            'operators' => 'nullable|array',
            'operators.*' => 'exists:users,id',
            'artist_ids' => 'nullable|array',
            'artist_ids.*' => 'integer|exists:artists,id',
            'artists_new' => 'nullable|array',
            'artists_new.*.name' => 'required_with:artists_new|string|max:255',
            'artists_new.*.photo' => 'nullable|image|max:2048',
            'ticket_types' => 'nullable|array',
            'ticket_types.*.id' => 'nullable|integer|exists:ticket_types,id',
            'ticket_types.*.name' => 'required_with:ticket_types|string|max:100|distinct',
            'ticket_types.*.price' => 'required_with:ticket_types|numeric|min:0',
            'ticket_types.*.fee_type' => 'nullable|in:fixed,percentage',
            'ticket_types.*.fee_amount' => 'nullable|numeric|min:0|max:1000',
            'ticket_types.*.capacity' => 'nullable|integer|min:0',
            'ticket_types.*.is_active' => 'nullable|in:0,1',
            'is_featured' => 'nullable|boolean',
        ]);

        // Business rules for featuring on update
        $wantFeatured = $request->boolean('is_featured');
        if ($wantFeatured && !$event->is_featured) {
            // 1) Not allowed to promote past events (use incoming date)
            if (\Carbon\Carbon::parse($validated['event_date'])->isBefore(now()->startOfDay())) {
                return back()->withErrors(['is_featured' => 'Cannot promote a past event.'])->withInput();
            }
            // 2) Not allowed while another event is already featured
            if (Event::where('is_featured', true)->where('id', '!=', $event->id)->exists()) {
                return back()->withErrors(['is_featured' => 'Another event is already promoted. Unpromote it first.'])->withInput();
            }
        }

        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($event->image_url) {
                Storage::disk('public')->delete($event->image_url);
            }
            $validated['image_url'] = $request->file('image')->store('event_images', 'public');
        }
        if ($request->hasFile('layout_image')) {
            if ($event->layout_image_url) {
                Storage::disk('public')->delete($event->layout_image_url);
            }
            $validated['layout_image_url'] = $request->file('layout_image')->store('event_layouts', 'public');
        }

        $coords = array_key_exists('google_maps_url',$validated)
            ? Event::parseCoordinatesFromUrl($validated['google_maps_url'])
            : null;
        $event->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'location' => $validated['location'],
            'google_maps_url' => $validated['google_maps_url'] ?? $event->google_maps_url,
            'latitude' => $coords['lat'] ?? (array_key_exists('google_maps_url',$validated) ? null : $event->latitude),
            'longitude' => $coords['lng'] ?? (array_key_exists('google_maps_url',$validated) ? null : $event->longitude),
            'event_date' => $validated['event_date'],
            'event_time' => $validated['event_time'],
            'capacity' => $validated['capacity'],
            'type' => $validated['type'],
            'status' => $validated['status'],
            'is_featured' => $request->boolean('is_featured'),
            'image_url' => $validated['image_url'] ?? $event->image_url,
            'layout_image_url' => $validated['layout_image_url'] ?? $event->layout_image_url,
            'terms_conditions' => $validated['terms_conditions'],
            // Fees remain unchanged here; will be managed via ticket management UI
            'artists' => $validated['artists'] ?? $event->artists,
            'finance_officer_id' => $request->finance_officer_id,
        ]);

        // Invalidate homepage featured cache immediately
        Cache::forget('home:featured:' . now()->format('Y-m'));

        if (!empty($validated['operators'])) {
            $event->operators()->sync($validated['operators']);
        } else {
            $event->operators()->detach();
        }

        // Sync existing artist IDs
        $syncIds = collect($request->input('artist_ids', []))->filter()->map(fn($id) => (int)$id)->values()->all();
        $event->artists()->sync($syncIds);
        // Create any new artists and attach
        foreach ($request->input('artists_new', []) as $idx => $a) {
            if (!empty($a['name'])) {
                $photoPath = null;
                if ($request->hasFile("artists_new.$idx.photo")) {
                    $photoPath = $request->file("artists_new.$idx.photo")->store('artist_images', 'public');
                }
                $artist = Artist::create(['name' => $a['name'], 'photo_url' => $photoPath]);
                $event->artists()->attach($artist->id);
            }
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
        // Invalidate homepage cache in case publish state changes featured choice
        Cache::forget('home:featured:' . now()->format('Y-m'));
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

    public function toggleFeatured(Event $event)
    {
        if (!Auth::user() || Auth::user()->role !== \App\Enums\Role::ADMIN) {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            abort(403);
        }

        // If trying to promote (currently not featured), enforce rules
        if (!$event->is_featured) {
            // Not allowed to promote past events
            $eventDateStr = substr((string)$event->event_date, 0, 10);
            if ($eventDateStr < now()->toDateString()) {
                $msg = 'Cannot promote a past event.';
                return request()->wantsJson()
                    ? response()->json(['message' => $msg, 'is_featured' => false, 'event_id' => $event->id], 422)
                    : back()->with('warning', $msg);
            }
            // Not allowed to promote when another event is already promoted
            if (Event::where('is_featured', true)->where('id', '!=', $event->id)->exists()) {
                $msg = 'Another event is already promoted. Unpromote it first.';
                return request()->wantsJson()
                    ? response()->json(['message' => $msg, 'is_featured' => false, 'event_id' => $event->id], 422)
                    : back()->with('warning', $msg);
            }
        }

        $event->is_featured = !$event->is_featured;
        $event->save();

        Cache::forget('home:featured:' . now()->format('Y-m'));

        $msg = $event->is_featured ? 'Event promoted on homepage.' : 'Event removed from homepage promotion.';

        if (request()->wantsJson()) {
            return response()->json([
                'message' => $msg,
                'is_featured' => $event->is_featured,
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

    /**
     * Export a single event KPIs as a CSV (Excel-compatible).
     */
    public function exportSingle(Event $event)
    {
        if (!Auth::user() || Auth::user()->role !== \App\Enums\Role::ADMIN) {
            abort(403);
        }

    // Filename: "Event name + date"
    $title = (string) ($event->title ?? 'Event');
    $dateStr = optional($event->event_date)->toDateString() ?? now()->toDateString();
    $base = trim($title . ' ' . $dateStr);
    // Sanitize filename (avoid regex meta issues by using str_replace for invalid chars)
    $safe = str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], ' ', $base);
    $safe = preg_replace('/\s+/', ' ', $safe);
    $filename = trim($safe) . '.xlsx';

    return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\SingleEventExport($event), $filename);
    }

    /**
     * Export multiple events (based on current filters) as CSV.
     */
    public function exportBulk(Request $request)
    {
        if (!Auth::user() || Auth::user()->role !== \App\Enums\Role::ADMIN) {
            abort(403);
        }

        $query = Event::query();

        // Optional filters similar to index()
        if ($status = $request->input('status')) {
            $validStatuses = collect(EventStatus::cases())->pluck('value')->all();
            if (in_array($status, $validStatuses, true)) {
                $query->where('status', $status);
            }
        }

        if ($request->boolean('promoted')) {
            $query->where('is_featured', true);
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

        if ($minCap = $request->input('capacity_min')) {
            $query->where('capacity', '>=', (int) $minCap);
        }
        if ($maxCap = $request->input('capacity_max')) {
            $query->where('capacity', '<=', (int) $maxCap);
        }

        // Sorting similar to index default
        $sort = $request->input('sort', 'date_asc');
        switch ($sort) {
            case 'date_desc':
                $query->orderBy('event_date', 'desc')->orderBy('event_time', 'desc');
                break;
            case 'created_desc':
                $query->orderBy('created_at', 'desc');
                break;
            case 'capacity_desc':
                $query->orderBy('capacity', 'desc');
                break;
            case 'capacity_asc':
                $query->orderBy('capacity', 'asc');
                break;
            case 'date_asc':
            default:
                $query->orderBy('event_date', 'asc')->orderBy('event_time', 'asc');
        }

        $events = $query->get();

        // Filename: "Events from Date to Date"
        $fromLabel = $request->input('date_from');
        $toLabel = $request->input('date_to');
        if (!$fromLabel && $events->count()) {
            $fromLabel = optional($events->min('event_date'))?->toDateString();
        }
        if (!$toLabel && $events->count()) {
            $toLabel = optional($events->max('event_date'))?->toDateString();
        }
        // Fallback if still empty
        $fromLabel = $fromLabel ?: now()->toDateString();
        $toLabel = $toLabel ?: $fromLabel;
    $base = "Events from {$fromLabel} to {$toLabel}";
    $safe = str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], ' ', $base);
    $safe = preg_replace('/\s+/', ' ', $safe);
        $filename = trim($safe) . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\EventsBulkExport($events), $filename);
    }
}
