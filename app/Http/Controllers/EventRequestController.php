<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EventRequest;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EventRequestController extends Controller
{
    use AuthorizesRequests;
    public function create(Event $event)
    {
        if ($event->type !== 'request') {
            abort(404, 'This event is not of type request.');
        }

        return view('event_requests.create', compact('event'));
    }

    public function store(Request $request, Event $event)
    {
        $user = Auth::user();

        // max 5 requests per user (across all events)
        $existingCount = EventRequest::where('user_id', $user->id)->count();
        if ($existingCount >= 5) {
            return redirect()->back()->with('error', 'You have reached the maximum allowed requests (5).');
        }

        $validated = $request->validate([
            'field_1' => 'required|string|max:100',
            'field_2' => 'required|string|max:255',
            'field_3' => 'required|string|max:500',
            'field_4' => 'required|string|max:500',
        ]);

        $er = EventRequest::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'payload' => $validated,
            'status' => 'pending',
        ]);

        return redirect()->route('event_requests.show', $er->id)->with('success', 'Request submitted successfully.');
    }

    public function show(EventRequest $eventRequest)
    {
        $this->authorize('view', $eventRequest);
        return view('event_requests.show', ['request' => $eventRequest]);
    }

    public function myRequests()
    {
        $user = Auth::user();
        $requests = EventRequest::where('user_id', $user->id)->with('event')->latest()->get();
        return view('event_requests.index', compact('requests'));
    }

    public function adminIndex()
    {
        $this->authorize('admin');
        $requests = EventRequest::with('user','event')->latest()->paginate(30);
        return view('event_requests.admin_index', compact('requests'));
    }

    public function approve(EventRequest $eventRequest)
    {
        $this->authorize('admin');
        $eventRequest->update([
            'status' => 'approved',
            'admin_id' => auth()->id(),
        ]);
        return redirect()->back()->with('success', 'Request approved.');
    }

    public function decline(EventRequest $eventRequest)
    {
        $this->authorize('admin');
        $eventRequest->update([
            'status' => 'declined',
            'admin_id' => auth()->id(),
        ]);
        return redirect()->back()->with('success', 'Request declined.');
    }
}