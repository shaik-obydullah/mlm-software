<?php

namespace Tests\Feature;

use App\Enums\PurchaseStatus;
use App\Models\CvActivation;
use App\Services\CvService;
use App\Services\PinService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use RuntimeException;

class CvServiceTest extends FeatureTestCase
{
    public function test_bank_purchase_is_created_as_pending(): void
    {
        $user = $this->member();
        $tier = $this->tier(200);

        $purchase = app(CvService::class)->purchase($user->id, $tier->id, 'bank', 'TXN-123');

        $this->assertSame(PurchaseStatus::Pending->value, $purchase->status);
        $this->assertSame(200, $purchase->amount);
        $this->assertSame($tier->price, $purchase->price);
        $this->assertSame('TXN-123', $purchase->transaction_id);
    }

    public function test_wallet_purchase_pays_from_balance_and_marks_paid(): void
    {
        $user = $this->member();
        $this->credit($user, 5000);

        $purchase = app(CvService::class)->purchase($user->id, $this->tier(400)->id, 'wallet');

        $this->assertSame(PurchaseStatus::Paid->value, $purchase->status);
        $this->assertNotNull($purchase->paid_at);
        $this->assertSame('1000.00', $user->wallet->fresh()->balance);
        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $user->wallet->id,
            'type' => 'debit',
            'category' => 'purchase',
        ]);
    }

    public function test_wallet_purchase_fails_without_balance(): void
    {
        $user = $this->member();

        $this->expectException(RuntimeException::class);
        app(CvService::class)->purchase($user->id, $this->tier(200)->id, 'wallet');
    }

    public function test_mark_paid_transitions_pending_to_paid(): void
    {
        $user = $this->member();
        $purchase = app(CvService::class)->purchase($user->id, $this->tier(200)->id, 'bank');

        $paid = app(CvService::class)->markPaid($purchase->id);

        $this->assertSame(PurchaseStatus::Paid->value, $paid->status);
        $this->assertNotNull($paid->paid_at);
    }

    public function test_mark_paid_rejects_already_paid_purchase(): void
    {
        $user = $this->member();
        $purchase = app(CvService::class)->purchase($user->id, $this->tier(200)->id, 'bank');
        app(CvService::class)->markPaid($purchase->id);

        $this->expectException(RuntimeException::class);
        app(CvService::class)->markPaid($purchase->id);
    }

    public function test_activate_requires_a_paid_purchase(): void
    {
        $user = $this->member();
        $purchase = app(CvService::class)->purchase($user->id, $this->tier(200)->id, 'bank');

        $this->expectException(RuntimeException::class);
        app(CvService::class)->activate($user->id, $purchase->id);
    }

    public function test_activate_activates_purchase_and_creates_activation(): void
    {
        $user = $this->member();
        $cv = app(CvService::class);
        $purchase = $cv->purchase($user->id, $this->tier(200)->id, 'bank');
        $cv->markPaid($purchase->id);

        $activated = $cv->activate($user->id, $purchase->id);

        $this->assertSame(PurchaseStatus::Activated->value, $activated->status);
        $this->assertDatabaseHas('cv_activations', [
            'purchase_id' => $purchase->id,
            'user_id' => $user->id,
            'status' => 'active',
        ]);
        $this->assertSame('active', $user->fresh()->status);
        $this->assertNotNull($user->fresh()->activated_at);
    }

    public function test_activate_cannot_activate_twice(): void
    {
        $user = $this->member();
        $this->activateCv($user, 200);

        $purchase = $user->purchases()->first();

        $this->expectException(RuntimeException::class);
        app(CvService::class)->activate($user->id, $purchase->id);
    }

    public function test_activate_with_pin_consumes_the_pin(): void
    {
        $user = $this->member();
        $admin = $this->admin();
        $tier = $this->tier(200);
        $pin = app(PinService::class)->generate($admin->id, $tier->id)[0];

        $cv = app(CvService::class);
        $purchase = $cv->purchase($user->id, $tier->id, 'bank');
        $cv->markPaid($purchase->id);

        $activated = $cv->activate($user->id, $purchase->id, $pin->code);

        $this->assertSame(PurchaseStatus::Activated->value, $activated->status);
        $this->assertSame($pin->code, $activated->pin_code);
        $this->assertSame('used', $pin->fresh()->status);
    }

    public function test_activate_rejects_pin_for_different_tier(): void
    {
        $user = $this->member();
        $admin = $this->admin();
        $pin = app(PinService::class)->generate($admin->id, $this->tier(200)->id)[0];

        $cv = app(CvService::class);
        $purchase = $cv->purchase($user->id, $this->tier(400)->id, 'bank');
        $cv->markPaid($purchase->id);

        $this->expectException(RuntimeException::class);
        $cv->activate($user->id, $purchase->id, $pin->code);
    }

    public function test_activate_rejects_invalid_pin(): void
    {
        $user = $this->member();
        $cv = app(CvService::class);
        $purchase = $cv->purchase($user->id, $this->tier(200)->id, 'bank');
        $cv->markPaid($purchase->id);

        $this->expectException(RuntimeException::class);
        $cv->activate($user->id, $purchase->id, 'BOGUS-000000');
    }

    public function test_activation_can_only_be_done_by_the_purchase_owner(): void
    {
        $owner = $this->member();
        $other = $this->member();
        $cv = app(CvService::class);
        $purchase = $cv->purchase($owner->id, $this->tier(200)->id, 'bank');
        $cv->markPaid($purchase->id);

        $this->expectException(ModelNotFoundException::class);
        $cv->activate($other->id, $purchase->id);
    }

    public function test_active_volume_sums_activated_purchases(): void
    {
        $user = $this->member();
        $this->activateCv($user, 400);
        $this->activateCv($user, 200);

        $this->assertSame(600.0, app(CvService::class)->activeVolume($user->id));
    }

    public function test_history_returns_orders_newest_first(): void
    {
        $user = $this->member();
        $this->activateCv($user, 200);

        $history = app(CvService::class)->history($user->id);

        $this->assertCount(1, $history);
        $this->assertInstanceOf(CvActivation::class, $history->first()->activation);
    }
}
