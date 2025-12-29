<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\EventRequest;
use App\Enums\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApprovalShowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Optionally seed?
    }

    private function makeRequest(array $overrides = []): EventRequest
    {
        return EventRequest::factory()->create(array_merge([
            'status' => 'pending',
        ], $overrides));
    }

    public function test_admin_can_view_show_page(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $req = $this->makeRequest();

        $this->actingAs($admin)
            ->get(route('approval.show', $req))
            ->assertStatus(200)
            ->assertSee('Request #'.$req->id)
            ->assertSee('Primary Attendee');
    }

    public function test_approval_officer_can_view_show_page(): void
    {
        $officer = User::factory()->create(['role' => Role::APPROVAL_OFFICER]);
        $req = $this->makeRequest();

        $this->actingAs($officer)
            ->get(route('approval.show', $req))
            ->assertOk()
            ->assertSee('Request #'.$req->id)
            ->assertSee('Guests');
    }

    public function test_normal_user_cannot_view_show_page(): void
    {
        $user = User::factory()->create(['role' => Role::USER]);
        $req = $this->makeRequest();

        $this->actingAs($user)
            ->get(route('approval.show', $req))
            ->assertStatus(403);
    }

    public function test_guest_redirected_to_login(): void
    {
        $req = $this->makeRequest();
        $this->get(route('approval.show', $req))
            ->assertStatus(302);
    }
}
