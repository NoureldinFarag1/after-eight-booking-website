<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\EventRequest;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Enums\EventRequestStatus;
use App\Notifications\RequestApprovedForPaymentNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class EventRequestController extends Controller
{
    protected function denyIfStaffRole(): void
    {
        $user = Auth::user();
        if ($user && $user->role && $user->role->isManageableStaff()) {
            abort(403, 'Staff roles cannot submit event requests.');
        }
    }

    /**
     * Determine which statuses should block a user from submitting another request.
     */
    protected function statusesBlockingNewSubmission(): array
    {
        return [
            EventRequestStatus::PENDING->value,
            EventRequestStatus::AWAITING_PAYMENT->value,
            EventRequestStatus::DECLINED->value,
            EventRequestStatus::PAID->value,
            // Legacy status retained for backward compatibility with historical records
            EventRequestStatus::APPROVED->value,
        ];
    }

    protected function findLatestUserRequestForEvent(Event $event): ?EventRequest
    {
        $userId = Auth::id();
        if (!$userId) {
            return null;
        }

        return EventRequest::where('event_id', $event->id)
            ->where('user_id', $userId)
            ->latest('id')
            ->first();
    }

    protected function redirectForExistingRequest(EventRequest $existing, Event $event)
    {
        $label = $this->formatStatusLabel($existing);
        $flashKey = $existing->status === EventRequestStatus::DECLINED->value ? 'error' : 'warning';
        $nextStep = match ($existing->status) {
            EventRequestStatus::PENDING->value => ' You can review or edit your submission below.',
            EventRequestStatus::AWAITING_PAYMENT->value => ' Please complete payment to confirm your spot.',
            EventRequestStatus::PAID->value => ' Payment has already been completed.',
            EventRequestStatus::DECLINED->value => ' The decision on this request is final.',
            default => '',
        };

        return redirect()->route('events.show', $event)
            ->with($flashKey, 'You have already submitted a request for this event. Status: ' . $label . '.' . $nextStep);
    }

    protected function formatStatusLabel(EventRequest $request): string
    {
        $enum = EventRequestStatus::tryFrom($request->status);

        return $enum ? $enum->label() : ucfirst(str_replace('_', ' ', $request->status));
    }

    // Show the form to create a request for an event
    public function create(Event $event)
    {
        $this->denyIfStaffRole();
        // If the user already has an active request for this event, surface it instead of opening a new form
        $existing = $this->findLatestUserRequestForEvent($event);
        if ($existing) {
            $existing->expireIfPastDeadline();
            if (in_array($existing->status, $this->statusesBlockingNewSubmission(), true)) {
                return $this->redirectForExistingRequest($existing, $event);
            }
        }
        // Provide ticket types for selection (active only)
        $ticketTypes = $event->ticketTypes()->where('is_active', 1)->orderBy('price')->get();
        return view('event_requests.create', compact('event','ticketTypes'));
    }

    // Store a new request
    public function store(Request $request, Event $event)
    {
        $this->denyIfStaffRole();
        // Prevent duplicate request by same user for same event
        $existing = $this->findLatestUserRequestForEvent($event);
        if ($existing) {
            $existing->expireIfPastDeadline();
            if (in_array($existing->status, $this->statusesBlockingNewSubmission(), true)) {
                return $this->redirectForExistingRequest($existing, $event);
            }
        }
        // Normalize guests: drop completely empty rows so we only validate rows the user actually filled
        $rawGuests = $request->input('guests', []);
        if (is_array($rawGuests)) {
            $cleanGuests = collect($rawGuests)
                ->filter(function ($g) {
                    if (!is_array($g)) return false;
                    $name = isset($g['name']) ? trim((string)$g['name']) : '';
                    $social = isset($g['social_url']) ? trim((string)$g['social_url']) : '';
                    return $name !== '' || $social !== '';
                })
                ->values()
                ->all();
            $request->merge(['guests' => $cleanGuests]);
        }

        // Validate attendees: up to 5, first requires name+email+social; each guest requires name + social if provided
        // Custom rule to ensure social URL is an Instagram or Facebook profile with a non-empty path
        $socialUrlRule = function (string $attribute, mixed $value, \Closure $fail) {
            $url = trim((string) $value);
            if ($url === '') {
                $fail('The social URL is required.');
                return;
            }
            $parts = parse_url($url);
            if ($parts === false || empty($parts['host']) || empty($parts['path'])) {
                $fail('Please provide a valid Instagram or Facebook profile link.');
                return;
            }
            $host = strtolower($parts['host']);
            $isInsta = preg_match('/(^|\.)instagram\.com$/i', $host) === 1;
            $isFb = preg_match('/(^|\.)facebook\.com$/i', $host) === 1;
            if (!($isInsta || $isFb)) {
                $fail('Social link must be from instagram.com or facebook.com.');
                return;
            }
            $path = trim($parts['path'], '/');
            if ($path === '') {
                $fail('Social link must include an account path.');
            }
        };

        $validated = $request->validate([
            'primary_name' => ['required', 'string', 'max:255'],
            'primary_email' => ['required', 'email', 'max:255'],
            'primary_social_url' => ['required', 'url', 'max:255', $socialUrlRule],
            'primary_ticket_type_id' => ['required', 'integer', 'exists:ticket_types,id'],
            'guests' => ['nullable', 'array', 'max:4'], // up to 4 additional guests (total 5 total attendees)
            'guests.*.name' => ['required', 'string', 'max:255'],
            'guests.*.email' => ['required','email','max:255'],
            'guests.*.social_url' => ['required', 'url', 'max:255', $socialUrlRule],
            'guests.*.ticket_type_id' => ['required','integer','exists:ticket_types,id'],
        ]);

        $payload = [
            'event_id' => $event->id,
            'user_id' => Auth::id(),
            'status' => 'pending',
            'primary_name' => $validated['primary_name'],
            'primary_email' => $validated['primary_email'],
            'primary_social_url' => $validated['primary_social_url'] ?? null,
            'primary_ticket_type_id' => $validated['primary_ticket_type_id'],
            'guests' => array_values(array_filter($validated['guests'] ?? [], function ($g) {
                // Keep only entries with a non-empty name (ticket type and email validated already if present)
                return isset($g['name']) && trim($g['name']) !== '';
            })),
            'attendee_count' => 1 + count(array_filter($validated['guests'] ?? [], function ($g) {
                return isset($g['name']) && trim($g['name']) !== '';
            })),
        ];

        // If event_requests has a payload JSON column, capture a snapshot of submitted data
    if (Schema::hasColumn('event_requests', 'payload')) {
            $payload['payload'] = [
                'primary' => [
                    'name' => $validated['primary_name'],
                    'email' => $validated['primary_email'],
                    'social_url' => $validated['primary_social_url'] ?? null,
                    'ticket_type_id' => $validated['primary_ticket_type_id'],
                ],
                'guests' => $payload['guests'],
            ];
        }

        EventRequest::create($payload);

        // TODO: send email with up to 5 QR codes to primary_email (out of scope for this step)

        return redirect()->route('events.show', $event)->with('success', 'Request submitted.');
    }

    // Alias for existing route name expecting myRequests
    public function myRequests()
    {
        return $this->index();
    }

    // List logged-in user's requests
    public function index()
    {
        $requests = EventRequest::where('user_id', Auth::id())->latest()->get();
        $requests->each(function (EventRequest $request) {
            $request->expireIfPastDeadline();
        });
        return view('event_requests.index', compact('requests'));
    }

    // Edit (only if pending and owner)
    public function edit(EventRequest $eventRequest)
    {
        $user = Auth::user();
        if (!$user || $user->id !== $eventRequest->user_id) {
            abort(403);
        }
        if ($eventRequest->status !== 'pending') {
            return redirect()->route('event_requests.show', $eventRequest)->with('warning', 'Only pending requests can be edited.');
        }
        $event = $eventRequest->event()->first();
        $ticketTypes = $event ? $event->ticketTypes()->where('is_active',1)->orderBy('price')->get() : collect();
        return view('event_requests.edit', compact('eventRequest','event','ticketTypes'));
    }

    // Update pending request
    public function update(Request $request, EventRequest $eventRequest)
    {
        $user = Auth::user();
        if (!$user || $user->id !== $eventRequest->user_id) {
            abort(403);
        }
        if ($eventRequest->status !== 'pending') {
            return redirect()->route('event_requests.show', $eventRequest)->with('warning', 'Only pending requests can be updated.');
        }

        $event = $eventRequest->event()->first();
        if (!$event) {
            return redirect()->route('event_requests.index')->with('error', 'Event no longer exists.');
        }

        // Normalize guests like in store
        $rawGuests = $request->input('guests', []);
        if (is_array($rawGuests)) {
            $cleanGuests = collect($rawGuests)
                ->filter(function ($g) {
                    if (!is_array($g)) return false;
                    $name = isset($g['name']) ? trim((string)$g['name']) : '';
                    $social = isset($g['social_url']) ? trim((string)$g['social_url']) : '';
                    $email = isset($g['email']) ? trim((string)$g['email']) : '';
                    return $name !== '' || $social !== '' || $email !== '';
                })
                ->values()
                ->all();
            $request->merge(['guests' => $cleanGuests]);
        }

        $socialUrlRule = function (string $attribute, mixed $value, \Closure $fail) {
            $url = trim((string) $value);
            if ($url === '') {
                $fail('The social URL is required.');
                return;
            }
            $parts = parse_url($url);
            if ($parts === false || empty($parts['host']) || empty($parts['path'])) {
                $fail('Please provide a valid Instagram or Facebook profile link.');
                return;
            }
            $host = strtolower($parts['host']);
            $isInsta = preg_match('/(^|\.)instagram\.com$/i', $host) === 1;
            $isFb = preg_match('/(^|\.)facebook\.com$/i', $host) === 1;
            if (!($isInsta || $isFb)) {
                $fail('Social link must be from instagram.com or facebook.com.');
                return;
            }
            $path = trim($parts['path'], '/');
            if ($path === '') {
                $fail('Social link must include an account path.');
            }
        };

        $validated = $request->validate([
            'primary_name' => ['required', 'string', 'max:255'],
            'primary_email' => ['required', 'email', 'max:255'],
            'primary_social_url' => ['required', 'url', 'max:255', $socialUrlRule],
            'primary_ticket_type_id' => ['required', 'integer', 'exists:ticket_types,id'],
            'guests' => ['nullable', 'array', 'max:4'],
            'guests.*.name' => ['required', 'string', 'max:255'],
            'guests.*.email' => ['required','email','max:255'],
            'guests.*.social_url' => ['required', 'url', 'max:255', $socialUrlRule],
            'guests.*.ticket_type_id' => ['required','integer','exists:ticket_types,id'],
        ]);

        $guests = array_values(array_filter($validated['guests'] ?? [], function ($g) {
            return isset($g['name']) && trim($g['name']) !== '';
        }));

        $eventRequest->update([
            'primary_name' => $validated['primary_name'],
            'primary_email' => $validated['primary_email'],
            'primary_social_url' => $validated['primary_social_url'],
            'primary_ticket_type_id' => $validated['primary_ticket_type_id'],
            'guests' => $guests,
            'attendee_count' => 1 + count($guests),
        ]);

        if (Schema::hasColumn('event_requests', 'payload')) {
            $snapshot = $eventRequest->payload ?? [];
            $snapshot['primary'] = [
                'name' => $validated['primary_name'],
                'email' => $validated['primary_email'],
                'social_url' => $validated['primary_social_url'],
                'ticket_type_id' => $validated['primary_ticket_type_id'],
            ];
            $snapshot['guests'] = $guests;
            $eventRequest->payload = $snapshot;
            $eventRequest->save();
        }

        return redirect()->route('event_requests.show', $eventRequest)->with('success', 'Request updated.');
    }

    // Admin listing of all requests
    public function adminIndex(Request $request)
    {
        $user = Auth::user();
        if (! $user || !in_array($user->role, [Role::ADMIN, Role::APPROVAL_OFFICER], true)) {
            abort(403);
        }
    $status = $request->query('status'); // optional status filter value
        $q = trim((string) $request->query('q', ''));

        $query = EventRequest::with(['user', 'event', 'admin'])->latest();
        $allowedStatuses = collect(EventRequestStatus::cases())->map(fn ($case) => $case->value)->all();
        if (in_array($status, $allowedStatuses, true)) {
            $query->where('status', $status);
        }
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                // Search requesting user
                $sub->whereHas('user', function ($u) use ($q) {
                        $u->where('name', 'like', "%$q%")
                          ->orWhere('email', 'like', "%$q%");
                    })
                    // Search event title only (no events.name column exists)
                    ->orWhereHas('event', function ($e) use ($q) {
                        $e->where('title', 'like', "%$q%");
                    })
                    // Primary attendee
                    ->orWhere('primary_name', 'like', "%$q%")
                    ->orWhere('primary_email', 'like', "%$q%")
                    // Guests (if JSON column present) – attempt basic JSON_EXTRACT match on guest names/emails
                    ->orWhere(function($g) use ($q) {
                        // Use MySQL JSON_SEARCH if available; fallback to LIKE on raw JSON text
                        $driver = config('database.default');
                        $conn = config("database.connections.$driver.driver");
                        if ($conn === 'mysql') {
                            // This pattern searches any guest object name/email containing the q string
                            $g->whereRaw('JSON_SEARCH(guests, "all", ?, NULL, "$[*].name") IS NOT NULL', ["%$q%"])
                              ->orWhereRaw('JSON_SEARCH(guests, "all", ?, NULL, "$[*].email") IS NOT NULL', ["%$q%"]);
                        } else {
                            // Fallback: coarse search on serialized guests array
                            $g->where('guests', 'like', "%$q%");
                        }
                    });
            });
        }

        $requests = $query->paginate(15)->appends([
            'status' => $status,
            'q' => $q !== '' ? $q : null,
        ]);

        $requests->getCollection()->each(function (EventRequest $request) {
            $request->expireIfPastDeadline();
        });

        // Collect ticket_type_ids from current page (primary + guests) to avoid N+1 lookups in view
        $ticketTypeIds = collect();
        foreach ($requests as $req) {
            if ($req->primary_ticket_type_id) {
                $ticketTypeIds->push($req->primary_ticket_type_id);
            }
            if (is_array($req->guests)) {
                foreach ($req->guests as $g) {
                    if (is_array($g) && !empty($g['ticket_type_id'])) {
                        $ticketTypeIds->push($g['ticket_type_id']);
                    }
                }
            }
        }
        $ticketTypeIds = $ticketTypeIds->unique()->values();
        $ticketTypeMap = [];
        if ($ticketTypeIds->count() > 0) {
            $ticketTypeMap = \App\Models\TicketType::whereIn('id', $ticketTypeIds)->pluck('name','id')->toArray();
        }

        return view('event_requests.admin_index', compact('requests', 'status', 'q', 'ticketTypeMap'));
    }

    // Show a single request (owner or admin)
    public function show(EventRequest $eventRequest)
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }
        $isOwner = $user->id === $eventRequest->user_id;
        $isAdmin = ($user->role === Role::ADMIN);
        if (! $isOwner && ! $isAdmin) {
            abort(403);
        }
        $eventRequest->expireIfPastDeadline();
        $eventRequest->load(['event', 'user', 'admin']);
        return view('event_requests.show', ['eventRequest' => $eventRequest]);
    }

    // Approve a request (admin only)
    public function approve(EventRequest $eventRequest)
    {
        $user = Auth::user();
        if (! $user || !in_array($user->role, [Role::ADMIN, Role::APPROVAL_OFFICER], true)) {
            abort(403, 'Unauthorized');
        }
        if ($eventRequest->status !== EventRequestStatus::PENDING->value) {
            return back()->with('error', 'This request is not pending and cannot be approved.');
        }

        try {
            $connName = config('database.default');
            $connectionDriver = config("database.connections.$connName.driver");
            $logic = function() use ($eventRequest, $user) {
                // Lock the event row to prevent race conditions (only on DBs that support it)
                /** @var Event $event */
                $connNameInner = config('database.default');
                $driverInner = config("database.connections.$connNameInner.driver");
                $relation = $eventRequest->event();
                if (in_array($driverInner, ['mysql','mariadb','pgsql','sqlsrv'], true)) {
                    $relation->lockForUpdate();
                }
                $event = $relation->first();
                if (!$event) {
                    abort(404, 'Event not found');
                }
                // Compute current availability considering booked tickets and active holds
                $booked = $event->bookings()->sum('quantity');
                $held = 0;
                if (Schema::hasColumn('event_requests','status') && Schema::hasColumn('event_requests','attendee_count')) {
                    $held = $event->requests()
                        ->where('status', EventRequestStatus::AWAITING_PAYMENT->value)
                        ->when(Schema::hasColumn('event_requests','expires_at'), function($q){
                            $q->where(function($sub){
                                $sub->whereNull('expires_at')->orWhere('expires_at','>', now());
                            });
                        })
                        ->sum('attendee_count');
                }
                $available = max(0, $event->capacity - $booked - $held);
                // Determine requested seats from attendee_count when present, otherwise infer from guests array
                if (Schema::hasColumn('event_requests','attendee_count')) {
                    $requestedSeats = (int) $eventRequest->attendee_count;
                } elseif (Schema::hasColumn('event_requests','guests')) {
                    $guests = is_array($eventRequest->guests) ? $eventRequest->guests : [];
                    $requestedSeats = 1 + count(array_filter($guests, function($g){
                        return is_array($g) && isset($g['name']) && trim((string)$g['name']) !== '';
                    }));
                } else {
                    $requestedSeats = 1; // minimal fallback
                }
                if ($requestedSeats <= 0) {
                    throw new \RuntimeException('Invalid attendee count.');
                }
                if ($requestedSeats > $available) {
                    Log::warning('Approval blocked due to capacity', [
                        'event_id' => $event->id,
                        'available' => $available,
                        'requested' => $requestedSeats,
                    ]);
                    throw new \RuntimeException('Not enough available seats to approve this request. Available: ' . $available);
                }

                // Persist status change and optional timestamps/admin
                $eventRequest->status = EventRequestStatus::AWAITING_PAYMENT->value;
                if (Schema::hasColumn('event_requests','approved_at')) {
                    $eventRequest->approved_at = now();
                }
                if (Schema::hasColumn('event_requests','expires_at')) {
                    $eventRequest->expires_at = now()->addHours(48);
                }
                if (Schema::hasColumn('event_requests','admin_id')) {
                    $eventRequest->admin_id = $user->id;
                }
                $eventRequest->save();
                Log::info('EventRequest approved for payment', [
                    'event_request_id' => $eventRequest->id,
                    'status' => $eventRequest->status,
                ]);
            };

            if (in_array($connectionDriver, ['mysql','mariadb','pgsql','sqlsrv'], true)) {
                DB::transaction($logic);
            } else {
                // SQLite or others: run without wrapping to ensure visibility across connections in tests
                $logic();
            }
            $eventRequest->refresh();
    } catch (\Throwable $e) {
            Log::error('Approve failed', [
                'event_request_id' => $eventRequest->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', $e->getMessage());
        }

    // Notify the user their request was approved and they can now pay
        try {
            Notification::send($eventRequest->user, new RequestApprovedForPaymentNotification($eventRequest));
            return back()->with('success', 'Request approved and user has been notified to complete payment.');
        } catch (\Throwable $mailEx) {
            Log::warning('Failed to send approval notification email', [
                'event_request_id' => $eventRequest->id,
                'error' => $mailEx->getMessage(),
            ]);
            return back()->with('success', 'Request approved. Email could not be sent in this environment. The user can track it from My Requests.');
        }
    }

    // Complete payment for an approved request (owner or admin/finance/approval)
    public function completePayment(EventRequest $eventRequest)
    {
        $user = Auth::user();
        if (! $user) {
            abort(403, 'Unauthorized');
        }
        $isOwner = $user->id === $eventRequest->user_id;
        $isPrivileged = in_array($user->role, [Role::ADMIN, Role::FINANCE_OFFICER, Role::APPROVAL_OFFICER], true);
        if (!($isOwner || $isPrivileged)) {
            abort(403, 'Unauthorized');
        }

        // Must be awaiting payment and not expired
        if ($eventRequest->status !== EventRequestStatus::AWAITING_PAYMENT->value) {
            return back()->with('error', 'This request is not awaiting payment.');
        }
        if (Schema::hasColumn('event_requests','expires_at') && $eventRequest->expires_at && $eventRequest->expires_at->isPast()) {
            $eventRequest->status = EventRequestStatus::EXPIRED->value;
            $eventRequest->save();
            return back()->with('error', 'This payment link has expired.');
        }

        // Build tickets and booking
        try {
            DB::transaction(function() use ($eventRequest, $user) {
                $event = $eventRequest->event()->firstOrFail();

                // Create booking confirmed
                $booking = new \App\Models\Booking();
                $booking->user_id = $eventRequest->user_id;
                $booking->event_id = $event->id;
                $booking->status = \App\Enums\BookingStatus::CONFIRMED;
                $booking->booking_date = now();
                $booking->quantity = 0; // will recalc
                // total_amount will be computed after creating tickets
                $booking->save();

                $ticketsToCreate = [];
                $ticketTypesCache = [];
                $resolveType = function($id) use (&$ticketTypesCache) {
                    if (!$id) return null;
                    if (!isset($ticketTypesCache[$id])) {
                        $ticketTypesCache[$id] = \App\Models\TicketType::find($id);
                    }
                    return $ticketTypesCache[$id];
                };

                // Primary attendee
                $primaryType = $resolveType($eventRequest->primary_ticket_type_id);
                if ($primaryType) {
                    $ticketsToCreate[] = [
                        'user_id' => $eventRequest->user_id,
                        'event_id' => $event->id,
                        'ticket_type_id' => $primaryType->id,
                        'price' => (float)($primaryType->price ?? 0),
                    ];
                }
                // Guests
                $guests = is_array($eventRequest->guests) ? $eventRequest->guests : [];
                foreach ($guests as $g) {
                    if (!is_array($g) || empty($g['ticket_type_id'])) continue;
                    $tt = $resolveType((int)$g['ticket_type_id']);
                    if (!$tt) continue;
                    $ticketsToCreate[] = [
                        'user_id' => $eventRequest->user_id,
                        'event_id' => $event->id,
                        'ticket_type_id' => $tt->id,
                        'price' => (float)($tt->price ?? 0),
                    ];
                }

                // Persist tickets
                $total = 0.0;
                foreach ($ticketsToCreate as $payload) {
                    $t = new \App\Models\Ticket($payload);
                    $t->booking_id = $booking->id;
                    $t->status = \App\Enums\TicketStatus::VALID;
                    $t->save();
                    $total += (float)$t->price;
                }

                $booking->quantity = count($ticketsToCreate);
                $totalWithFees = round($event->getTotalWithFees($total), 2);
                $booking->setAttribute('total_amount', $totalWithFees);
                $booking->save();

                // Mark request as paid (approval timestamp already set during approve step)
                $eventRequest->status = EventRequestStatus::PAID->value;
                if (Schema::hasColumn('event_requests','paid_at')) {
                    $eventRequest->paid_at = now();
                }
                $eventRequest->save();

                // Send QR codes
                try {
                    Notification::send($eventRequest->user, new \App\Notifications\BookingConfirmationNotification($booking));
                } catch (\Throwable $mailEx) {
                    Log::warning('Failed to send booking confirmation email', [
                        'booking_id' => $booking->id ?? null,
                        'event_request_id' => $eventRequest->id,
                        'error' => $mailEx->getMessage(),
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Log::error('EventRequest payment completion failed', [
                'event_request_id' => $eventRequest->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Payment processing failed: '.$e->getMessage());
        }

        return back()->with('success', 'Payment confirmed. Your tickets have been emailed.');
    }

    // Decline a request (admin only)
    public function decline(EventRequest $eventRequest)
    {
        $user = Auth::user();
        if (! $user || !in_array($user->role, [Role::ADMIN, Role::APPROVAL_OFFICER], true)) {
            abort(403);
        }
        if ($eventRequest->status !== EventRequestStatus::PENDING->value) {
            return back()->with('warning', 'This request has already been ' . strtolower($this->formatStatusLabel($eventRequest)) . ' and cannot be changed.');
        }
        $eventRequest->status = EventRequestStatus::DECLINED->value;
        if (Schema::hasColumn('event_requests', 'admin_id')) {
            $eventRequest->admin_id = $user->id;
        }
        $eventRequest->save();

        return back()->with('success', 'Request declined.');
    }
}
