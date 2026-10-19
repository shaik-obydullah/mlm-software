<?php

namespace Tests\Feature;

use App\Models\CvPurchase;
use App\Models\CvTier;
use App\Models\User;
use App\Services\CvService;
use App\Services\MlmTreeService;
use App\Services\WalletService;
use Database\Seeders\CommissionRuleSeeder;
use Database\Seeders\CvTierSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SystemConfigSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class FeatureTestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RoleSeeder::class,
            CvTierSeeder::class,
            CommissionRuleSeeder::class,
            SystemConfigSeeder::class,
        ]);
    }

    protected function admin(array $attributes = []): User
    {
        $user = User::factory()->admin()->create($attributes);
        $user->assignRole('admin');

        return $user;
    }

    protected function member(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole('member');

        return $user;
    }

    protected function tier(int $amount): CvTier
    {
        return CvTier::query()->where('status', 'active')->where('amount', $amount)->first()
            ?? CvTier::factory()->create(['amount' => $amount, 'status' => 'active']);
    }

    protected function place(User $user, int $sponsorId, ?string $position = null): void
    {
        app(MlmTreeService::class)->place($user, $sponsorId, $position);
    }

    protected function ensureWallet(User $user): void
    {
        app(WalletService::class)->ensureWallet($user->id);
    }

    protected function credit(User $user, float $amount, string $category = 'commission', ?string $reference = null): void
    {
        app(WalletService::class)->credit($user->id, $amount, $category, $reference);
    }

    /**
     * Create a paid + activated CV purchase for a user and return the purchase.
     */
    protected function activateCv(User $user, int $amount = 200): CvPurchase
    {
        $cv = app(CvService::class);
        $purchase = $cv->purchase($user->id, $this->tier($amount)->id, 'bank');
        $cv->markPaid($purchase->id);
        $cv->activate($user->id, $purchase->id);

        return $purchase->fresh();
    }
}
