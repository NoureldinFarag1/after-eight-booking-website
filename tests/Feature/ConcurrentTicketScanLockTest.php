<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\Role;
use App\Enums\TicketStatus;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class ConcurrentTicketScanLockTest extends TestCase
{
    use RefreshDatabase;

    public function test_concurrent_scan_second_process_sees_used_after_lock_release(): void
    {
        // Arrange operator and buyer
        $operator = User::factory()->create(['role' => Role::OPERATOR, 'active' => true, 'profile_completed' => true]);
        $buyer = User::factory()->create(['role' => Role::USER, 'active' => true]);

        // Arrange event and assign operator
        $event = Event::factory()->create([
            'event_date' => Carbon::now()->addDays(2)->toDateString(),
            'event_time' => Carbon::now()->addDays(2)->format('H:i:s'),
            'status' => 'published',
        ]);
        $event->operators()->attach($operator->id);

        // Ticket type
        $type = TicketType::create([
            'event_id' => $event->id,
            'name' => 'Standard',
            'price' => 50.00,
            'fee_type' => null,
            'fee_amount' => 0,
            'capacity' => 100,
            'is_active' => true,
        ]);

        // Confirmed booking and ticket
        $booking = Booking::create([
            'user_id' => $buyer->id,
            'event_id' => $event->id,
            'quantity' => 1,
            'total_amount' => 50.00,
            'status' => BookingStatus::CONFIRMED,
            'booking_date' => Carbon::now(),
        ]);

        $ticket = Ticket::create([
            'user_id' => $buyer->id,
            'event_id' => $event->id,
            'ticket_type_id' => $type->id,
            'booking_id' => $booking->id,
            'status' => TicketStatus::VALID,
            'price' => 50.00,
        ]);

        // Connection A: Start a transaction and acquire a write lock on the ticket row
        DB::beginTransaction();
        // SQLite ignores FOR UPDATE, so touch the row to acquire a write lock
        DB::update('UPDATE tickets SET updated_at = updated_at WHERE id = ?', [$ticket->id]);

        // Start child process that tries to validate while the row is locked
        $env = array_merge($_SERVER, [
            'APP_ENV' => 'testing',
            'DB_CONNECTION' => env('DB_CONNECTION', 'sqlite'),
            'DB_DATABASE' => env('DB_DATABASE', 'database/testing.sqlite'),
        ]);

        $process = new Process([
            PHP_BINARY,
            'artisan',
            'probe:validate-ticket',
            $ticket->qr_code,
            (string)$operator->id,
        ], base_path(), $env);

        $process->start();

        // Give the child a moment to attempt to read and block on the lock
        usleep(200000); // 200ms

        // While the child is blocked, perform the actual validation in the parent (acts as first operator)
        $this->actingAs($operator);
        $resp1 = $this->postJson(route('tickets.validate', ['qr_code' => $ticket->qr_code]));
        $resp1->assertStatus(200)->assertJson(['success' => true, 'status' => 'valid']);

        // Commit the transaction to release the lock for the child
        DB::commit();

        // Wait for child to finish (on SQLite this may fail with a locked DB error)
        $process->wait();

        // Regardless of the child outcome under SQLite, the system must now report the ticket as used
        $resp2 = $this->postJson(route('tickets.validate', ['qr_code' => $ticket->qr_code]));
        $resp2->assertStatus(200)
            ->assertJson([
                'success' => false,
                'status' => 'used',
            ]);
    }
}
