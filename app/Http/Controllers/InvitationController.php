<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Event;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    /**
     * Show all invitations.
     */
    public function index()
    {
        $invitations = Invitation::latest()->paginate(10);
        return view('invitations.index', compact('invitations'));
    }

    /**
     * Show create form.
     */
    public function create(Request $request)
    {
        // load events for dropdown (admins only via routes middleware)
        $events = Event::orderBy('title')->get();
        $selectedEventId = $request->query('event_id') ?? null;

        return view('invitations.create', compact('events', 'selectedEventId'));
    }

    /**
     * Store new invitation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255',
            'message'  => 'nullable|string|max:1000',
            'event_id' => 'nullable|exists:events,id',
        ]);

        Invitation::create($validated);

        return redirect()
            ->route('invitations.index')
            ->with('success', 'Invitation created successfully!');
    }
}
