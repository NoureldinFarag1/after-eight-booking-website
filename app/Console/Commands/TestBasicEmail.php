<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TestBasicBookingEmail extends Notification
{
    public $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Basic Booking Test')
            ->line('This is a basic booking confirmation test.')
            ->line('Booking ID: ' . $this->booking->id)
            ->line('Event: ' . $this->booking->event->title)
            ->line('Thank you!');
    }
}

class TestBasicEmail extends Command
{
    protected $signature = 'test:basic-email {email}';
    protected $description = 'Test basic booking email without QR codes';

    public function handle()
    {
        $email = $this->argument('email');

        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User with email {$email} not found.");
            return 1;
        }

        $booking = Booking::where('user_id', $user->id)
            ->with('event')
            ->latest()
            ->first();

        if (!$booking) {
            $this->error("No bookings found for user {$email}.");
            return 1;
        }

        try {
            $user->notify(new TestBasicBookingEmail($booking));
            $this->info("✅ Basic email sent successfully!");
        } catch (\Exception $e) {
            $this->error("❌ Failed to send email: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
