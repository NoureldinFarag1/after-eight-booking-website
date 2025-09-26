<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\TicketStatus;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BookingController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isAdmin()) {
            $bookings = Booking::with(['user', 'event'])->latest()->paginate(15);
        } else {
            $bookings = Booking::with('event')
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(15);
        }

        return view('bookings.index', compact('bookings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Event $event)
    {
        // Enforce business rule: admins cannot create bookings
        $this->authorize('create', Booking::class);
        if (!$event->isBookable()) {
            return redirect()
                ->route('events.show', $event)
                ->with('error', 'This event is not available for booking.');
        }

        $types = $event->ticketTypes()->where('is_active', true)->orderBy('price')->get();

        return view('bookings.create', compact('event', 'types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Enforce business rule: admins cannot create bookings
        $this->authorize('create', Booking::class);
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'quantity' => 'required|integer|min:1|max:10',
            'ticket_type_id' => ['nullable', 'integer'],
        ]);

        $event = Event::findOrFail($validated['event_id']);

        if (!$event->isBookable()) {
            return redirect()
                ->route('events.show', $event)
                ->with('error', 'This event is not available for booking.');
        }

        if ($event->getAvailableSeatsAttribute() < $validated['quantity']) {
            return redirect()
                ->route('events.show', $event)
                ->with('error', 'Not enough seats available.');
        }

    $types = $event->ticketTypes()->where('is_active', true)->get();
    $selectedType = null;
    if ($types->count() > 0) {
            // When types exist, a valid type is required
            $request->validate([
                'ticket_type_id' => [
                    'required',
                    Rule::exists('ticket_types', 'id')->where(function ($q) use ($event) {
                        return $q->where('event_id', $event->id)->where('is_active', true);
                    }),
                ],
            ]);

            $selectedType = $types->firstWhere('id', (int)$request->input('ticket_type_id'));
            if (!$selectedType) {
                return back()->withErrors(['ticket_type_id' => 'Invalid ticket type selected.'])->withInput();
            }

            // Enforce per-type capacity if set
            if (!is_null($selectedType->capacity)) {
                $soldCount = Ticket::where('event_id', $event->id)
                    ->where('ticket_type_id', $selectedType->id)
                    ->where('status', '!=', TicketStatus::CANCELLED)
                    ->count();
                $remaining = max(0, $selectedType->capacity - $soldCount);
                if ($remaining < $validated['quantity']) {
                    return back()->withErrors(['quantity' => 'Not enough tickets available for the selected type. Remaining: ' . $remaining])->withInput();
                }
            }
        }

        DB::transaction(function () use ($validated, $event, $selectedType) {
            // With ticket-type-first model, a type must be selected when types exist
            $unitPrice = $selectedType ? $selectedType->price : 0;
            // Create booking
            $booking = Booking::create([
                'user_id' => Auth::id(),
                'event_id' => $validated['event_id'],
                'quantity' => $validated['quantity'],
                'total_amount' => $unitPrice * $validated['quantity'],
                'status' => BookingStatus::CONFIRMED,
                'booking_date' => now(),
            ]);

            // Create tickets for each booking
            for ($i = 0; $i < $validated['quantity']; $i++) {
                Ticket::create([
                    'user_id' => Auth::id(),
                    'event_id' => $event->id,
                    'ticket_type_id' => $selectedType?->id,
                    'booking_id' => $booking->id,
                    'status' => TicketStatus::VALID,
                    'price' => $unitPrice,
                ]);
            }
        });

        return redirect()
            ->route('bookings.index')
            ->with('success', 'Booking created successfully! Your tickets have been generated.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        $booking->load(['event', 'tickets.type', 'user']);

        return view('bookings.show', compact('booking'));
    }

    /**
     * Cancel a booking
     */
    public function cancel(Booking $booking)
    {
        $this->authorize('cancel', $booking);

        if (!$booking->canBeCancelled()) {
            return redirect()
                ->route('bookings.show', $booking)
                ->with('error', 'This booking cannot be cancelled.');
        }

        DB::transaction(function () use ($booking) {
            $booking->update(['status' => BookingStatus::CANCELLED]);

            // Cancel all tickets for this booking
            $booking->tickets()->update(['status' => TicketStatus::CANCELLED]);
        });

        return redirect()
            ->route('bookings.index')
            ->with('success', 'Booking cancelled successfully.');
    }

    /**
     * Edit booking (admin only)
     */
    public function edit(Booking $booking)
    {
        $this->authorize('update', $booking);

        return view('bookings.edit', compact('booking'));
    }

    /**
     * Update booking (admin only)
     */
    public function update(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,refunded',
            'notes' => 'nullable|string',
        ]);

        $booking->update($validated);

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', 'Booking updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        $this->authorize('delete', $booking);

        $booking->delete();

        return redirect()
            ->route('bookings.index')
            ->with('success', 'Booking deleted successfully.');
    }
}
