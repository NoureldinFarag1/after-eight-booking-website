<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EventRequest;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $data = $request->validate([
            'field1' => 'required|string',
            'field2' => 'required|string',
            'field3' => 'required|string',
            'field4' => 'required|string',
        ]);

        $data['event_id'] = $event->id;
        $data['user_id'] = Auth::id();
        $data['status'] = 'pending';

        EventRequest::create($data);

        return redirect()->route('events.show', $event)->with('success', 'Request submitted.');
    }

    // List logged-in user's requests
    public function index()
    {
        $requests = EventRequest::where('user_id', Auth::id())->latest()->get();
        return view('event_requests.index', compact('requests'));
    }

    // Admin listing of all requests
    public function adminIndex()
    {
        $user = Auth::user();
        if (! $user || ! ($user->isAdmin() ?? ($user->is_admin ?? false)) ) {
            abort(403);
        }
        $requests = EventRequest::latest()->get();
        return view('event_requests.admin_index', compact('requests'));
    }

    // Show a single request (owner or admin)
    public function show(EventRequest $eventRequest)
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }
        $isOwner = $user->id === $eventRequest->user_id;
        $isAdmin = ($user->isAdmin() ?? ($user->is_admin ?? false));
        if (! $isOwner && ! $isAdmin) {
            abort(403);
        }
        return view('event_requests.show', ['request' => $eventRequest]);
    }

    // Approve a request (admin only)
    public function approve(EventRequest $eventRequest)
    {
        $user = Auth::user();
        if (! $user || ! ($user->isAdmin() ?? ($user->is_admin ?? false)) ) {
            abort(403);
        }
        $eventRequest->status = 'approved';
        $eventRequest->save();

        return back()->with('success', 'Request approved.');
    }

    // Decline a request (admin only)
    public function decline(EventRequest $eventRequest)
    {
        $user = Auth::user();
        if (! $user || ! ($user->isAdmin() ?? ($user->is_admin ?? false)) ) {
            abort(403);
        }
        $eventRequest->status = 'declined';
        $eventRequest->save();

        return back()->with('success', 'Request declined.');
    }
}
