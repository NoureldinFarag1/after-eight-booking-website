<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\EventRequest;

class RequestApprovedForPaymentNotification extends Notification
{
    use Queueable;

    public EventRequest $eventRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(EventRequest $eventRequest)
    {
        $this->eventRequest = $eventRequest;
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
        $event = $this->eventRequest->event;
        $deadline = $this->eventRequest->expires_at;

        $deadlineDisplay = $deadline
            ? $deadline->timezone(config('app.timezone'))->format('D, M j Y g:i A T')
            : '48 hours from approval';

        $eventDate = $event && $event->event_date instanceof \Illuminate\Support\Carbon
            ? $event->event_date->format('l, F j, Y')
            : 'TBD';

        $viewData = [
            'userName' => $notifiable->name ?? 'Guest',
            'eventName' => $event?->title ?? 'Upcoming Event',
            'deadlineDisplay' => $deadlineDisplay,
            'requestId' => $this->eventRequest->id,
            'primaryName' => $this->eventRequest->primary_name,
            'attendeeCount' => $this->eventRequest->attendee_count ?? 1,
            'eventDate' => $eventDate,
            'actionUrl' => route('event_requests.show', $this->eventRequest),
        ];

        return (new MailMessage)
            ->subject('Payment Required · Request #' . $this->eventRequest->id)
            ->view('emails.request-approved', $viewData);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
