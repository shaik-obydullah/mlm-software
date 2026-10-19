<?php

namespace Tests\Feature;

use App\Models\CvPurchase;
use App\Models\InterestAccrual;
use App\Models\InterestRate;
use App\Services\CvService;
use App\Services\InterestService;

class InterestServiceTest extends FeatureTestCase
{
    public function test_monthly_interest_is_credited_to_active_cv(): void
    {
        $this->tier(400);
        $user = $this->member();
        $this->activateCv($user, 400);

        $period = now()->format('Y-m');
        $credited = app(InterestService::class)->processMonthly($period);

        $this->assertSame(1, $credited);
        $this->assertDatabaseHas('interest_accruals', [
            'user_id' => $user->id,
            'amount' => 8.0,
            'rate' => 2.0,
            'period' => $period,
        ]);
        $this->assertSame('8.00', $user->wallet->fresh()->balance);
    }

    public function test_interest_is_not_credited_twice_for_the_same_period(): void
    {
        $user = $this->member();
        $this->activateCv($user, 400);

        $period = now()->format('Y-m');
        $service = app(InterestService::class);

        $service->processMonthly($period);
        $credited = $service->processMonthly($period);

        $this->assertSame(0, $credited);
        $this->assertSame(1, InterestAccrual::count());
        $this->assertSame('8.00', $user->wallet->fresh()->balance);
    }

    public function test_interest_skips_purchases_activated_after_the_period(): void
    {
        $user = $this->member();
        $this->activateCv($user, 400);

        $credited = app(InterestService::class)->processMonthly(now()->subMonth()->format('Y-m'));

        $this->assertSame(0, $credited);
        $this->assertSame(0, InterestAccrual::count());
    }

    public function test_interest_skips_purchases_that_are_not_activated(): void
    {
        $user = $this->member();
        app(CvService::class)->purchase($user->id, $this->tier(200)->id, 'bank');

        $credited = app(InterestService::class)->processMonthly(now()->format('Y-m'));

        $this->assertSame(0, $credited);
    }

    public function test_interest_returns_zero_when_no_active_rate_exists(): void
    {
        InterestRate::query()->delete();

        $user = $this->member();
        $this->activateCv($user, 400);

        $credited = app(InterestService::class)->processMonthly(now()->format('Y-m'));

        $this->assertSame(0, $credited);
    }

    public function test_interest_uses_the_configured_rate(): void
    {
        InterestRate::query()->delete();
        InterestRate::create([
            'effective_date' => now()->toDateString(),
            'rate' => 1.5,
            'status' => 'active',
        ]);

        $user = $this->member();
        $this->activateCv($user, 200);

        $credited = app(InterestService::class)->processMonthly(now()->format('Y-m'));

        $this->assertSame(1, $credited);
        $this->assertSame('3.00', $user->wallet->fresh()->balance);
    }

    public function test_interest_accumulates_across_multiple_activations(): void
    {
        $user = $this->member();
        $this->activateCv($user, 400);
        $this->activateCv($user, 600);

        app(InterestService::class)->processMonthly(now()->format('Y-m'));

        $total = (int) CvPurchase::where('user_id', $user->id)->where('status', 'activated')->sum('amount');
        $this->assertSame(1000, $total);
        $this->assertSame('20.00', $user->wallet->fresh()->balance);
    }
}
