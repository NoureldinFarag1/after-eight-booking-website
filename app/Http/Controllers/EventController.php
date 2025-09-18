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
        $totalEvents = Event::query()->count();
        $publishedEvents = Event::query()->published()->count();
        $upcomingEvents = Event::query()->upcoming()->count();
        $totalBookings = Event::withCount('bookings')->get()->sum('bookings_count');

        $recentEvents = Event::query()->latest()->limit(5)->get();

        return view('admin.dashboard', compact(
            'totalEvents',
            'publishedEvents',
            'upcomingEvents',
            'totalBookings',
            'recentEvents'
        ));
    }
}
