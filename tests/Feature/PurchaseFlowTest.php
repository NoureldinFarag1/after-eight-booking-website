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
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use App\Notifications\BookingConfirmationNotification;

class PurchaseFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeEvent(array $overrides = []): Event
    {
        $defaults = [
            'event_date' => Carbon::now()->addDays(2)->toDateString(),
            'event_time' => Carbon::now()->addDays(2)->format('H:i:s'),
            'status' => 'published',
            'capacity' => 500,
        ];
        return Event::factory()->create(array_merge($defaults, $overrides));
    }

    private function makeUser(Role $role = Role::USER): User
    {
        return User::factory()->create([
            'role' => $role,
            'active' => true,
            'profile_completed' => true,
        ]);
    }

    public function test_purchase_success_with_percentage_fee_and_whatsapp(): void
    {
        Notification::fake();

        $user = $this->makeUser();
        $event = $this->makeEvent();

        $type = TicketType::create([
            'event_id' => $event->id,
            'name' => 'VIP',
            'price' => 100.00,
            'fee_type' => 'percentage',
            'fee_amount' => 10, // 10%
            'capacity' => 100,
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $resp = $this->post(route('bookings.store'), [
            'event_id' => $event->id,
            'quantity' => 2,
            'ticket_type_id' => $type->id,
            'whatsapp' => 1,
        ]);

        // Should redirect to booking show page
        $resp->assertStatus(302);
        $booking = Booking::latest()->first();
        $this->assertNotNull($booking, 'Booking should be created');

        // Expected totals: unit=100 + 10% = 110; total=110*2 + 25 whatsapp = 245
        $this->assertEquals(BookingStatus::CONFIRMED, $booking->status);
        $this->assertSame(245.00, (float)$booking->total_amount);
        $this->assertSame(2, $booking->quantity);

        // Tickets created with correct price and type
        $tickets = Ticket::where('booking_id', $booking->id)->get();
        $this->assertCount(2, $tickets);
        $this->assertTrue($tickets->every(fn($t) => (float)$t->price === 110.00));
        $this->assertTrue($tickets->every(fn($t) => $t->ticket_type_id === $type->id));
        $this->assertTrue($tickets->every(fn($t) => $t->status === TicketStatus::VALID));

        // Notification sent to user
        Notification::assertSentTo($user, BookingConfirmationNotification::class, function($n) use ($booking) {
            return $n->booking->id === $booking->id;
        });
    }

    public function test_purchase_success_with_fixed_fee_no_whatsapp(): void
    {
        Notification::fake();

        $user = $this->makeUser();
        $event = $this->makeEvent();

        $type = TicketType::create([
            'event_id' => $event->id,
            'name' => 'Standard',
            'price' => 100.00,
            'fee_type' => 'fixed',
            'fee_amount' => 5, // +5 per ticket
            'capacity' => 100,
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $resp = $this->post(route('bookings.store'), [
            'event_id' => $event->id,
            'quantity' => 3,
            'ticket_type_id' => $type->id,
        ]);

        $resp->assertStatus(302);
        $booking = Booking::latest()->first();
        $this->assertNotNull($booking);

        // unit=105; total=105*3 = 315
        $this->assertSame(315.00, (float)$booking->total_amount);
        $this->assertSame(3, $booking->quantity);
        $tickets = Ticket::where('booking_id', $booking->id)->get();
        $this->assertCount(3, $tickets);
        $this->assertTrue($tickets->every(fn($t) => (float)$t->price === 105.00));
    }

    public function test_quantity_exceeds_event_capacity(): void
    {
        Notification::fake();
        $user = $this->makeUser();
        $event = $this->makeEvent(['capacity' => 3]);

        // No types for simplicity
        $this->actingAs($user);
        $resp = $this->post(route('bookings.store'), [
            'event_id' => $event->id,
            'quantity' => 4,
        ]);

        // Should redirect back with errors
        $resp->assertStatus(302);
        $this->assertTrue(session()->has('errors'));
        $this->assertSame(0, Booking::count());
        $this->assertSame(0, Ticket::count());
    }

    public function test_quantity_exceeds_ticket_type_capacity(): void
    {
        Notification::fake();
        $user = $this->makeUser();
        $event = $this->makeEvent(['capacity' => 100]);
        $type = TicketType::create([
            'event_id' => $event->id,
            'name' => 'Limited',
            'price' => 50.00,
            'fee_type' => null,
            'fee_amount' => 0,
            'capacity' => 2, // per-type limit
            'is_active' => true,
        ]);

        $this->actingAs($user);
        $resp = $this->post(route('bookings.store'), [
            'event_id' => $event->id,
            'quantity' => 3,
            'ticket_type_id' => $type->id,
        ]);

        $resp->assertStatus(302);
        $this->assertTrue(session()->has('errors'));
        $this->assertSame(0, Booking::count());
        $this->assertSame(0, Ticket::count());
    }

    public function test_quantity_zero_is_invalid(): void
    {
        Notification::fake();
        $user = $this->makeUser();
        $event = $this->makeEvent();
        $type = TicketType::create([
            'event_id' => $event->id,
            'name' => 'General',
            'price' => 20.00,
            'fee_type' => null,
            'fee_amount' => 0,
            'capacity' => 100,
            'is_active' => true,
        ]);

        $this->actingAs($user);
        $resp = $this->post(route('bookings.store'), [
            'event_id' => $event->id,
            'quantity' => 0,
            'ticket_type_id' => $type->id,
        ]);

        $resp->assertStatus(302);
        $this->assertTrue(session()->has('errors'));
        $this->assertSame(0, Booking::count());
        $this->assertSame(0, Ticket::count());
    }

    public function test_checkout_prg_flow_computed_totals_match_booking_after_purchase(): void
    {
        \Illuminate\Support\Facades\Notification::fake();

        $user = $this->makeUser();
        $event = $this->makeEvent(['capacity' => 50]);

        // Use percentage fee to exercise fee math
        $type = TicketType::create([
            'event_id' => $event->id,
            'name' => 'Gold',
            'price' => 80.00,
            'fee_type' => 'percentage',
            'fee_amount' => 12.5, // 12.5%
            'capacity' => 20,
            'is_active' => true,
        ]);

        $this->actingAs($user);

        // Step 1: POST checkout to stash session and redirect to GET view
        $post = $this->post(route('bookings.checkout'), [
            'event_id' => $event->id,
            'quantity' => 4,
            'ticket_type_id' => $type->id,
            'whatsapp' => 1,
        ]);
        $post->assertRedirect(route('bookings.checkout.view'));

        // Step 2: GET checkout view and capture computed values
        $view = $this->get(route('bookings.checkout.view'));
        $view->assertOk();
        $view->assertViewIs('bookings.checkout');
        $view->assertViewHasAll(['unitBase','unitPrice','quantity','subtotal','handlingFee','totalBeforeWhatsapp','total','whatsappSelected','ticketType']);

        $unitBase = (float)$view->viewData('unitBase');
        $unitPrice = (float)$view->viewData('unitPrice');
        $quantity = (int)$view->viewData('quantity');
        $subtotal = (float)$view->viewData('subtotal');
        $handlingFee = (float)$view->viewData('handlingFee');
        $totalBeforeWhatsapp = (float)$view->viewData('totalBeforeWhatsapp');
        $total = (float)$view->viewData('total');
        $whatsappSelected = (bool)$view->viewData('whatsappSelected');

        // Sanity: these come from controller formula
        $this->assertSame(80.00, $unitBase);
        $this->assertSame(4, $quantity);
        $this->assertTrue($whatsappSelected);
        // 12.5% of 80 = 10; unitPrice = 90; subtotal = 80*4=320; handling = 10*4=40; totalBeforeWhatsapp = 360; whatsapp=25; total=385
        $this->assertSame(90.00, $unitPrice);
        $this->assertSame(320.00, $subtotal);
        $this->assertSame(40.00, $handlingFee);
        $this->assertSame(360.00, $totalBeforeWhatsapp);
        $this->assertSame(385.00, $total);

        // Step 3: POST store (finalize purchase)
        $store = $this->post(route('bookings.store'), [
            'event_id' => $event->id,
            'quantity' => $quantity,
            'ticket_type_id' => $type->id,
            'whatsapp' => 1,
        ]);
        $store->assertStatus(302);

        // Verify booking totals match the checkout view
        $booking = Booking::latest()->firstOrFail();
        $this->assertSame($total, (float)$booking->total_amount, 'Booking total should match checkout view total');
        $this->assertSame($quantity, $booking->quantity);

        // Verify ticket count and per-ticket price matches unitPrice
        $tickets = Ticket::where('booking_id', $booking->id)->get();
        $this->assertCount($quantity, $tickets);
        $this->assertTrue($tickets->every(fn($t) => (float)$t->price === $unitPrice));
        $this->assertTrue($tickets->every(fn($t) => $t->status === \App\Enums\TicketStatus::VALID));
    }
}
