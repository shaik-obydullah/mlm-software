<?php

namespace Tests\Feature;

use App\Models\User;
use Laravel\Sanctum\Sanctum;

class ApiAuthTest extends FeatureTestCase
{
    public function test_registration_creates_member_with_tree_and_wallet(): void
    {
        $payload = [
            'name' => 'New Member',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+911234567890',
        ];

        $response = $this->postJson('/api/v1/register', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'token', 'user' => ['id', 'unique_id', 'wallet', 'tree_node']]);

        $user = User::where('email', 'new@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('member', $user->role);
        $this->assertSame('pending', $user->status);
        $this->assertSame('not_submitted', $user->kyc_status);
        $this->assertTrue($user->hasRole('member'));
        $this->assertNotNull($user->wallet);
        $this->assertNotNull($user->treeNode);
        $this->assertDatabaseHas('audit_logs', ['action' => 'registered', 'subject_id' => $user->id]);
    }

    public function test_registration_with_sponsor_links_the_sponsor(): void
    {
        $sponsor = $this->member();
        $this->place($sponsor, $sponsor->id);

        $response = $this->postJson('/api/v1/register', [
            'name' => 'Referred Member',
            'email' => 'referred@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'sponsor_unique_id' => $sponsor->unique_id,
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'referred@example.com')->first();
        $this->assertSame($sponsor->id, $user->sponsor_id);
        $this->assertDatabaseHas('sponsors', [
            'sponsor_id' => $sponsor->id,
            'sponsee_id' => $user->id,
        ]);
        $this->assertSame($sponsor->treeNode->id, $user->treeNode->parent_id);
    }

    public function test_registration_validates_duplicate_email(): void
    {
        $this->member(['email' => 'taken@example.com']);

        $this->postJson('/api/v1/register', [
            'name' => 'Duplicate',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_registration_validates_sponsor_unique_id(): void
    {
        $this->postJson('/api/v1/register', [
            'name' => 'Bad Sponsor',
            'email' => 'bad@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'sponsor_unique_id' => 'DOESNOTEXIST',
        ])->assertStatus(422)->assertJsonValidationErrors('sponsor_unique_id');
    }

    public function test_active_user_can_login(): void
    {
        $user = $this->member(['password' => 'password123']);

        $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'unique_id']]);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $this->member(['email' => 'me@example.com']);

        $this->postJson('/api/v1/login', [
            'email' => 'me@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(401)->assertJson(['message' => 'Invalid credentials.']);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $this->member(['email' => 'pending@example.com', 'status' => 'pending']);

        $this->postJson('/api/v1/login', [
            'email' => 'pending@example.com',
            'password' => 'password',
        ])->assertStatus(403)->assertJson(['message' => 'Your account is not active.']);
    }

    public function test_authenticated_user_can_fetch_their_profile(): void
    {
        $user = $this->member();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/user')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonStructure(['user' => ['sponsor', 'wallet', 'tree_node']]);
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $user = $this->member();
        $token = $user->createToken('auth')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/logout')->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id]);
    }

    public function test_protected_route_requires_authentication(): void
    {
        $this->getJson('/api/v1/user')->assertStatus(401);
    }

    public function test_sanctum_user_can_access_own_data_endpoints(): void
    {
        $user = $this->member();
        $this->ensureWallet($user);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/wallet/balance')->assertOk();
        $this->getJson("/api/v1/mlm/tree/{$user->id}")->assertOk();
        $this->getJson("/api/v1/commissions/history/{$user->id}")->assertOk();
    }
}
