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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class ConcurrentTicketScanTest extends TestCase
{
    use RefreshDatabase;

    public function test_double_scan_returns_used_on_second_attempt(): void
    {
        // Create operator and regular user
        /** @var User $operator */
    $operator = User::factory()->create(['role' => Role::OPERATOR, 'active' => true, 'profile_completed' => true]);
        /** @var User $buyer */
    $buyer = User::factory()->create(['role' => Role::USER, 'active' => true]);

        // Create future event and assign operator
        /** @var Event $event */
        $event = Event::factory()->create([
            'event_date' => Carbon::now()->addDays(2)->toDateString(),
            'event_time' => Carbon::now()->addDays(2)->format('H:i:s'),
            'status' => 'published',
        ]);
        $event->operators()->attach($operator->id);

        // Create a ticket type
        /** @var TicketType $type */
        $type = TicketType::create([
            'event_id' => $event->id,
            'name' => 'VIP',
            'description' => 'VIP Access',
            'price' => 100.00,
            'fee_type' => null,
            'fee_amount' => 0,
            'capacity' => 100,
            'is_active' => true,
        ]);

        // Create a confirmed booking
        /** @var Booking $booking */
        $booking = Booking::create([
            'user_id' => $buyer->id,
            'event_id' => $event->id,
            'quantity' => 1,
            'total_amount' => 100.00,
            'status' => BookingStatus::CONFIRMED,
            'booking_date' => Carbon::now(),
        ]);

        // Create a valid ticket
        /** @var Ticket $ticket */
        $ticket = Ticket::create([
            'user_id' => $buyer->id,
            'event_id' => $event->id,
            'ticket_type_id' => $type->id,
            'booking_id' => $booking->id,
            'status' => TicketStatus::VALID,
            'price' => 100.00,
        ]);

        $this->actingAs($operator);

        // First scan should validate successfully
        $resp1 = $this->postJson(route('tickets.validate', ['qr_code' => $ticket->qr_code]));
        $resp1->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'valid',
            ]);

        // Refresh to ensure DB changes are applied
        $ticket->refresh();
        $this->assertTrue($ticket->isUsed(), 'Ticket should be marked as used after first scan');

        // Second immediate scan should report already used
        $resp2 = $this->postJson(route('tickets.validate', ['qr_code' => $ticket->qr_code]));
        $resp2->assertStatus(200)
            ->assertJson([
                'success' => false,
                'status' => 'used',
            ]);
    }
}
