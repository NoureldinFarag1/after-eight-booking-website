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
        // Ensure allocated capacities across types do not exceed event capacity
        if (!is_null($validated['capacity'] ?? null)) {
            $allocated = $event->ticketTypes()->whereNotNull('capacity')->sum('capacity');
            $remaining = max(0, $event->capacity - $allocated);
            if ($validated['capacity'] > $remaining) {
                return back()
                    ->withErrors(['capacity' => 'Allocated capacity exceeds event capacity. Remaining available: ' . $remaining])
                    ->withInput();
            }
        }
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
        // Validate capacity allocation on update
        if (array_key_exists('capacity', $validated) && !is_null($validated['capacity'])) {
            // Prevent setting capacity below already sold tickets for this type
            $soldCount = $ticketType->tickets()
                ->where('status', '!=', \App\Enums\TicketStatus::CANCELLED)
                ->count();
            if ($validated['capacity'] < $soldCount) {
                return back()
                    ->withErrors(['capacity' => 'Capacity cannot be less than already sold tickets: ' . $soldCount])
                    ->withInput();
            }

            // Ensure total allocated does not exceed event capacity
            $allocated = $event->ticketTypes()
                ->whereNotNull('capacity')
                ->where('id', '!=', $ticketType->id)
                ->sum('capacity');
            $remaining = max(0, $event->capacity - $allocated);
            if ($validated['capacity'] > $remaining) {
                return back()
                    ->withErrors(['capacity' => 'Allocated capacity exceeds event capacity. Remaining available: ' . $remaining])
                    ->withInput();
            }
        }
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
