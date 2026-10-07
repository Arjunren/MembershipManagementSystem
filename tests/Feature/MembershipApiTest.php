<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MembershipApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_forces_member_role_and_generates_member_number(): void
    {
        $response = $this->postJson('/api/auth/register', ['name' => 'Jane Member', 'email' => 'JANE@example.com', 'password' => 'StrongPass123!', 'password_confirmation' => 'StrongPass123!', 'role' => 'admin']);

        $response->assertCreated()->assertJsonPath('data.user.role', 'member');
        $this->assertMatchesRegularExpression('/^MEM-[A-Z0-9]{10}$/', $response->json('data.user.member_number'));
        $this->assertDatabaseHas('users', ['email' => 'jane@example.com', 'role' => 'member']);
    }

    public function test_staff_can_enroll_member_with_atomic_payment(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'active' => true]);
        $member = $this->member();
        $plan = $this->plan('35.00');
        Sanctum::actingAs($staff);

        $this->postJson("/api/members/{$member->id}/memberships", ['membership_plan_id' => $plan->id, 'amount' => 35, 'method' => 'card', 'reference' => 'PAY-100'])
            ->assertCreated()->assertJsonPath('data.status', 'active')->assertJsonPath('data.price', '35.00');
        $this->assertDatabaseHas('membership_payments', ['reference' => 'PAY-100', 'amount' => 35]);
    }

    public function test_incorrect_payment_rolls_back_enrollment(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'staff', 'active' => true]));
        $member = $this->member();
        $plan = $this->plan('35.00');

        $this->postJson("/api/members/{$member->id}/memberships", ['membership_plan_id' => $plan->id, 'amount' => 30, 'method' => 'cash'])->assertUnprocessable();
        $this->assertDatabaseCount('memberships', 0);
        $this->assertDatabaseCount('membership_payments', 0);
    }

    public function test_renewal_is_scheduled_after_current_membership(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'active' => true]);
        $member = $this->member();
        $plan = $this->plan('35.00');
        Sanctum::actingAs($staff);

        $first = $this->postJson("/api/members/{$member->id}/memberships", ['membership_plan_id' => $plan->id, 'amount' => 35, 'method' => 'cash'])->assertCreated();
        $second = $this->postJson("/api/members/{$member->id}/memberships", ['membership_plan_id' => $plan->id, 'amount' => 35, 'method' => 'cash'])->assertCreated()->assertJsonPath('data.status', 'scheduled');

        $this->assertSame(now()->parse($first->json('data.ends_on'))->addDay()->toDateString(), now()->parse($second->json('data.starts_on'))->toDateString());
    }

    public function test_active_member_can_check_in_once_per_day(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'active' => true]);
        $member = $this->member();
        $plan = $this->plan();
        $membership = Membership::create(['member_id' => $member->id, 'membership_plan_id' => $plan->id, 'starts_on' => today(), 'ends_on' => today()->addMonth(), 'price' => 35, 'status' => 'active', 'created_by' => $staff->id]);
        Sanctum::actingAs($staff);

        $this->postJson("/api/members/{$member->id}/check-ins")->assertCreated()->assertJsonPath('data.membership_id', $membership->id);
        $this->postJson("/api/members/{$member->id}/check-ins")->assertConflict();
        $this->assertDatabaseCount('check_ins', 1);
    }

    public function test_member_cannot_view_another_members_membership(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'active' => true]);
        $owner = $this->member();
        $membership = Membership::create(['member_id' => $owner->id, 'membership_plan_id' => $this->plan()->id, 'starts_on' => today(), 'ends_on' => today()->addMonth(), 'price' => 35, 'status' => 'active', 'created_by' => $staff->id]);
        Sanctum::actingAs($this->member());

        $this->getJson("/api/memberships/{$membership->id}")->assertForbidden()->assertJsonPath('error.code', 'FORBIDDEN');
    }

    private function member(): User
    {
        return User::factory()->create(['role' => 'member', 'active' => true, 'member_number' => 'MEM-'.str()->upper(str()->random(10)), 'joined_on' => today()]);
    }

    private function plan(string $price = '35.00'): MembershipPlan
    {
        return MembershipPlan::create(['name' => 'Monthly-'.str()->random(5), 'duration_months' => 1, 'price' => $price, 'active' => true]);
    }
}
