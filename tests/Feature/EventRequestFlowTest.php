<?php

namespace Tests\Feature;

use App\Enums\EventRequestStatus;
use App\Enums\Role;
use App\Models\Event;
use App\Models\EventRequest;
use App\Models\TicketType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EventRequestFlowTest extends TestCase
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
            'event_date' => Carbon::now()->addDays(5)->toDateString(),
            'event_time' => Carbon::now()->addDays(5)->format('H:i:s'),
            'status' => 'published',
            'capacity' => 200,
            'type' => 'request',
        ];
        return Event::factory()->create(array_merge($defaults, $overrides));
    }

    private function addTicketTypes(Event $event): array
    {
        $primary = TicketType::create([
            'event_id' => $event->id,
            'name' => 'Request-General',
            'price' => 0,
            'fee_type' => null,
            'fee_amount' => 0,
            'capacity' => 100,
            'is_active' => true,
        ]);
        $guest = TicketType::create([
            'event_id' => $event->id,
            'name' => 'Request-Guest',
            'price' => 0,
            'fee_type' => null,
            'fee_amount' => 0,
            'capacity' => 100,
            'is_active' => true,
        ]);
        return [$primary, $guest];
    }

    public function test_user_can_view_request_form_for_event(): void
    {
        $user = $this->makeUser();
        $event = $this->makeEvent();
        $this->addTicketTypes($event);

        $this->actingAs($user);
        $resp = $this->get(route('event-requests.create', $event));
        $resp->assertOk();
        $resp->assertViewIs('event_requests.create');
        $resp->assertViewHasAll(['event', 'ticketTypes']);
    }

    public function test_user_can_submit_event_request_successfully(): void
    {
        $user = $this->makeUser();
        $event = $this->makeEvent();
        [$primaryType, $guestType] = $this->addTicketTypes($event);

        $this->actingAs($user);
        $payload = [
            'primary_name' => 'Alice Example',
            'primary_email' => 'alice@example.com',
            'primary_social_url' => 'https://instagram.com/alice',
            'primary_ticket_type_id' => $primaryType->id,
            'guests' => [
                [
                    'name' => 'Bob Guest',
                    'email' => 'bob@example.com',
                    'social_url' => 'https://facebook.com/bob',
                    'ticket_type_id' => $guestType->id,
                ],
            ],
        ];

        $resp = $this->post(route('event-requests.store', $event), $payload);
        $resp->assertRedirect(route('events.show', $event));
        $resp->assertSessionHas('success');

        /** @var EventRequest $req */
        $req = EventRequest::first();
        $this->assertNotNull($req, 'EventRequest should be created');
        $this->assertSame($event->id, $req->event_id);
        $this->assertSame($user->id, $req->user_id);
        $this->assertSame(EventRequestStatus::PENDING->value, $req->status);
        $this->assertSame('Alice Example', $req->primary_name);
        $this->assertSame('alice@example.com', $req->primary_email);
        $this->assertSame($primaryType->id, $req->primary_ticket_type_id);
        // attendee_count = 2 (primary + 1 guest)
        $this->assertSame(2, (int)$req->attendee_count);
        $this->assertIsArray($req->guests);
        $this->assertSame('Bob Guest', $req->guests[0]['name'] ?? null);

        if (Schema::hasColumn('event_requests', 'payload')) {
            $this->assertIsArray($req->payload);
            $this->assertSame('Alice Example', $req->payload['primary']['name'] ?? null);
        }
    }

    public function test_duplicate_pending_request_redirects_user_to_existing(): void
    {
        $user = $this->makeUser();
        $event = $this->makeEvent();
        [$primaryType] = $this->addTicketTypes($event);

        $this->actingAs($user);
        // First submission
        $this->post(route('event-requests.store', $event), [
            'primary_name' => 'First User',
            'primary_email' => 'first@example.com',
            'primary_social_url' => 'https://instagram.com/first_user',
            'primary_ticket_type_id' => $primaryType->id,
            'guests' => [],
        ])->assertRedirect(route('events.show', $event));

        // Try to open create form again -> should redirect to event page with warning
        $resp = $this->get(route('event-requests.create', $event));
        $resp->assertStatus(302);
        $resp->assertRedirect(route('events.show', $event));
        $this->assertTrue(session()->has('warning') || session()->has('error'));
    }

    public function test_invalid_social_url_is_rejected(): void
    {
        $user = $this->makeUser();
        $event = $this->makeEvent();
        [$primaryType] = $this->addTicketTypes($event);

        $this->actingAs($user);
        $payload = [
            'primary_name' => 'Bad Social',
            'primary_email' => 'bad@example.com',
            'primary_social_url' => 'https://twitter.com/not_allowed',
            'primary_ticket_type_id' => $primaryType->id,
        ];
        $resp = $this->post(route('event-requests.store', $event), $payload);
        $resp->assertStatus(302);
        $this->assertTrue(session()->has('errors'));
        $this->assertSame(0, EventRequest::count());
    }
}
