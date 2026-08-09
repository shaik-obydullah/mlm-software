<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WalletService
{
    public function ensureWallet(int $userId): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0, 'locked_amount' => 0, 'total_earned' => 0, 'total_withdrawn' => 0]
        );
    }

    public function balance(int $userId): string
    {
        $wallet = $this->ensureWallet($userId);

        return number_format($wallet->balance, 2);
    }

    public function credit(
        int $userId,
        float $amount,
        string $category = 'commission',
        ?string $reference = null,
        ?string $description = null
    ): Transaction {
        if ($amount <= 0) {
            throw new RuntimeException('Credit amount must be positive.');
        }

        return DB::transaction(function () use ($userId, $amount, $category, $reference, $description) {
            $wallet = $this->ensureWallet($userId);
            $wallet = Wallet::whereKey($wallet->id)->lockForUpdate()->first();

            $wallet->increment('balance', $amount);
            $wallet->increment('total_earned', $amount);

            $this->syncUserBalance($userId, $wallet->balance);

            return $wallet->transactions()->create([
                'type' => TransactionType::Credit->value,
                'category' => $category,
                'amount' => $amount,
                'balance_after' => $wallet->fresh()->balance,
                'reference' => $reference,
                'description' => $description,
                'status' => 'completed',
            ]);
        });
    }

    public function debit(
        int $userId,
        float $amount,
        string $category = 'adjustment',
        ?string $reference = null,
        ?string $description = null
    ): Transaction {
        if ($amount <= 0) {
            throw new RuntimeException('Debit amount must be positive.');
        }

        return DB::transaction(function () use ($userId, $amount, $category, $reference, $description) {
            $wallet = $this->ensureWallet($userId);
            $wallet = Wallet::whereKey($wallet->id)->lockForUpdate()->first();

            if ($wallet->balance < $amount) {
                throw new RuntimeException('Insufficient wallet balance.');
            }

            $wallet->decrement('balance', $amount);

            $this->syncUserBalance($userId, $wallet->fresh()->balance);

            return $wallet->transactions()->create([
                'type' => TransactionType::Debit->value,
                'category' => $category,
                'amount' => $amount,
                'balance_after' => $wallet->fresh()->balance,
                'reference' => $reference,
                'description' => $description,
                'status' => 'completed',
            ]);
        });
    }

    public function hold(int $userId, float $amount, ?string $reference = null): Transaction
    {
        if ($amount <= 0) {
            throw new RuntimeException('Hold amount must be positive.');
        }

        return DB::transaction(function () use ($userId, $amount, $reference) {
            $wallet = $this->ensureWallet($userId);
            $wallet = Wallet::whereKey($wallet->id)->lockForUpdate()->first();

            if ($wallet->balance < $amount) {
                throw new RuntimeException('Insufficient wallet balance to hold.');
            }

            $wallet->decrement('balance', $amount);
            $wallet->increment('locked_amount', $amount);

            $this->syncUserBalance($userId, $wallet->fresh()->balance);

            return $wallet->transactions()->create([
                'type' => TransactionType::Hold->value,
                'category' => 'withdrawal',
                'amount' => $amount,
                'balance_after' => $wallet->fresh()->balance,
                'reference' => $reference,
                'description' => 'Funds locked for pending withdrawal',
                'status' => 'completed',
            ]);
        });
    }

    public function release(int $userId, float $amount, ?string $reference = null): Transaction
    {
        if ($amount <= 0) {
            throw new RuntimeException('Release amount must be positive.');
        }

        return DB::transaction(function () use ($userId, $amount, $reference) {
            $wallet = $this->ensureWallet($userId);
            $wallet = Wallet::whereKey($wallet->id)->lockForUpdate()->first();

            if ($wallet->locked_amount < $amount) {
                throw new RuntimeException('Insufficient locked amount to release.');
            }

            $wallet->increment('balance', $amount);
            $wallet->decrement('locked_amount', $amount);

            $this->syncUserBalance($userId, $wallet->fresh()->balance);

            return $wallet->transactions()->create([
                'type' => TransactionType::Release->value,
                'category' => 'withdrawal',
                'amount' => $amount,
                'balance_after' => $wallet->fresh()->balance,
                'reference' => $reference,
                'description' => 'Locked funds released after withdrawal rejection',
                'status' => 'completed',
            ]);
        });
    }

    public function markWithdrawn(int $userId, float $amount, ?string $reference = null): void
    {
        DB::transaction(function () use ($userId, $amount) {
            $wallet = $this->ensureWallet($userId);
            $wallet = Wallet::whereKey($wallet->id)->lockForUpdate()->first();

            $wallet->decrement('locked_amount', $amount);
            $wallet->increment('total_withdrawn', $amount);
        });
    }

    private function syncUserBalance(int $userId, float $balance): void
    {
        User::whereKey($userId)->update(['balance' => $balance]);
    }
}
