<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingConfirmationNotification;
use Illuminate\Console\Command;

class TestBookingEmail extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:booking-email {email} {--booking-id=}';

    /**
     * The console command description.
     */
    protected $description = 'Test booking confirmation email with QR codes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $bookingId = $this->option('booking-id');

        // Find or create a test user
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User with email {$email} not found.");
            return 1;
        }

        // Get booking
        if ($bookingId) {
            $booking = Booking::find($bookingId);
            if (!$booking) {
                $this->error("Booking with ID {$bookingId} not found.");
                return 1;
            }
        } else {
            // Get the most recent booking for the user
            $booking = Booking::where('user_id', $user->id)
                ->with(['event', 'tickets.type'])
                ->latest()
                ->first();

            if (!$booking) {
                $this->error("No bookings found for user {$email}.");
                return 1;
            }
        }

        $this->info("Testing booking confirmation email...");
        $this->info("User: {$user->name} ({$user->email})");
        $this->info("Booking ID: {$booking->id}");
        $this->info("Event: {$booking->event->title}");
        $this->info("Tickets: {$booking->quantity}");
        $this->info("Total: \${$booking->total_amount}");

        try {
            // Load necessary relationships
            $booking->load(['event', 'tickets.type', 'user']);

            // Send notification
            $user->notify(new BookingConfirmationNotification($booking));

            $this->info("✅ Booking confirmation email sent successfully!");
            $this->info("📧 Check {$email} for the email with QR code attachments");

        } catch (\Exception $e) {
            $this->error("❌ Failed to send email: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
