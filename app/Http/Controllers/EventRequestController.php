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

class EventRequestController extends Controller
{
    // Show the form to create a request for an event
    public function create(Event $event)
    {
        return view('events.request_form', compact('event'));
    }

    // Store a new request
    public function store(Request $request, Event $event)
    {
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

    // List logged-in user's requests
    public function index()
    {
        $requests = EventRequest::where('user_id', Auth::id())->latest()->get();
        return view('event_requests.index', compact('requests'));
    }

    // Admin listing of all requests
    public function adminIndex(Request $request)
    {
        $user = Auth::user();
        if (! $user || ($user->role !== Role::ADMIN)) {
            abort(403);
        }
        $status = $request->query('status'); // pending|approved|declined|null
        $q = trim((string) $request->query('q', ''));

    $query = EventRequest::with(['user', 'event', 'admin'])->latest();
        if (in_array($status, ['pending','approved','declined'], true)) {
            $query->where('status', $status);
        }
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->whereHas('user', function ($u) use ($q) {
                        $u->where('name', 'like', "%$q%")
                          ->orWhere('email', 'like', "%$q%");
                    })
                    ->orWhereHas('event', function ($e) use ($q) {
                        $e->where('title', 'like', "%$q%")
                          ->orWhere('name', 'like', "%$q%");
                    })
                    ->orWhere('primary_name', 'like', "%$q%")
                    ->orWhere('primary_email', 'like', "%$q%");
            });
        }

        $requests = $query->paginate(15)->appends([
            'status' => $status,
            'q' => $q !== '' ? $q : null,
        ]);

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
    $eventRequest->load(['event', 'user', 'admin']);
        return view('event_requests.show', ['eventRequest' => $eventRequest]);
    }

    // Approve a request (admin only)
    public function approve(EventRequest $eventRequest)
    {
        $user = Auth::user();
        if (! $user || ($user->role !== Role::ADMIN)) {
            abort(403);
        }
        if ($eventRequest->status !== 'pending') {
            return back()->with('warning', 'This request has already been ' . $eventRequest->status . ' and cannot be changed.');
        }
        $event = $eventRequest->event()->lockForUpdate()->first();

        // Safeguard: ensure enough seats remain
        $needed = $eventRequest->attendee_count ?? (1 + (is_array($eventRequest->guests) ? count($eventRequest->guests) : 0));
        if ($event->getAvailableSeatsAttribute() < $needed) {
            return back()->with('error', 'Not enough available seats to approve this request (needs ' . $needed . ').');
        }

        // Wrap in transaction to maintain consistency
        DB::transaction(function () use ($eventRequest, $user, $event) {

            // Decrement ticket type capacities if present in payload or columns
            if ($eventRequest->primary_ticket_type_id) {
                $primaryType = \App\Models\TicketType::where('id', $eventRequest->primary_ticket_type_id)->lockForUpdate()->first();
                if ($primaryType && !is_null($primaryType->capacity) && $primaryType->capacity > 0) {
                    $primaryType->capacity = max(0, $primaryType->capacity - 1);
                    $primaryType->save();
                }
            }
            if (is_array($eventRequest->guests)) {
                $grouped = collect($eventRequest->guests)
                    ->filter(fn($g) => is_array($g) && isset($g['ticket_type_id']))
                    ->groupBy('ticket_type_id')
                    ->map(fn($group) => count($group));
                foreach ($grouped as $ticketTypeId => $count) {
                    $tt = \App\Models\TicketType::where('id', $ticketTypeId)->lockForUpdate()->first();
                    if ($tt && !is_null($tt->capacity) && $tt->capacity > 0) {
                        $tt->capacity = max(0, $tt->capacity - $count);
                        $tt->save();
                    }
                }
            }

            $eventRequest->status = 'approved';
            if (Schema::hasColumn('event_requests', 'admin_id')) {
                $eventRequest->admin_id = $user->id;
            }
            $eventRequest->save();
        });

        return back()->with('success', 'Request approved.');
    }

    // Decline a request (admin only)
    public function decline(EventRequest $eventRequest)
    {
        $user = Auth::user();
        if (! $user || ($user->role !== Role::ADMIN)) {
            abort(403);
        }
        if ($eventRequest->status !== 'pending') {
            return back()->with('warning', 'This request has already been ' . $eventRequest->status . ' and cannot be changed.');
        }
        $eventRequest->status = 'declined';
        if (Schema::hasColumn('event_requests', 'admin_id')) {
            $eventRequest->admin_id = $user->id;
        }
        $eventRequest->save();

        return back()->with('success', 'Request declined.');
    }
}
