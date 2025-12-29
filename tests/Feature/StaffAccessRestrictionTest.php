<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffAccessRestrictionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    private function createStandardUser(): User { return User::factory()->create(['role' => Role::USER]); }
    private function createFinanceOfficer(): User { return User::factory()->create(['role' => Role::FINANCE_OFFICER]); }
    private function createOperator(): User { return User::factory()->create(['role' => Role::OPERATOR]); }
    private function createApprovalOfficer(): User { return User::factory()->create(['role' => Role::APPROVAL_OFFICER]); }

    public function test_finance_officer_cannot_access_bookings_index(): void
    {
        $finance = $this->createFinanceOfficer();
        $response = $this->actingAs($finance)->get(route('bookings.index'));
        $response->assertStatus(403);
    }

    public function test_operator_cannot_access_bookings_index(): void
    {
        $op = $this->createOperator();
        $response = $this->actingAs($op)->get(route('bookings.index'));
        $response->assertStatus(403);
    }

    public function test_approval_officer_cannot_access_bookings_index(): void
    {
        $appr = $this->createApprovalOfficer();
        $response = $this->actingAs($appr)->get(route('bookings.index'));
        $response->assertStatus(403);
    }

    public function test_finance_officer_cannot_access_tickets_index(): void
    {
        $finance = $this->createFinanceOfficer();
        $response = $this->actingAs($finance)->get(route('tickets.index'));
        $response->assertStatus(403);
    }

    public function test_operator_cannot_access_tickets_index(): void
    {
        $op = $this->createOperator();
        $response = $this->actingAs($op)->get(route('tickets.index'));
        $response->assertStatus(403);
    }

    public function test_approval_officer_cannot_access_tickets_index(): void
    {
        $appr = $this->createApprovalOfficer();
        $response = $this->actingAs($appr)->get(route('tickets.index'));
        $response->assertStatus(403);
    }

    public function test_normal_user_can_access_bookings_index(): void
    {
        $user = $this->createStandardUser();
        $response = $this->actingAs($user)->get(route('bookings.index'));
        $response->assertStatus(200);
    }

    public function test_normal_user_can_access_tickets_index(): void
    {
        $user = $this->createStandardUser();
        $response = $this->actingAs($user)->get(route('tickets.index'));
        $response->assertStatus(200);
    }
}
