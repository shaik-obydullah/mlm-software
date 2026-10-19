<?php

namespace Tests\Feature;

use App\Enums\PurchaseStatus;
use App\Models\Pin;
use App\Services\PinService;
use Laravel\Sanctum\Sanctum;

class ApiCvTest extends FeatureTestCase
{
    public function test_tiers_are_publicly_listed(): void
    {
        $this->getJson('/api/v1/cv/tiers')
            ->assertOk()
            ->assertJsonCount(7, 'tiers')
            ->assertJsonPath('tiers.0.amount', 200);
    }

    public function test_bank_purchase_is_created_as_pending(): void
    {
        $user = $this->member();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/cv/purchase', [
            'tier_id' => $this->tier(200)->id,
            'payment_method' => 'bank',
            'transaction_id' => 'TXN-001',
        ])->assertStatus(201)
            ->assertJsonPath('purchase.status', PurchaseStatus::Pending->value);
    }

    public function test_wallet_purchase_pays_immediately(): void
    {
        $user = $this->member();
        $this->credit($user, 5000);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/cv/purchase', [
            'tier_id' => $this->tier(400)->id,
            'payment_method' => 'wallet',
        ])->assertStatus(201)
            ->assertJsonPath('purchase.status', PurchaseStatus::Paid->value);

        $this->assertSame('1000.00', $user->wallet->fresh()->balance);
    }

    public function test_purchase_requires_an_active_tier(): void
    {
        $user = $this->member();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/cv/purchase', [
            'tier_id' => 99999,
            'payment_method' => 'bank',
        ])->assertStatus(422);
    }

    public function test_paid_purchase_can_be_activated_without_a_pin(): void
    {
        $user = $this->member();
        $this->credit($user, 5000);
        Sanctum::actingAs($user);

        $purchase = $this->postJson('/api/v1/cv/purchase', [
            'tier_id' => $this->tier(200)->id,
            'payment_method' => 'wallet',
        ])->json('purchase');

        $this->postJson("/api/v1/cv/{$purchase['id']}/activate")
            ->assertOk()
            ->assertJsonPath('purchase.status', PurchaseStatus::Activated->value);
    }

    public function test_paid_purchase_can_be_activated_with_a_pin(): void
    {
        $admin = $this->admin();
        $user = $this->member();
        Sanctum::actingAs($admin);

        $pin = $this->postJson('/api/v1/pins/generate', [
            'cv_tier_id' => $this->tier(200)->id,
            'count' => 1,
        ])->json('pins.0');

        Sanctum::actingAs($user);

        $purchase = $this->postJson('/api/v1/cv/purchase', [
            'tier_id' => $this->tier(200)->id,
            'payment_method' => 'bank',
        ])->json('purchase');

        Sanctum::actingAs($admin);
        $this->postJson("/api/v1/cv/{$purchase['id']}/pay")->assertOk();

        Sanctum::actingAs($user);
        $this->postJson("/api/v1/cv/{$purchase['id']}/activate", [
            'pin_code' => $pin['code'],
        ])->assertOk()
            ->assertJsonPath('purchase.status', PurchaseStatus::Activated->value);

        $this->assertSame('used', Pin::find($pin['id'])->fresh()->status);
    }

    public function test_pin_use_endpoint_validates_pin(): void
    {
        $admin = $this->admin();
        $user = $this->member();
        $pin = app(PinService::class)->generate($admin->id, $this->tier(200)->id)[0];

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/pins/use', ['code' => $pin->code])
            ->assertOk()
            ->assertJson(['message' => 'PIN is valid.']);

        $this->postJson('/api/v1/pins/use', ['code' => 'BOGUS-000000'])
            ->assertStatus(422)
            ->assertJson(['message' => 'Invalid or expired PIN.']);
    }

    public function test_purchases_and_activations_are_listed_for_the_owner(): void
    {
        $user = $this->member();
        $this->activateCv($user, 200);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/cv/purchases/{$user->id}")->assertOk()->assertJsonCount(1, 'data');
        $this->getJson("/api/v1/cv/activations/{$user->id}")->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_member_cannot_view_another_users_purchases(): void
    {
        $owner = $this->member();
        $this->activateCv($owner, 200);
        $other = $this->member();
        Sanctum::actingAs($other);

        $this->getJson("/api/v1/cv/purchases/{$owner->id}")->assertStatus(403);
    }
}
