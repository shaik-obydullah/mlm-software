<?php

namespace Tests\Feature;

use App\Enums\CommissionType;
use App\Models\Commission;
use App\Services\CommissionService;

class CommissionServiceTest extends FeatureTestCase
{
    public function test_sponsor_bonus_is_paid_on_activation(): void
    {
        $sponsor = $this->member();
        $this->place($sponsor, $sponsor->id);

        $member = $this->member(['sponsor_id' => $sponsor->id]);
        $this->place($member, $sponsor->id);

        $this->activateCv($member, 200);

        $bonus = Commission::query()
            ->where('user_id', $sponsor->id)
            ->where('type', CommissionType::Sponsor->value)
            ->first();

        $this->assertNotNull($bonus);
        $this->assertSame('20.00', $bonus->amount);
        $this->assertSame($member->id, $bonus->from_user_id);
        $this->assertSame('30.00', $sponsor->wallet->fresh()->balance);
    }

    public function test_sponsor_bonus_is_skipped_for_admin_sponsors(): void
    {
        $admin = $this->admin();
        $this->place($admin, $admin->id);

        $member = $this->member(['sponsor_id' => $admin->id]);
        $this->place($member, $admin->id);

        $this->activateCv($member, 200);

        $this->assertSame(0, Commission::where('type', CommissionType::Sponsor->value)->count());
    }

    public function test_matching_bonus_is_paid_based_on_weaker_leg(): void
    {
        $root = $this->member();
        $this->place($root, $root->id);

        $left = $this->member();
        $right = $this->member();
        $this->place($left, $root->id);
        $this->place($right, $root->id);

        $this->activateCv($left, 400);
        $this->assertSame(0, Commission::where('type', CommissionType::Matching->value)->count());

        $this->activateCv($right, 400);

        $matching = Commission::query()
            ->where('user_id', $root->id)
            ->where('type', CommissionType::Matching->value)
            ->first();

        $this->assertNotNull($matching);
        $this->assertSame('20.00', $matching->amount);
    }

    public function test_level_commissions_are_paid_to_seven_uplines(): void
    {
        $chain = [];
        $prev = $this->member();
        $this->place($prev, $prev->id);
        $chain[] = $prev;

        for ($i = 1; $i < 8; $i++) {
            $user = $this->member();
            $this->place($user, $prev->id);
            $chain[] = $user;
            $prev = $user;
        }

        $deepest = $chain[7];
        $this->activateCv($deepest, 400);

        $levels = Commission::query()
            ->where('cv_purchase_id', $deepest->purchases()->first()->id)
            ->where('type', CommissionType::Level->value)
            ->orderBy('level')
            ->get();

        $this->assertCount(7, $levels);
        $this->assertSame([20.0, 16.0, 12.0, 8.0, 8.0, 8.0, 8.0], $levels->pluck('amount')->map(fn ($a) => (float) $a)->all());

        foreach ($levels as $level) {
            $upline = $chain[7 - $level->level];
            $this->assertSame($upline->id, $level->user_id);
        }
    }

    public function test_commissions_are_not_duplicated_for_repeat_activations(): void
    {
        $sponsor = $this->member();
        $this->place($sponsor, $sponsor->id);
        $member = $this->member(['sponsor_id' => $sponsor->id]);
        $this->place($member, $sponsor->id);

        $this->activateCv($member, 200);
        $this->activateCv($member, 200);

        $this->assertSame(2, Commission::where('type', CommissionType::Sponsor->value)->count());
        $this->assertSame(2, Commission::where('user_id', $sponsor->id)
            ->where('type', CommissionType::Sponsor->value)
            ->count());
    }

    public function test_commission_service_exposes_max_levels(): void
    {
        $this->assertSame(7, CommissionService::MAX_LEVELS);
    }

    public function test_level_commission_skips_admin_uplines(): void
    {
        $admin = $this->admin();
        $this->place($admin, $admin->id);

        $m1 = $this->member();
        $m2 = $this->member();
        $this->place($m1, $admin->id);
        $this->place($m2, $m1->id);

        $this->activateCv($m2, 200);

        $this->assertSame(0, Commission::where('user_id', $admin->id)->where('type', CommissionType::Level->value)->count());
        $this->assertSame(1, Commission::where('user_id', $m1->id)->where('type', CommissionType::Level->value)->count());
    }
}
