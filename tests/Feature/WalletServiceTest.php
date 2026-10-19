<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\WalletService;
use RuntimeException;

class WalletServiceTest extends FeatureTestCase
{
    public function test_ensure_wallet_creates_and_reuses_wallet(): void
    {
        $user = $this->member();
        $service = app(WalletService::class);

        $first = $service->ensureWallet($user->id);
        $second = $service->ensureWallet($user->id);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Wallet::count());
        $this->assertSame('0.00', $first->balance);
    }

    public function test_credit_increases_balance_and_tracks_earnings(): void
    {
        $user = $this->member();
        $service = app(WalletService::class);

        $transaction = $service->credit($user->id, 500.00, 'commission', 'REF-1');

        $wallet = $user->wallet->fresh();
        $this->assertSame('500.00', $wallet->balance);
        $this->assertSame('500.00', $wallet->total_earned);
        $this->assertSame('500.00', $user->fresh()->balance);
        $this->assertSame(TransactionType::Credit->value, $transaction->type);
        $this->assertSame('500.00', $transaction->balance_after);
        $this->assertSame('REF-1', $transaction->reference);
    }

    public function test_credit_rejects_non_positive_amounts(): void
    {
        $user = $this->member();
        $service = app(WalletService::class);

        $this->expectException(RuntimeException::class);
        $service->credit($user->id, 0);
    }

    public function test_debit_reduces_balance(): void
    {
        $user = $this->member();
        $service = app(WalletService::class);
        $service->credit($user->id, 500.00);

        $service->debit($user->id, 120.00, 'purchase', 'CV-1');

        $this->assertSame('380.00', $user->wallet->fresh()->balance);
        $this->assertSame('380.00', $user->fresh()->balance);
        $this->assertSame(2, Transaction::count());
    }

    public function test_debit_fails_when_balance_is_insufficient(): void
    {
        $user = $this->member();
        $service = app(WalletService::class);
        $service->credit($user->id, 100.00);

        $this->expectException(RuntimeException::class);
        $service->debit($user->id, 200.00);
    }

    public function test_hold_locks_funds_and_release_returns_them(): void
    {
        $user = $this->member();
        $service = app(WalletService::class);
        $service->credit($user->id, 1000.00);

        $service->hold($user->id, 300.00);

        $wallet = $user->wallet->fresh();
        $this->assertSame('700.00', $wallet->balance);
        $this->assertSame('300.00', $wallet->locked_amount);

        $service->release($user->id, 300.00);

        $wallet = $user->wallet->fresh();
        $this->assertSame('1000.00', $wallet->balance);
        $this->assertSame('0.00', $wallet->locked_amount);
    }

    public function test_hold_fails_when_balance_is_insufficient(): void
    {
        $user = $this->member();
        $service = app(WalletService::class);
        $service->credit($user->id, 50.00);

        $this->expectException(RuntimeException::class);
        $service->hold($user->id, 100.00);
    }

    public function test_release_fails_when_nothing_is_locked(): void
    {
        $user = $this->member();
        $service = app(WalletService::class);

        $this->expectException(RuntimeException::class);
        $service->release($user->id, 50.00);
    }

    public function test_mark_withdrawn_tracks_lifetime_withdrawals(): void
    {
        $user = $this->member();
        $service = app(WalletService::class);
        $service->credit($user->id, 1000.00);
        $service->hold($user->id, 400.00);

        $service->markWithdrawn($user->id, 400.00);

        $wallet = $user->wallet->fresh();
        $this->assertSame('600.00', $wallet->balance);
        $this->assertSame('0.00', $wallet->locked_amount);
        $this->assertSame('400.00', $wallet->total_withdrawn);
    }

    public function test_transactions_record_balance_after_each_operation(): void
    {
        $user = $this->member();
        $service = app(WalletService::class);

        $service->credit($user->id, 1000.00);
        $service->debit($user->id, 250.00);
        $service->hold($user->id, 150.00);

        $balances = $user->wallet->transactions()->orderBy('id')->pluck('balance_after');

        $this->assertSame(['1000.00', '750.00', '600.00'], $balances->all());
    }
}
