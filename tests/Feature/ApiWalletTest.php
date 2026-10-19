<?php

namespace Tests\Feature;

use App\Models\WithdrawalRequest;
use App\Services\WalletService;
use Laravel\Sanctum\Sanctum;

class ApiWalletTest extends FeatureTestCase
{
    public function test_balance_returns_wallet_totals(): void
    {
        $user = $this->member();
        $this->credit($user, 500);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/wallet/balance')
            ->assertOk()
            ->assertJsonPath('available', '500.00')
            ->assertJsonPath('locked', '0.00')
            ->assertJsonPath('total_earned', '500.00');
    }

    public function test_transactions_can_be_filtered_by_category(): void
    {
        $user = $this->member();
        $this->credit($user, 500, 'commission', 'C-1');
        $this->credit($user, 100, 'interest', 'I-1');

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/wallet/transactions?category=interest')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.reference', 'I-1');
    }

    public function test_withdrawal_requires_kyc_approval(): void
    {
        $user = $this->member(['kyc_status' => 'pending']);
        $this->credit($user, 1000);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/withdrawal/request', [
            'amount' => 200,
            'bank_name' => 'HDFC',
            'account_name' => $user->name,
            'account_number' => '1234567890',
            'ifsc' => 'HDFC0000001',
        ])->assertStatus(422)->assertJson(['message' => 'KYC approval is required before withdrawing funds.']);
    }

    public function test_withdrawal_holds_funds_and_creates_request(): void
    {
        $user = $this->member(['kyc_status' => 'approved']);
        $this->credit($user, 1000);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/withdrawal/request', [
            'amount' => 200,
            'bank_name' => 'HDFC',
            'account_name' => $user->name,
            'account_number' => '1234567890',
            'ifsc' => 'HDFC0000001',
        ]);

        $response->assertStatus(201)->assertJsonPath('message', 'Withdrawal request submitted.');

        $this->assertSame('800.00', $user->wallet->fresh()->balance);
        $this->assertSame('200.00', $user->wallet->fresh()->locked_amount);

        $withdrawal = WithdrawalRequest::where('user_id', $user->id)->first();
        $this->assertNotNull($withdrawal);
        $this->assertSame('pending', $withdrawal->status);
        $this->assertSame('200.00', $withdrawal->amount);
        $this->assertSame('4.00', $withdrawal->fee);
        $this->assertSame('196.00', $withdrawal->net_amount);
    }

    public function test_withdrawal_rejects_amount_below_minimum(): void
    {
        $user = $this->member(['kyc_status' => 'approved']);
        $this->credit($user, 1000);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/withdrawal/request', [
            'amount' => 10,
            'bank_name' => 'HDFC',
            'account_name' => $user->name,
            'account_number' => '1234567890',
            'ifsc' => 'HDFC0000001',
        ])->assertStatus(422);
    }

    public function test_withdrawal_rejects_amount_exceeding_balance(): void
    {
        $user = $this->member(['kyc_status' => 'approved']);
        $this->credit($user, 100);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/withdrawal/request', [
            'amount' => 500,
            'bank_name' => 'HDFC',
            'account_name' => $user->name,
            'account_number' => '1234567890',
            'ifsc' => 'HDFC0000001',
        ])->assertStatus(422);
    }

    public function test_withdrawal_history_lists_requests(): void
    {
        $user = $this->member(['kyc_status' => 'approved']);
        $this->credit($user, 1000);

        app(WalletService::class)->hold($user->id, 300);

        WithdrawalRequest::create([
            'user_id' => $user->id,
            'amount' => 300,
            'fee' => 0,
            'net_amount' => 300,
            'method' => 'bank',
            'bank_name' => 'HDFC',
            'account_name' => $user->name,
            'account_number' => '1234567890',
            'ifsc' => 'HDFC0000001',
            'status' => 'pending',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/withdrawal/history')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
