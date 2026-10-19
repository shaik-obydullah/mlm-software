<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\WithdrawalRequest;
use App\Services\WalletService;
use Laravel\Sanctum\Sanctum;

class ApiReportTest extends FeatureTestCase
{
    public function test_member_earnings_report_aggregates_wallet_data(): void
    {
        $user = $this->member();
        $wallet = $this->credit($user, 1000);
        $this->credit($user, 250, 'interest', 'I-1');

        app(WalletService::class)->hold($user->id, 200);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/reports/earnings')->assertOk();

        $response->assertJsonPath('total_earned', 1250)
            ->assertJsonPath('total_withdrawn', 0)
            ->assertJsonPath('available_balance', 1050)
            ->assertJsonPath('pending_withdrawals', 200);
    }

    public function test_member_earnings_report_includes_monthly_breakdown(): void
    {
        $user = $this->member();
        $this->credit($user, 500);

        Sanctum::actingAs($user);

        $month = now()->format('Y-m');

        $this->getJson('/api/v1/reports/earnings')
            ->assertOk()
            ->assertJsonPath("monthly.$month", 500);
    }

    public function test_admin_earnings_report_includes_platform_totals(): void
    {
        $admin = $this->admin();
        $member = $this->member();
        $this->activateCv($member, 200);

        Commission::create([
            'user_id' => $member->id,
            'from_user_id' => $member->id,
            'type' => 'sponsor',
            'cv_volume' => 200,
            'amount' => 20,
            'status' => 'paid',
        ]);

        WithdrawalRequest::create([
            'user_id' => $member->id,
            'amount' => 100,
            'fee' => 0,
            'net_amount' => 100,
            'method' => 'bank',
            'bank_name' => 'HDFC',
            'account_name' => $member->name,
            'account_number' => '1234567890',
            'ifsc' => 'HDFC0000001',
            'status' => 'completed',
        ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/reports/earnings')
            ->assertOk()
            ->assertJsonPath('platform.total_commissions', 20)
            ->assertJsonPath('platform.total_cv_sold', 200)
            ->assertJsonPath('platform.total_withdrawn', 100);
    }

    public function test_member_downline_report_contains_leg_volumes(): void
    {
        $user = $this->member();
        $this->place($user, $user->id);

        $left = $this->member();
        $this->place($left, $user->id, 'left');

        $right = $this->member();
        $this->place($right, $user->id, 'right');

        $this->activateCv($left, 200);
        $this->activateCv($right, 400);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/reports/downline')
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('left_volume', 200)
            ->assertJsonPath('right_volume', 400);
    }

    public function test_member_can_export_own_commissions_as_csv(): void
    {
        $user = $this->member();
        $this->activateCv($user, 200);

        Commission::create([
            'user_id' => $user->id,
            'from_user_id' => $user->id,
            'type' => 'sponsor',
            'cv_volume' => 200,
            'amount' => 20,
            'status' => 'paid',
        ]);

        $commission = Commission::where('user_id', $user->id)->first();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/reports/export/commissions')->assertOk();

        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString((string) $commission->amount, $response->streamedContent());
    }

    public function test_unknown_export_type_returns_404(): void
    {
        $user = $this->member();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/reports/export/unknown')->assertStatus(404);
    }
}
