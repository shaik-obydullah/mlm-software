<?php

namespace Tests\Feature;

use Laravel\Sanctum\Sanctum;

class ApiPermissionTest extends FeatureTestCase
{
    public function test_member_cannot_list_all_users(): void
    {
        $member = $this->member();
        Sanctum::actingAs($member);

        $this->getJson('/api/v1/users')->assertStatus(403);
    }

    public function test_admin_can_list_all_users(): void
    {
        $admin = $this->admin();
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/users')->assertOk();
    }

    public function test_admin_can_generate_and_assign_pins(): void
    {
        $admin = $this->admin();
        $member = $this->member();
        Sanctum::actingAs($admin);

        $pin = $this->postJson('/api/v1/pins/generate', [
            'cv_tier_id' => $this->tier(200)->id,
            'count' => 1,
        ])->assertStatus(201)->json('pins.0');

        $this->postJson('/api/v1/pins/assign', [
            'pin_id' => $pin['id'],
            'user_id' => $member->id,
        ])->assertOk()->assertJsonPath('pin.status', 'assigned');
    }

    public function test_member_cannot_generate_pins(): void
    {
        $member = $this->member();
        Sanctum::actingAs($member);

        $this->postJson('/api/v1/pins/generate', [
            'cv_tier_id' => $this->tier(200)->id,
            'count' => 1,
        ])->assertStatus(403);
    }

    public function test_member_can_view_own_tree_but_not_others(): void
    {
        $owner = $this->member();
        $this->place($owner, $owner->id);

        $other = $this->member();
        $this->place($other, $other->id);

        Sanctum::actingAs($owner);

        $this->getJson("/api/v1/mlm/tree/{$owner->id}")->assertOk();
        $this->getJson("/api/v1/mlm/tree/{$other->id}")->assertStatus(403);
    }

    public function test_admin_can_view_any_users_tree(): void
    {
        $admin = $this->admin();
        $member = $this->member();
        $this->place($member, $member->id);

        Sanctum::actingAs($admin);

        $this->getJson("/api/v1/mlm/tree/{$member->id}")->assertOk();
    }

    public function test_admin_can_manually_place_a_user(): void
    {
        $admin = $this->admin();
        $this->place($admin, $admin->id);
        $newMember = $this->member();

        Sanctum::actingAs($admin);

        $this->postJson('/api/v1/mlm/place', [
            'user_id' => $newMember->id,
            'sponsor_id' => $admin->id,
            'position' => 'left',
        ])->assertOk()->assertJsonPath('node.position', 'left');
    }

    public function test_member_cannot_manually_place_users(): void
    {
        $sponsor = $this->member();
        $this->place($sponsor, $sponsor->id);
        $newMember = $this->member();

        Sanctum::actingAs($sponsor);

        $this->postJson('/api/v1/mlm/place', [
            'user_id' => $newMember->id,
            'sponsor_id' => $sponsor->id,
            'position' => 'left',
        ])->assertStatus(403);
    }

    public function test_admin_can_approve_withdrawal(): void
    {
        $admin = $this->admin();
        $user = $this->member(['kyc_status' => 'approved']);
        $this->credit($user, 1000);

        $withdrawal = $this->postJsonAsMember($user, '/api/v1/withdrawal/request', [
            'amount' => 300,
            'bank_name' => 'HDFC',
            'account_name' => $user->name,
            'account_number' => '1234567890',
            'ifsc' => 'HDFC0000001',
        ])->assertStatus(201)->json('withdrawal');

        Sanctum::actingAs($admin);

        $this->postJson("/api/v1/withdrawal/{$withdrawal['id']}/approve")
            ->assertOk()
            ->assertJsonPath('withdrawal.status', 'approved');

        $this->assertSame('300.00', $user->wallet->fresh()->total_withdrawn);
        $this->assertSame('0.00', $user->wallet->fresh()->locked_amount);
    }

    public function test_member_cannot_approve_withdrawals(): void
    {
        $member = $this->member();
        Sanctum::actingAs($member);

        $this->postJson('/api/v1/withdrawal/1/approve')->assertStatus(403);
    }

    private function postJsonAsMember($user, string $uri, array $data)
    {
        Sanctum::actingAs($user);

        return $this->postJson($uri, $data);
    }
}
