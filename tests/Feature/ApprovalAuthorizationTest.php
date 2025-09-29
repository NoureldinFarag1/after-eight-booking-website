<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Enums\Role;
use App\Models\User;
use App\Models\EventRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApprovalAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure basic users table + event_requests table exist via migrations
        $this->artisan('migrate');
    }

    protected function makeUser(Role $role): User
    {
        return User::factory()->create(['role' => $role->value]);
    }

    public function test_guest_cannot_access_approvals_index(): void
    {
        $response = $this->get(route('approval.index'));
        $response->assertRedirect('/login');
    }

    public function test_regular_user_forbidden(): void
    {
        $user = $this->makeUser(Role::USER);
        $this->actingAs($user);
        $response = $this->get(route('approval.index'));
        $response->assertStatus(403);
    }

    public function test_operator_forbidden(): void
    {
        $user = $this->makeUser(Role::OPERATOR);
        $this->actingAs($user);
        $response = $this->get(route('approval.index'));
        $response->assertStatus(403);
    }

    public function test_approval_officer_can_access(): void
    {
        $officer = $this->makeUser(Role::APPROVAL_OFFICER);
        $this->actingAs($officer);
        $response = $this->get(route('approval.index'));
        $response->assertOk();
    }

    public function test_admin_can_access(): void
    {
        $admin = $this->makeUser(Role::ADMIN);
        $this->actingAs($admin);
        $response = $this->get(route('approval.index'));
        $response->assertOk();
    }

    public function test_non_privileged_cannot_approve(): void
    {
        $user = $this->makeUser(Role::USER);
        $eventRequest = EventRequest::factory()->create(['status' => 'pending']);
        $this->actingAs($user);
        $response = $this->post(route('approval.approve', $eventRequest->id));
        $response->assertStatus(403);
    }

    public function test_approval_officer_can_approve(): void
    {
        $officer = $this->makeUser(Role::APPROVAL_OFFICER);
        $eventRequest = EventRequest::factory()->create(['status' => 'pending']);
        $this->actingAs($officer);
        $response = $this->post(route('approval.approve', $eventRequest->id));
        $response->assertRedirect();
        $this->assertEquals('approved', $eventRequest->fresh()->status);
    }
}
