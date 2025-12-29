<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Services\QrCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class BookingConfirmationNotification extends Notification
{
    use Queueable;

    public Booking $booking;
    public array $qrCodePaths;
    public array $qrDataUrls;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        $this->qrCodePaths = [];
        $this->qrDataUrls = [];
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        // Ensure related models are available to the view without N+1 queries
        $booking = $this->booking->loadMissing(['event', 'tickets.type']);
        $event = $booking->event;

        // Generate QR codes just before sending
        $this->generateQrCodes();

        $message = (new MailMessage)
            ->subject('Booking Confirmation - ' . $event->title)
            ->view('emails.booking-confirmation', [
                'booking' => $booking,
                'event' => $event,
                'tickets' => $booking->tickets,
                'user' => $notifiable,
                'qrCodePaths' => $this->qrCodePaths,
                'qrDataUrls' => $this->qrDataUrls,
            ]);

        // Note: QR codes are now embedded in email template as base64 images
        // Attachments disabled for Resend compatibility
        /*
        foreach ($this->qrCodePaths as $ticketId => $path) {
            $fullPath = Storage::disk('public')->path($path);
            if (file_exists($fullPath)) {
                $message->attach($fullPath, [
                    'as' => "ticket-{$ticketId}-qr.png",
                    'mime' => 'image/png',
                ]);
            }
        }
        */

        return $message;
    }

    /**
     * Generate QR codes for all tickets in the booking
     */
    private function generateQrCodes(): void
    {
        $qrCodeService = app(QrCodeService::class);
        $tickets = $this->booking->tickets;

        $this->qrCodePaths = [];
        $this->qrDataUrls = [];
        foreach ($tickets as $ticket) {
            $path = $qrCodeService->generateTicketQrCode($ticket);
            $this->qrCodePaths[$ticket->id] = $path;
            // Try to create base64 data URL for inline embedding
            try {
                $binary = Storage::disk('public')->get($path);
                if ($binary) {
                    $this->qrDataUrls[$ticket->id] = 'data:image/png;base64,' . base64_encode($binary);
                }
            } catch (\Throwable $e) {
                // ignore if file missing; template will fall back to public URL if available
            }
        }
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'event_id' => $this->booking->event_id,
            'booking_reference' => $this->booking->booking_reference ?? 'BK-' . $this->booking->id,
        ];
    }
}
