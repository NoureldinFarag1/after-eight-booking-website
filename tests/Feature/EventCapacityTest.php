<?php

namespace Tests\Feature;

use App\Enums\EventRequestStatus;
use App\Enums\EventStatus;
use App\Enums\Role;
use App\Models\Booking;
use App\Models\Event;
use App\Models\EventRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventCapacityTest extends TestCase
{
    use RefreshDatabase;

    public function test_available_seats_subtracts_booked_and_active_holds(): void
    {
        $event = Event::factory()->create([
            'capacity' => 10,
            'status' => EventStatus::PUBLISHED,
            'event_date' => now()->addDay()->toDateString(),
            'event_time' => now()->addDay(),
        ]);

        // Initially full capacity
        $this->assertSame(10, $event->fresh()->available_seats);

        // Book 3 seats
        Booking::create([
            'user_id' => User::factory()->create()->id,
            'event_id' => $event->id,
            'quantity' => 3,
            'total_amount' => 0,
            'status' => \App\Enums\BookingStatus::CONFIRMED,
            'booking_date' => now(),
        ]);
        $this->assertSame(7, $event->fresh()->available_seats);

        // Create an active hold for 2 seats
        EventRequest::create([
            'event_id' => $event->id,
            'user_id' => User::factory()->create()->id,
            'status' => EventRequestStatus::AWAITING_PAYMENT->value,
            'approved_at' => now(),
            'expires_at' => now()->addHour(),
            'primary_name' => 'John',
            'primary_email' => 'john@example.com',
            'primary_social_url' => 'https://instagram.com/example',
            'primary_ticket_type_id' => null,
            'guests' => [],
            'attendee_count' => 2,
        ]);
        $this->assertSame(5, $event->fresh()->available_seats);

        // Expire the hold
        EventRequest::where('event_id', $event->id)->update([
            'status' => EventRequestStatus::EXPIRED->value,
            'expires_at' => now()->subMinute(),
        ]);
        $this->assertSame(7, $event->fresh()->available_seats);
    }

    public function test_approve_creates_hold_and_respects_capacity(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $this->actingAs($admin);

        $event = Event::factory()->create([
            'capacity' => 5,
            'status' => EventStatus::PUBLISHED,
            'event_date' => now()->addDay()->toDateString(),
            'event_time' => now()->addDay(),
        ]);

        // Book 3 seats already
        Booking::create([
            'user_id' => User::factory()->create()->id,
            'event_id' => $event->id,
            'quantity' => 3,
            'total_amount' => 0,
            'status' => \App\Enums\BookingStatus::CONFIRMED,
            'booking_date' => now(),
        ]);

        $this->assertSame(2, $event->fresh()->available_seats);

        // Pending request for 2 seats
        $req1 = EventRequest::create([
            'event_id' => $event->id,
            'user_id' => User::factory()->create()->id,
            'status' => EventRequestStatus::PENDING->value,
            'primary_name' => 'Alice',
            'primary_email' => 'alice@example.com',
            'primary_social_url' => 'https://instagram.com/example',
            'primary_ticket_type_id' => null,
            'guests' => [],
            'attendee_count' => 2,
        ]);

        // Approve should succeed and create a 48h hold
        $resp = $this->post(route('admin.event_requests.approve', $req1));
        $resp->assertRedirect();
        $this->assertEquals(EventRequestStatus::AWAITING_PAYMENT->value, $req1->fresh()->status);
        $this->assertNotNull($req1->fresh()->expires_at);
        $this->assertSame(0, $event->fresh()->available_seats);

        // Another pending request for 1 seat should fail due to no capacity
        $req2 = EventRequest::create([
            'event_id' => $event->id,
            'user_id' => User::factory()->create()->id,
            'status' => EventRequestStatus::PENDING->value,
            'primary_name' => 'Bob',
            'primary_email' => 'bob@example.com',
            'primary_social_url' => 'https://instagram.com/example',
            'primary_ticket_type_id' => null,
            'guests' => [],
            'attendee_count' => 1,
        ]);

        $resp2 = $this->post(route('admin.event_requests.approve', $req2));
        $resp2->assertSessionHas('error');
        $this->assertEquals(EventRequestStatus::PENDING->value, $req2->fresh()->status);

        // Simulate expiration of first hold, capacity should free up
        $this->artisan('event-requests:simulate-expire', ['eventRequestId' => $req1->id])->assertSuccessful();
        $this->assertSame(2, $event->fresh()->available_seats);

        // Now approving second should succeed
        $resp3 = $this->post(route('admin.event_requests.approve', $req2));
        $resp3->assertRedirect();
        $this->assertEquals(EventRequestStatus::AWAITING_PAYMENT->value, $req2->fresh()->status);
        $this->assertSame(1, $event->fresh()->available_seats);
    }
}
