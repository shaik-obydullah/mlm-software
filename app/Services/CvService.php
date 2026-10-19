<?php

namespace App\Services;

use App\Enums\PurchaseStatus;
use App\Models\CvActivation;
use App\Models\CvPurchase;
use App\Models\CvTier;
use App\Models\Pin;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CvService
{
    public function __construct(
        private PinService $pinService,
        private CommissionService $commissionService
    ) {}

    public function purchase(int $userId, int $tierId, string $paymentMethod = 'bank', ?string $transactionId = null): CvPurchase
    {
        $tier = CvTier::where('status', 'active')->findOrFail($tierId);

        $purchase = CvPurchase::create([
            'user_id' => $userId,
            'tier_id' => $tier->id,
            'amount' => $tier->amount,
            'price' => $tier->price ?? $tier->amount,
            'payment_method' => $paymentMethod,
            'status' => PurchaseStatus::Pending->value,
            'transaction_id' => $transactionId,
        ]);

        if ($paymentMethod === 'wallet') {
            $this->payFromWallet($purchase);
        }

        return $purchase;
    }

    public function payFromWallet(CvPurchase $purchase): CvPurchase
    {
        $walletService = app(WalletService::class);

        return DB::transaction(function () use ($purchase, $walletService) {
            $walletService->debit(
                $purchase->user_id,
                (float) $purchase->price,
                'purchase',
                'CV-'.$purchase->id,
                "CV purchase of {$purchase->amount} CV"
            );

            $purchase->update([
                'status' => PurchaseStatus::Paid->value,
                'paid_at' => now(),
                'transaction_id' => $purchase->transaction_id ?? 'WALLET-'.Str::random(8),
            ]);

            return $purchase->fresh();
        });
    }

    public function markPaid(int $purchaseId, ?int $verifiedBy = null): CvPurchase
    {
        $purchase = CvPurchase::findOrFail($purchaseId);

        if ($purchase->status !== PurchaseStatus::Pending->value) {
            throw new RuntimeException('Purchase cannot be marked paid from its current status.');
        }

        $purchase->update([
            'status' => PurchaseStatus::Paid->value,
            'paid_at' => now(),
        ]);

        return $purchase->fresh();
    }

    /**
     * Activate a paid purchase, optionally using an activation PIN.
     */
    public function activate(int $userId, int $purchaseId, ?string $pinCode = null): CvPurchase
    {
        return DB::transaction(function () use ($userId, $purchaseId, $pinCode) {
            $purchase = CvPurchase::where('id', $purchaseId)
                ->where('user_id', $userId)
                ->firstOrFail();

            if ($purchase->status === PurchaseStatus::Activated->value) {
                throw new RuntimeException('Purchase is already activated.');
            }

            if ($purchase->status !== PurchaseStatus::Paid->value) {
                throw new RuntimeException('Purchase must be paid before activation.');
            }

            if ($pinCode) {
                $pin = Pin::where('code', $pinCode)->valid()->first();

                if (! $pin) {
                    throw new RuntimeException('Invalid or expired PIN.');
                }

                if ($pin->cv_tier_id !== $purchase->tier_id) {
                    throw new RuntimeException('PIN does not match the purchased CV tier.');
                }

                if ($pin->assigned_to !== null && $pin->assigned_to !== $userId) {
                    throw new RuntimeException('PIN is assigned to another user.');
                }

                $this->pinService->use($pinCode, $userId, $purchase->id);
                $purchase->update(['pin_code' => $pinCode]);
            }

            $expiryMonths = (int) setting('cv_validity_months', 12);

            CvActivation::create([
                'purchase_id' => $purchase->id,
                'user_id' => $userId,
                'activation_date' => now(),
                'expiry_date' => now()->addMonths($expiryMonths),
                'status' => 'active',
            ]);

            $purchase->update([
                'status' => PurchaseStatus::Activated->value,
                'paid_at' => $purchase->paid_at ?? now(),
            ]);

            User::whereKey($userId)->update([
                'status' => 'active',
                'activated_at' => now(),
            ]);

            $this->commissionService->processForPurchase($purchase->fresh());

            return $purchase->fresh();
        });
    }

    public function activeVolume(int $userId): float
    {
        return (float) CvPurchase::query()
            ->where('user_id', $userId)
            ->where('status', PurchaseStatus::Activated->value)
            ->sum('amount');
    }

    public function history(int $userId): Collection
    {
        return CvPurchase::query()
            ->where('user_id', $userId)
            ->with(['tier', 'activation'])
            ->orderByDesc('id')
            ->get();
    }
}
