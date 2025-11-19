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
use Tests\TestCase;

class UnassignedOperatorScanTest extends TestCase
{
    use RefreshDatabase;

    public function test_unassigned_operator_cannot_validate_and_ticket_remains_valid(): void
    {
        // Create an operator (active) and a buyer
        $operator = User::factory()->create(['role' => Role::OPERATOR, 'active' => true, 'profile_completed' => true]);
        $buyer = User::factory()->create(['role' => Role::USER, 'active' => true]);

        // Create two future events: A (ticket belongs here) and B (operator assigned here)
        $eventA = Event::factory()->create([
            'event_date' => Carbon::now()->addDays(2)->toDateString(),
            'event_time' => Carbon::now()->addDays(2)->format('H:i:s'),
            'status' => 'published',
        ]);
        $eventB = Event::factory()->create([
            'event_date' => Carbon::now()->addDays(3)->toDateString(),
            'event_time' => Carbon::now()->addDays(3)->format('H:i:s'),
            'status' => 'published',
        ]);

        // Assign operator to Event B only (not A)
        $eventB->operators()->attach($operator->id);

        // Create ticket type for Event A
        $typeA = TicketType::create([
            'event_id' => $eventA->id,
            'name' => 'General',
            'price' => 25.00,
            'fee_type' => null,
            'fee_amount' => 0,
            'capacity' => 200,
            'is_active' => true,
        ]);

        // Confirmed booking and ticket on Event A
        $booking = Booking::create([
            'user_id' => $buyer->id,
            'event_id' => $eventA->id,
            'quantity' => 1,
            'total_amount' => 25.00,
            'status' => BookingStatus::CONFIRMED,
            'booking_date' => Carbon::now(),
        ]);

        $ticket = Ticket::create([
            'user_id' => $buyer->id,
            'event_id' => $eventA->id,
            'ticket_type_id' => $typeA->id,
            'booking_id' => $booking->id,
            'status' => TicketStatus::VALID,
            'price' => 25.00,
        ]);

        // Act as unassigned operator attempting to validate Event A ticket
        $this->actingAs($operator);
        $resp = $this->postJson(route('tickets.validate', ['qr_code' => $ticket->qr_code]));

        // Expect a 403 unauthorized with appropriate message and status
        $resp->assertStatus(403)
            ->assertJson([
                'success' => false,
                'status' => 'unauthorized',
            ]);

        // Ensure ticket remains VALID (unchanged)
        $ticket->refresh();
        $this->assertEquals(TicketStatus::VALID, $ticket->status, 'Ticket status should remain VALID');
        $this->assertNull($ticket->scanned_at, 'Ticket should not have a scanned_at timestamp');
        $this->assertNull($ticket->scanned_by, 'Ticket should not have a scanned_by user');
    }
}
