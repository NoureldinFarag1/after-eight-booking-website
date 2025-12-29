<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\EventRequestStatus;
use App\Enums\Role;
use App\Enums\TicketStatus;
use App\Models\Booking;
use App\Models\Event;
use App\Models\EventRequest;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use App\Notifications\BookingConfirmationNotification;
use App\Notifications\RequestApprovedForPaymentNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EventRequestStatusTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(Role $role = Role::USER): User
    {
        return User::factory()->create([
            'role' => $role,
            'active' => true,
            'profile_completed' => true,
        ]);
    }

    private function makeEvent(array $overrides = []): Event
    {
        $defaults = [
            'event_date' => Carbon::now()->addDays(7)->toDateString(),
            'event_time' => Carbon::now()->addDays(7)->format('H:i:s'),
            'status' => 'published',
            'capacity' => 50,
            'type' => 'request',
        ];
        return Event::factory()->create(array_merge($defaults, $overrides));
    }

    private function addTypes(Event $event): array
    {
        $primary = TicketType::create([
            'event_id' => $event->id,
            'name' => 'Req-Primary',
            'price' => 30.00,
            'fee_type' => null,
            'fee_amount' => 0,
            'capacity' => 40,
            'is_active' => true,
        ]);
        $guest = TicketType::create([
            'event_id' => $event->id,
            'name' => 'Req-Guest',
            'price' => 25.00,
            'fee_type' => null,
            'fee_amount' => 0,
            'capacity' => 40,
            'is_active' => true,
        ]);
        return [$primary, $guest];
    }

    private function submitRequest(User $user, Event $event, TicketType $primaryType, ?TicketType $guestType = null): EventRequest
    {
        $this->actingAs($user);
        $payload = [
            'primary_name' => 'Alice',
            'primary_email' => 'alice@example.com',
            'primary_social_url' => 'https://instagram.com/alice',
            'primary_ticket_type_id' => $primaryType->id,
            'guests' => $guestType ? [[
                'name' => 'Bob',
                'email' => 'bob@example.com',
                'social_url' => 'https://facebook.com/bob',
                'ticket_type_id' => $guestType->id,
            ]] : [],
        ];
        $this->post(route('event-requests.store', $event), $payload)->assertRedirect(route('events.show', $event));
        return EventRequest::firstOrFail();
    }

    public function test_admin_approves_request_sets_awaiting_payment_and_deadline(): void
    {
        Notification::fake();

        $user = $this->makeUser(Role::USER);
    $admin = $this->makeUser(Role::ADMIN);
        $event = $this->makeEvent();
        [$primaryType, $guestType] = $this->addTypes($event);
        $req = $this->submitRequest($user, $event, $primaryType, $guestType);

        $this->actingAs($admin);
        $resp = $this->post(route('admin.event_requests.approve', $req));
        $resp->assertStatus(302);

        $req->refresh();
        $this->assertSame(EventRequestStatus::AWAITING_PAYMENT->value, $req->status);
        if (Schema::hasColumn('event_requests', 'approved_at')) {
            $this->assertNotNull($req->approved_at);
        }
        if (Schema::hasColumn('event_requests', 'expires_at')) {
            $this->assertNotNull($req->expires_at);
            $this->assertTrue($req->expires_at->greaterThan(Carbon::now()));
        }

        Notification::assertSentTo($user, RequestApprovedForPaymentNotification::class);
    }

    public function test_request_expires_after_deadline_when_viewed(): void
    {
        $user = $this->makeUser();
    $admin = $this->makeUser(Role::ADMIN);
        $event = $this->makeEvent();
        [$primaryType] = $this->addTypes($event);
        $req = $this->submitRequest($user, $event, $primaryType);

        // Approve first
        $this->actingAs($admin);
        $this->post(route('admin.event_requests.approve', $req))->assertStatus(302);
        $req->refresh();

        // Fast-forward expiry if supported
        if (Schema::hasColumn('event_requests', 'expires_at')) {
            $req->expires_at = Carbon::now()->subMinutes(1);
            $req->save();
        }

        // Viewing triggers expireIfPastDeadline in controller's show
        $this->actingAs($user);
        $this->get(route('event_requests.show', $req))->assertOk();
        $req->refresh();

        if (Schema::hasColumn('event_requests', 'expires_at')) {
            $this->assertSame(EventRequestStatus::EXPIRED->value, $req->status);
        } else {
            // If no expires_at column, status remains awaiting_payment
            $this->assertSame(EventRequestStatus::AWAITING_PAYMENT->value, $req->status);
        }
    }

    public function test_complete_payment_transitions_to_paid_and_creates_booking_and_tickets(): void
    {
        Notification::fake();

        $user = $this->makeUser();
    $admin = $this->makeUser(Role::ADMIN);
        $event = $this->makeEvent();
        [$primaryType, $guestType] = $this->addTypes($event);
        $req = $this->submitRequest($user, $event, $primaryType, $guestType);

        // Approve and ensure not expired
        $this->actingAs($admin);
        $this->post(route('admin.event_requests.approve', $req))->assertStatus(302);
        $req->refresh();

        // Owner completes payment
        $this->actingAs($user);
        $this->post(route('event_requests.pay', $req))->assertStatus(302);

        $req->refresh();
        $this->assertSame(EventRequestStatus::PAID->value, $req->status);

        $booking = Booking::latest()->first();
        $this->assertNotNull($booking);
        $this->assertSame($user->id, $booking->user_id);
        $this->assertSame($event->id, $booking->event_id);
        $this->assertSame(BookingStatus::CONFIRMED, $booking->status);

        $tickets = Ticket::where('booking_id', $booking->id)->get();
        $this->assertCount(2, $tickets, 'Should create tickets for primary + one guest');
        $this->assertTrue($tickets->every(fn($t) => $t->status === TicketStatus::VALID));
        $this->assertEquals(30.00 + 25.00, (float)$tickets->sum('price'));

        Notification::assertSentTo($user, BookingConfirmationNotification::class);
    }

    public function test_payment_attempt_after_expiry_rejects_and_marks_expired(): void
    {
        $user = $this->makeUser();
    $admin = $this->makeUser(Role::ADMIN);
        $event = $this->makeEvent();
        [$primaryType] = $this->addTypes($event);
        $req = $this->submitRequest($user, $event, $primaryType);

        $this->actingAs($admin);
        $this->post(route('admin.event_requests.approve', $req))->assertStatus(302);
        $req->refresh();

        // Force expiry
        if (Schema::hasColumn('event_requests', 'expires_at')) {
            $req->expires_at = Carbon::now()->subDay();
            $req->save();
        }

        $this->actingAs($user);
        $resp = $this->post(route('event_requests.pay', $req));
        $resp->assertStatus(302);
        $this->assertTrue(session()->has('error'));

        $req->refresh();
        if (Schema::hasColumn('event_requests', 'expires_at')) {
            $this->assertSame(EventRequestStatus::EXPIRED->value, $req->status);
        } else {
            $this->assertSame(EventRequestStatus::AWAITING_PAYMENT->value, $req->status);
        }

        $this->assertSame(0, Booking::count(), 'No booking should be created after expiry');
        $this->assertSame(0, Ticket::count(), 'No tickets should be created after expiry');
    }

    public function test_event_request_does_not_increase_booking_quantity_until_paid(): void
    {
        Notification::fake();

        $user = $this->makeUser(Role::USER);
        $admin = $this->makeUser(Role::ADMIN);
        $event = $this->makeEvent(['capacity' => 100]);
        [$primaryType, $guestType] = $this->addTypes($event);

        // Submit request (pending) for primary + 1 guest
        $req = $this->submitRequest($user, $event, $primaryType, $guestType);
        $event->refresh();
        $this->assertSame(0, $event->bookings()->sum('quantity'), 'Pending request should not create bookings');

        // Approve (awaiting_payment) still no booking added
        $this->actingAs($admin);
        $this->post(route('admin.event_requests.approve', $req))->assertStatus(302);
        $req->refresh();
        $event->refresh();
        $this->assertSame(EventRequestStatus::AWAITING_PAYMENT->value, $req->status);
        $this->assertSame(0, $event->bookings()->sum('quantity'), 'Awaiting payment should not yet create bookings');

        // Complete payment -> booking + tickets created; quantity reflects attendees
        $this->actingAs($user);
        $this->post(route('event_requests.pay', $req))->assertStatus(302);
        $req->refresh();
        $event->refresh();
        $this->assertSame(EventRequestStatus::PAID->value, $req->status);

        // Derive expected attendee count (primary + guests)
        $expectedAttendees = 2; // primary + one guest in this test
        if (Schema::hasColumn('event_requests','attendee_count')) {
            $expectedAttendees = (int) $req->attendee_count;
        }
        $this->assertSame($expectedAttendees, $event->bookings()->sum('quantity'), 'Booking quantity should match attendee count only after payment');

        // Tickets created
        $booking = Booking::latest()->first();
        $this->assertNotNull($booking);
        $tickets = Ticket::where('booking_id', $booking->id)->get();
        $this->assertCount($expectedAttendees, $tickets);
        $this->assertTrue($tickets->every(fn($t) => $t->status === TicketStatus::VALID));
    }

    private function submitMultiGuestRequest(User $user, Event $event, TicketType $primaryType, array $guestTypes): EventRequest
    {
        $this->actingAs($user);
        $guestsPayload = [];
        $i = 0;
        foreach ($guestTypes as $gt) {
            $guestsPayload[] = [
                'name' => 'Guest'.(++$i),
                'email' => 'guest'.$i.'@example.com',
                'social_url' => 'https://instagram.com/guest'.$i,
                'ticket_type_id' => $gt->id,
            ];
        }
        $payload = [
            'primary_name' => 'MultiPrimary',
            'primary_email' => 'multi-primary@example.com',
            'primary_social_url' => 'https://facebook.com/multiprimary',
            'primary_ticket_type_id' => $primaryType->id,
            'guests' => $guestsPayload,
        ];
        $this->post(route('event-requests.store', $event), $payload)->assertRedirect(route('events.show', $event));
        return EventRequest::latest('id')->firstOrFail();
    }

    public function test_capacity_holds_reduce_available_seats_without_creating_bookings(): void
    {
        $user1 = $this->makeUser();
        $user2 = $this->makeUser();
        $admin = $this->makeUser(Role::ADMIN);
        $event = $this->makeEvent(['capacity' => 10]);

        // Two ticket types for simplicity (reuse addTypes)
        [$primaryType, $guestType] = $this->addTypes($event);

        // First request: primary + 2 guests (3 seats)
        $req1 = $this->submitMultiGuestRequest($user1, $event, $primaryType, [$guestType, $guestType]);
        $this->actingAs($admin);
        $this->post(route('admin.event_requests.approve', $req1))->assertStatus(302);
        $req1->refresh();
        $event->refresh();
        $this->assertSame(EventRequestStatus::AWAITING_PAYMENT->value, $req1->status);
        $this->assertSame(0, $event->bookings()->sum('quantity'), 'No bookings yet for awaiting_payment');
        $heldSeats1 = Schema::hasColumn('event_requests','attendee_count') ? (int)$req1->attendee_count : 3;
        $this->assertSame(10 - $heldSeats1, $event->available_seats, 'Available seats reduced by held seats');

        // Second request: primary + 4 guests (5 seats)
        $req2 = $this->submitMultiGuestRequest($user2, $event, $primaryType, [$guestType,$guestType,$guestType,$guestType]);
        $this->actingAs($admin);
        $this->post(route('admin.event_requests.approve', $req2))->assertStatus(302);
        $req2->refresh();
        $event->refresh();
        $heldSeats2 = Schema::hasColumn('event_requests','attendee_count') ? (int)$req2->attendee_count : 5;
        $this->assertSame(10 - ($heldSeats1 + $heldSeats2), $event->available_seats, 'Available seats reflect cumulative holds');
        $this->assertSame(0, $event->bookings()->sum('quantity'));

        // Third request should fail if requesting more than remaining (attempt 3 seats when only 2 left)
        $user3 = $this->makeUser();
        $req3 = $this->submitMultiGuestRequest($user3, $event, $primaryType, [$guestType, $guestType]); // 3 seats
        $this->actingAs($admin);
        $resp = $this->post(route('admin.event_requests.approve', $req3));
        // Expect redirect back with error flash (status remains pending)
        $resp->assertStatus(302);
        $req3->refresh();
        $this->assertSame(EventRequestStatus::PENDING->value, $req3->status, 'Insufficient capacity blocks approval');
        $event->refresh();
        $this->assertSame(10 - ($heldSeats1 + $heldSeats2), $event->available_seats, 'Held seats unchanged after failed approval');
    }

    public function test_multi_guest_payment_releases_hold_and_creates_correct_booking(): void
    {
        Notification::fake();
        $user = $this->makeUser();
        $admin = $this->makeUser(Role::ADMIN);
        $event = $this->makeEvent(['capacity' => 15]);
        [$primaryType, $guestType] = $this->addTypes($event);

        // Request with primary + 3 guests (4 seats)
        $req = $this->submitMultiGuestRequest($user, $event, $primaryType, [$guestType,$guestType,$guestType]);
        $this->actingAs($admin);
        $this->post(route('admin.event_requests.approve', $req))->assertStatus(302);
        $req->refresh();
        $event->refresh();
        $heldSeats = Schema::hasColumn('event_requests','attendee_count') ? (int)$req->attendee_count : 4;
        $this->assertSame(15 - $heldSeats, $event->available_seats, 'Hold reduces available seats');
        $this->assertSame(0, $event->bookings()->sum('quantity'));

        // Complete payment
        $this->actingAs($user);
        $this->post(route('event_requests.pay', $req))->assertStatus(302);
        $req->refresh();
        $event->refresh();
        $this->assertSame(EventRequestStatus::PAID->value, $req->status);

        // Available seats should still be capacity - attendees (now via bookings instead of hold)
        $this->assertSame(15 - $heldSeats, $event->available_seats, 'Booking replaces hold without changing consumed seats');
        $this->assertSame($heldSeats, $event->bookings()->sum('quantity'), 'Booking quantity equals attendee count');

        $booking = Booking::latest()->first();
        $this->assertNotNull($booking);
        $tickets = Ticket::where('booking_id', $booking->id)->get();
        $this->assertCount($heldSeats, $tickets);
        $this->assertTrue($tickets->every(fn($t) => $t->status === TicketStatus::VALID));
        Notification::assertSentTo($user, BookingConfirmationNotification::class);
    }
}
