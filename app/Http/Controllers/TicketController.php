<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TicketController extends Controller
{
    use AuthorizesRequests;

    /**
     * Defense-in-depth: block manageable staff roles (operator, approval_officer, finance_officer) from personal ticket resources.
     * Primary enforcement handled by 'restrict_staff_personal' middleware on routes.
     */
    protected function denyIfStaffRole(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        if ($user && $user->role && $user->role->isManageableStaff()) {
            abort(403, 'Staff roles cannot access personal tickets.');
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    $this->denyIfStaffRole();
        /** @var User $user */
        $user = Auth::user();

        $tickets = $user->isAdmin()
            ? Ticket::with(['user', 'event', 'booking', 'type'])->latest()->paginate(15)
            : Ticket::with(['event', 'booking', 'type'])
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(15);

        return view('tickets.index', compact('tickets'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
    $this->denyIfStaffRole();
        $this->authorize('view', $ticket);

    $ticket->load(['event', 'booking', 'user', 'type']);

        return view('tickets.show', compact('ticket'));
    }

    /**
     * Scan QR code (operator functionality)
     */
    public function scan(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isOperator() && !$user->isAdmin()) {
            abort(403, 'Only operators can scan tickets.');
        }

        if ($request->has('qr_code')) {
            return $this->validateTicket($request->input('qr_code'));
        }

        return view('operator.scan');
    }

    /**
     * Validate ticket by QR code
     */
    public function validateTicket(string $qrCode)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isOperator() && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Use a transaction + row lock to avoid race conditions when two operators scan at the same time
        return DB::transaction(function () use ($qrCode, $user) {
            $ticket = Ticket::where('qr_code', $qrCode)
                ->lockForUpdate()
                ->with(['event', 'user', 'booking', 'type', 'scannedBy'])
                ->first();

            if (!$ticket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid ticket - QR code not found',
                    'status' => 'invalid'
                ]);
            }

            // Operators must be assigned to the event for which they're scanning
            if ($user->isOperator()) {
                $isAssigned = $ticket->event
                    ? $ticket->event->operators()->where('users.id', $user->id)->exists()
                    : false;
                if (!$isAssigned) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are not assigned to this event',
                        'status' => 'unauthorized'
                    ], 403);
                }
            }

            // If another operator just used it, we'll see it here due to the lock
            if ($ticket->isUsed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket already used',
                    'status' => 'used',
                    'ticket' => [
                        'ticket_number' => $ticket->ticket_number,
                        'event_title' => $ticket->event->title,
                        'user_name' => $ticket->user->name,
                        'ticket_type' => $ticket->type->name ?? null,
                        'scanned_at' => $ticket->scanned_at,
                        'scanned_by' => $ticket->scannedBy->name ?? 'Unknown'
                    ]
                ]);
            }

            // Expired or invalid check under lock
            if ($ticket->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket expired',
                    'status' => 'expired',
                    'ticket' => [
                        'ticket_number' => $ticket->ticket_number,
                        'event_title' => $ticket->event->title,
                        'event_date' => $ticket->event->event_date,
                        'user_name' => $ticket->user->name,
                        'ticket_type' => $ticket->type->name ?? null,
                    ]
                ]);
            }

            if (!$ticket->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket is not valid',
                    'status' => 'invalid',
                    'ticket' => [
                        'ticket_number' => $ticket->ticket_number,
                        'status' => $ticket->status->label(),
                        'event_title' => $ticket->event->title,
                        'user_name' => $ticket->user->name,
                        'ticket_type' => $ticket->type->name ?? null,
                    ]
                ]);
            }

            // Mark as used atomically inside the transaction
            $ticket->update([
                'status' => TicketStatus::USED,
                'scanned_at' => now(),
                'scanned_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ticket validated successfully',
                'status' => 'valid',
                'ticket' => [
                    'ticket_number' => $ticket->ticket_number,
                    'event_title' => $ticket->event->title,
                    'event_date' => $ticket->event->event_date,
                    'event_time' => $ticket->event->event_time,
                    'user_name' => $ticket->user->name,
                    'user_email' => $ticket->user->email,
                    'ticket_type' => $ticket->type->name ?? null,
                    'price' => $ticket->price,
                    'validated_at' => now(),
                    'validated_by' => $user->name
                ]
            ]);
        });
    }

    /**
     * Get QR code for ticket (user functionality)
     */
    public function qrCode(Ticket $ticket)
    {
    $this->denyIfStaffRole();
        $this->authorize('view', $ticket);

        // Generate QR code URL that points to validation endpoint
        $qrCodeData = route('tickets.validate', ['qr_code' => $ticket->qr_code]);

        return view('tickets.qr-code', compact('ticket', 'qrCodeData'));
    }

    /**
     * Download ticket as PDF
     */
    public function download(Ticket $ticket)
    {
    $this->denyIfStaffRole();
        $this->authorize('view', $ticket);

        // Here you would integrate with a PDF library like DOMPDF or similar
        // For now, we'll just return the ticket view
        return view('tickets.download', compact('ticket'));
    }

    /**
     * Admin: Update ticket status
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403, 'Only administrators can update ticket status.');
        }

        $validated = $request->validate([
            'status' => 'required|in:valid,used,cancelled,expired'
        ]);

        $ticket->update([
            'status' => TicketStatus::from($validated['status'])
        ]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket status updated successfully.');
    }

    /**
     * Verify ticket from QR code (public route)
     */
    public function verify(Ticket $ticket, string $code)
    {
        if ($ticket->qr_code !== $code) {
            return view('tickets.verify', [
                'valid' => false,
                'message' => 'Invalid QR code.'
            ]);
        }

        $ticket->load(['event', 'booking.user', 'type']);

        return view('tickets.verify', [
            'valid' => true,
            'ticket' => $ticket,
            'message' => 'Valid ticket.'
        ]);
    }

}
