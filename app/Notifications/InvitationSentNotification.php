<?php

namespace App\Notifications;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon; // retained if used elsewhere; safe to remove if unused

class InvitationSentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $invitation;

    /**
     * Create a new notification instance.
     */
    public function __construct(Invitation $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $event = $this->invitation->event;

        $subject = $event
            ? "Invitation to {$event->title}"
            : "You've received an invitation";

        // Prepare QR sources: inline base64 data URL preferred; public URL as fallback
        $qrUrl = null;
        $qrDataUrl = null;
        if ($this->invitation->qr_code_path && Storage::disk('public')->exists($this->invitation->qr_code_path)) {
            // Public URL (may fail to load in some clients if not publicly reachable)
            $qrUrl = rtrim(config('app.url'), '/') . Storage::url($this->invitation->qr_code_path);
            // Inline base64 so it renders without external fetch
            try {
                $binary = Storage::disk('public')->get($this->invitation->qr_code_path);
                if ($binary) {
                    $qrDataUrl = 'data:image/png;base64,' . base64_encode($binary);
                }
            } catch (\Throwable $e) {
                // Silently ignore; we'll fall back to $qrUrl
            }
        }

        $message = (new MailMessage)
            ->subject($subject)
            ->view('emails.invitation', [
                'invitation' => $this->invitation,
                'qrUrl' => $qrUrl,
                'qrDataUrl' => $qrDataUrl,
            ]);

        // Do not attach the QR code; we embed it inline in the email using the public URL

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invitation_id' => $this->invitation->id,
            'sender_name' => $this->invitation->sender->name,
            'event_title' => $this->invitation->event->title ?? null,
        ];
    }
}
