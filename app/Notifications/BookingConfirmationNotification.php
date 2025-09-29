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

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        $this->qrCodePaths = [];
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
        $booking = $this->booking;
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
                'qrCodePaths' => $this->qrCodePaths
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
        foreach ($tickets as $ticket) {
            $this->qrCodePaths[$ticket->id] = $qrCodeService->generateTicketQrCode($ticket);
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
