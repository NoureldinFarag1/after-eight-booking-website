<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Event;
use App\Notifications\InvitationSentNotification;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InvitationController extends Controller
{
    protected $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Show all invitations.
     */
    public function index()
    {
        $invitations = Invitation::with(['event', 'sender'])
            ->latest()
            ->paginate(10);
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
            'event_id' => 'required|exists:events,id',
        ]);

        $validated['sender_id'] = Auth::id();

        $invitation = DB::transaction(function () use ($validated) {
            // Create the invitation
            $invitation = Invitation::create($validated);
            Log::info('Invitation created:', ['invitation_id' => $invitation->id]);

            // Generate QR code for the invitation
            $qrCodePath = $this->qrCodeService->generateInvitationQrCode($invitation);
            $invitation->update(['qr_code_path' => $qrCodePath]);
            Log::info('QR code generated:', ['path' => $qrCodePath]);

            // Send the email notification
            try {
                Notification::route('mail', $invitation->email)
                    ->notify(new InvitationSentNotification($invitation));
                Log::info('Email notification sent successfully');
            } catch (\Exception $e) {
                Log::error('Failed to send email notification:', ['error' => $e->getMessage()]);
                throw $e;
            }

            // Update invitation status to 'sent'
            $invitation->update(['status' => 'sent']);
            Log::info('Invitation status updated to sent');

            return $invitation;
        });

        return redirect()
            ->route('invitations.index')
            ->with('success', 'Invitation sent successfully with QR code!');
    }

    /**
     * Show invitation details and QR code
     */
    public function show(Invitation $invitation)
    {
        $invitation->load(['event', 'sender']);
        return view('invitations.show', compact('invitation'));
    }

    /**
     * Verify an invitation QR code (similar to ticket verification)
     */
    public function verify(Invitation $invitation, $code)
    {
        if ($invitation->qr_code !== $code) {
            return view('invitations.verify-result', [
                'success' => false,
                'message' => 'Invalid QR code.'
            ]);
        }

        if ($invitation->qr_status === 'cancelled') {
            return view('invitations.verify-result', [
                'success' => false,
                'message' => 'This invitation has been cancelled.'
            ]);
        }

        if ($invitation->qr_status === 'used') {
            return view('invitations.verify-result', [
                'success' => false,
                'message' => 'This invitation has already been used.',
                'invitation' => $invitation
            ]);
        }

        // Mark as used
        $invitation->update(['qr_status' => 'used']);

        return view('invitations.verify-result', [
            'success' => true,
            'message' => 'Invitation verified successfully!',
            'invitation' => $invitation
        ]);
    }
}
