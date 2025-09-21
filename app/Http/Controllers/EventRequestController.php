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
    public function create(Event $event)
    {
        return view('events.request_form', compact('event'));
    }

    public function store(Request $request, Event $event)
    {
        // check if user already reached max 5 requests
        $count = EventRequest::where('event_id', $event->id)
                             ->where('user_id', Auth::id())
                             ->count();

        if ($count >= 5) {
            return redirect()->back()->with('error', 'You have reached the maximum of 5 requests for this event.');
        }

        $request->validate([
            'field1' => 'required|string|max:255',
            'field2' => 'required|string|max:255',
            'field3' => 'required|string|max:255',
            'field4' => 'required|string|max:255',
        ]);

        EventRequest::create([
            'event_id' => $event->id,
            'user_id' => Auth::id(),
            'field1' => $request->field1,
            'field2' => $request->field2,
            'field3' => $request->field3,
            'field4' => $request->field4,
            'status' => 'pending',
        ]);

        return redirect()->route('events.show', $event)->with('success', 'Your request has been submitted!');
    }
}