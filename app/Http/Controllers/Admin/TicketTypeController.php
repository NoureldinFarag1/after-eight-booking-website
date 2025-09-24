<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TicketTypeController extends Controller
{
    use AuthorizesRequests;
    public function index(Event $event)
    {
        $types = $event->ticketTypes()->orderBy('name')->paginate(20);
        return view('admin.ticket_types.index', compact('event', 'types'));
    }

    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => [
                'required','string','max:100',
                Rule::unique('ticket_types', 'name')->where(fn($q) => $q->where('event_id', $event->id)),
            ],
            'description' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'capacity' => 'nullable|integer|min:0',
            'is_active' => 'required|in:0,1',
        ]);

        $validated['is_active'] = (int) $request->input('is_active', 1);
        $event->ticketTypes()->create($validated);

        return redirect()->route('admin.events.ticket-types.index', $event)
            ->with('success', 'Ticket type created.');
    }

    public function update(Request $request, Event $event, TicketType $ticketType)
    {
        abort_unless($ticketType->event_id === $event->id, 404);

        $validated = $request->validate([
            'name' => [
                'required','string','max:100',
                Rule::unique('ticket_types', 'name')
                    ->ignore($ticketType->id)
                    ->where(fn($q) => $q->where('event_id', $event->id)),
            ],
            'description' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'capacity' => 'nullable|integer|min:0',
            'is_active' => 'required|in:0,1',
        ]);

        $validated['is_active'] = (int) $request->input('is_active', 1);
        $ticketType->update($validated);

        return redirect()->route('admin.events.ticket-types.index', $event)
            ->with('success', 'Ticket type updated.');
    }

    public function destroy(Event $event, TicketType $ticketType)
    {
        abort_unless($ticketType->event_id === $event->id, 404);
        $ticketType->delete();
        return redirect()->route('admin.events.ticket-types.index', $event)
            ->with('success', 'Ticket type deleted.');
    }
}
