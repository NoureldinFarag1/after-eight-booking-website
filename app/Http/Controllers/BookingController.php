<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\TicketStatus;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\BookingConfirmationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BookingController extends Controller
{
    use AuthorizesRequests;

    /**
     * Defense-in-depth: block manageable staff roles (operator, approval_officer, finance_officer) from personal booking flows.
     * Primary enforcement is in middleware 'restrict_staff_personal'.
     */
    protected function denyIfStaffRole(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        if ($user && $user->role && $user->role->isManageableStaff()) {
            abort(403, 'Staff roles cannot access personal bookings.');
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->denyIfStaffRole();
        /** @var User $user */
        $user = Auth::user();

        $eventRequests = collect();
        $bookingQuery = Booking::query()->with(['event']);
        if ($user->isAdmin()) {
            $bookingQuery->with('user');
        } else {
            $bookingQuery->where('user_id', $user->id);
            $eventRequests = \App\Models\EventRequest::with('event')
                ->where('user_id', $user->id);
        }

        // Search: booking reference or event title
        if ($search = trim($request->input('q', ''))) {
            $bookingQuery->where(function ($q) use ($search) {
                $q->where('booking_reference', 'like', "%{$search}%")
                  ->orWhereHas('event', function ($qe) use ($search) {
                      $qe->where('title', 'like', "%{$search}%");
                  });
            });
            if (!$user->isAdmin()) {
                $eventRequests->whereHas('event', function ($qe) use ($search) {
                    $qe->where('title', 'like', "%{$search}%");
                });
            }
        }

        // Status filter
        if ($status = $request->input('status')) {
            $validStatuses = collect(BookingStatus::cases())->pluck('value')->all();
            if (in_array($status, $validStatuses, true)) {
                $bookingQuery->where('status', $status);
            }
        }

        $bookings = $bookingQuery->latest()->paginate(15, ['*'], 'bookings_page')->appends($request->query());
        if (!$user->isAdmin()) {
            $eventRequests = $eventRequests->latest()->paginate(12, ['*'], 'requests_page')->appends($request->query());
        }

        $filters = [
            'q' => $request->input('q'),
            'status' => $request->input('status'),
        ];

        $statusOptions = collect(BookingStatus::cases())->pluck('value')->all();

        return view('bookings.index', compact('bookings', 'eventRequests', 'filters', 'statusOptions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Event $event)
    {
        $this->denyIfStaffRole();
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
     * Show checkout page for booking (PRG: POST -> redirect -> GET)
     */
    public function checkout(Request $request)
    {
        $this->denyIfStaffRole();

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'event_id' => 'required|exists:events,id',
                'quantity' => 'required|integer|min:1|max:10',
                'ticket_type_id' => ['nullable', 'integer'],
                'whatsapp' => ['nullable', 'boolean'],
            ]);

            $event = Event::findOrFail($validated['event_id']);
            if ($event->ticketTypes()->where('is_active', true)->exists()) {
                $request->validate([
                    'ticket_type_id' => [
                        'required',
                        Rule::exists('ticket_types', 'id')->where(function ($q) use ($event) {
                            return $q->where('event_id', $event->id)->where('is_active', true);
                        }),
                    ],
                ]);
            }

            session(['checkout.data' => [
                'event_id' => (int) $validated['event_id'],
                'quantity' => (int) $validated['quantity'],
                'ticket_type_id' => $request->input('ticket_type_id'),
                'whatsapp' => $request->boolean('whatsapp'),
            ]]);

            return redirect()->route('bookings.checkout.view');
        }

        $data = session('checkout.data');
        if (!$data) {
            return redirect()->route('events.index')->with('error', 'Please choose your tickets first.');
        }

        $event = Event::findOrFail($data['event_id']);
        if (!$event->isBookable()) {
            return redirect()->route('events.show', $event)->with('error', 'This event is not available for booking.');
        }

        $types = $event->ticketTypes()->where('is_active', true)->orderBy('price')->get();
        $selectedType = null;
        if ($types->count() > 0) {
            $selectedType = $types->firstWhere('id', (int)($data['ticket_type_id'] ?? 0));
            if (!$selectedType) {
                return redirect()->route('bookings.create', $event)->with('error', 'Please select a valid ticket type.');
            }
        }

        $unitBase = $selectedType ? (float) $selectedType->price : 0.0;
        $unitFee = $selectedType ? $selectedType->calculateFee($unitBase) : 0.0;
        $unitPrice = $unitBase + $unitFee; // per-ticket price including fee
        $quantity = (int) $data['quantity'];
        $subtotal = $unitBase * $quantity;
        $handlingFee = $unitFee * $quantity;
        $whatsappSelected = !empty($data['whatsapp']);
        $whatsappFee = $whatsappSelected ? 25.00 : 0.00;
    $totalBeforeWhatsapp = $unitPrice * $quantity;
    $total = $totalBeforeWhatsapp + $whatsappFee;

        $ticketLabel = $selectedType ? $selectedType->name : 'General Admission';

        return view('bookings.checkout', [
            'event' => $event,
            'ticketType' => $selectedType,
            'ticketLabel' => $ticketLabel,
            'unitBase' => $unitBase,
            'unitPrice' => $unitPrice,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
            'handlingFee' => $handlingFee,
            'total' => $total,
            'totalBeforeWhatsapp' => $totalBeforeWhatsapp,
            'whatsappSelected' => $whatsappSelected,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->denyIfStaffRole();
        // Enforce business rule: admins cannot create bookings
        $this->authorize('create', Booking::class);

        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'quantity' => 'required|integer|min:1|max:10',
            'ticket_type_id' => ['nullable', 'integer'],
            'whatsapp' => ['nullable', 'boolean'],
        ]);

        $event = Event::findOrFail($validated['event_id']);

        if (!$event->isBookable()) {
            return redirect()->route('events.show', $event)->with('error', 'This event is not available for booking.');
        }

        if ($event->getAvailableSeatsAttribute() < $validated['quantity']) {
            return back()->withErrors(['quantity' => 'Not enough seats available.'])->withInput();
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

        $booking = DB::transaction(function () use ($validated, $event, $selectedType) {
            $unitBase = $selectedType ? (float)$selectedType->price : 0;
            $unitFee = $selectedType ? $selectedType->calculateFee($unitBase) : 0;
            $unitPrice = $unitBase + $unitFee; // final per-ticket price including fee
            $whatsappFee = request()->boolean('whatsapp') ? 25.00 : 0.00; // one-time

            $booking = Booking::create([
                'user_id' => Auth::id(),
                'event_id' => $validated['event_id'],
                'quantity' => $validated['quantity'],
                'total_amount' => ($unitPrice * $validated['quantity']) + $whatsappFee,
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

            return $booking;
        });

        // Load relationships for notification
        $booking->load(['event', 'tickets.type', 'user']);

        // Send booking confirmation email with QR codes
        /** @var User $user */
        $user = Auth::user();
        $user->notify(new BookingConfirmationNotification($booking));

        // Clear checkout session payload to avoid stale data
        session()->forget('checkout.data');

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Booking created successfully! Your tickets have been generated.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking)
    {
    $this->denyIfStaffRole();
        $this->authorize('view', $booking);

        $booking->load(['event', 'tickets.type', 'user']);

        return view('bookings.show', compact('booking'));
    }

    /**
     * Cancel a booking
     */
    public function cancel(Booking $booking)
    {
    $this->denyIfStaffRole();
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
    $this->denyIfStaffRole();
        $this->authorize('update', $booking);

        return view('bookings.edit', compact('booking'));
    }

    /**
     * Update booking (admin only)
     */
    public function update(Request $request, Booking $booking)
    {
    $this->denyIfStaffRole();
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
        $this->denyIfStaffRole();
        $this->authorize('delete', $booking);

        $booking->delete();

        return redirect()
            ->route('bookings.index')
            ->with('success', 'Booking deleted successfully.');
    }
}
