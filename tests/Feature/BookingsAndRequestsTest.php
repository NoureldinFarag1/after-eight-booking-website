<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Event;
use App\Models\Booking;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\EventRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Enums\EventStatus;
use App\Enums\BookingStatus;
use App\Enums\TicketStatus;

class BookingsAndRequestsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure factories exist or create minimal models inline
    }

    public function test_user_sees_bookings_and_requests_sections(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'capacity' => 100,
            'status' => EventStatus::PUBLISHED,
            'event_date' => now()->addDay()->toDateString(),
            'event_time' => now()->addDay(),
        ]);

        // Create booking
        $booking = Booking::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'quantity' => 2,
            'total_amount' => 0,
            'status' => BookingStatus::CONFIRMED,
            'booking_date' => now(),
        ]);
        // minimal tickets
        for ($i=0;$i<2;$i++) {
            Ticket::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'booking_id' => $booking->id,
                'status' => TicketStatus::VALID,
                'price' => 0,
            ]);
        }

        // Create a request
        EventRequest::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'primary_name' => 'John Test',
            'primary_email' => 'john@example.com',
            'primary_social_url' => null,
            'primary_ticket_type_id' => null,
            'guests' => [],
            'attendee_count' => 1,
            'payload' => [],
        ]);

        $this->actingAs($user);

        $response = $this->get(route('bookings.index'));
        $response->assertStatus(200)
            ->assertSee('My Event Requests')
            ->assertSee('Attendees:')
            ->assertSee('My Bookings');
    }

    public function test_event_requests_pagination_separate_from_bookings(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'capacity' => 200,
            'status' => EventStatus::PUBLISHED,
            'event_date' => now()->addDay()->toDateString(),
            'event_time' => now()->addDay(),
        ]);

        // Create 20 requests
        for ($i=0; $i<20; $i++) {
            EventRequest::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'status' => 'pending',
                'primary_name' => 'User '.$i,
                'primary_email' => 'user'.$i.'@example.com',
                'primary_social_url' => null,
                'primary_ticket_type_id' => null,
                'guests' => [],
                'attendee_count' => 1,
                'payload' => [],
            ]);
        }

        $this->actingAs($user);

        $response = $this->get(route('bookings.index'));
        $response->assertStatus(200);
        // Should show first page (12 requests)
        $response->assertSee('My Event Requests');
        $this->assertStringContainsString('requests_page=2', $response->getContent());

        $responsePage2 = $this->get(route('bookings.index', ['requests_page' => 2]));
        $responsePage2->assertStatus(200);
    }
}
