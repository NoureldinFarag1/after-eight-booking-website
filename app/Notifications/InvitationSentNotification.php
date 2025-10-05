<?php

namespace App\Notifications;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

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

        if (!$event) {
            // Fallback for invitations without events (shouldn't happen with new validation)
            return (new MailMessage)
                ->subject("You've received an invitation")
                ->greeting("Hello {$this->invitation->name}!")
                ->line($this->invitation->sender->name . " has sent you an invitation.")
                ->line($this->invitation->message ?: "No additional message provided.")
                ->line('Thank you!');
        }

        $subject = "Invitation to {$event->title}";
        $eventDate = Carbon::parse($event->event_date);
        $eventTime = Carbon::parse($event->event_time);

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$this->invitation->name}!")
            ->line($this->invitation->sender->name . " has invited you to attend the following event:")
            ->line("**Event:** {$event->title}")
            ->line("**Date:** " . $eventDate->format('F j, Y'))
            ->line("**Time:** " . $eventTime->format('g:i A'))
            ->line("**Location:** {$event->location}");

        // Add custom message if provided
        if ($this->invitation->message) {
            $mail->line("**Personal Message:**")
                 ->line($this->invitation->message);
        }

        $mail->line('Please save this email and use the attached QR code for event entry.')
             ->line('Thank you!');

        // Attach QR code if it exists
        if ($this->invitation->qr_code_path && Storage::disk('public')->exists($this->invitation->qr_code_path)) {
            $qrCodePath = Storage::disk('public')->path($this->invitation->qr_code_path);
            $mail->attach($qrCodePath, [
                'as' => 'invitation-qr-code.png',
                'mime' => 'image/png',
            ]);
        }

        return $mail;
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
