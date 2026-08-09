<?php

namespace App\Services;

use App\Enums\CommissionType;
use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\CvPurchase;
use App\Models\MlmTree;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    public const MAX_LEVELS = 7;

    private const LEVEL_RATES = [1 => 0.05, 2 => 0.04, 3 => 0.03, 4 => 0.02, 5 => 0.02, 6 => 0.02, 7 => 0.02];

    public function __construct(private WalletService $walletService, private MlmTreeService $treeService) {}

    /**
     * Process sponsor, matching and level commissions for an activated purchase.
     */
    public function processForPurchase(CvPurchase $purchase): array
    {
        $purchase->loadMissing('user.sponsor');

        $results = [
            'sponsor' => $this->paySponsorBonus($purchase),
            'matching' => $this->payMatchingBonus($purchase),
            'levels' => $this->payLevelCommissions($purchase),
        ];

        return $results;
    }

    public function paySponsorBonus(CvPurchase $purchase): ?Commission
    {
        $sponsor = $purchase->user->sponsor;

        if (! $sponsor || $sponsor->isAdmin()) {
            return null;
        }

        $rule = CommissionRule::query()->active()->where('type', 'sponsor')->first();
        $rate = $rule ? (float) $rule->rate / 100 : 0.10;

        if ($this->alreadyPaid($purchase->id, CommissionType::Sponsor, $sponsor->id)) {
            return null;
        }

        $amount = round($purchase->amount * $rate, 2);

        if ($amount <= 0) {
            return null;
        }

        return DB::transaction(function () use ($purchase, $sponsor, $amount, $rate) {
            $commission = $this->recordCommission($purchase, $sponsor->id, CommissionType::Sponsor, $amount, $rate);

            $this->walletService->credit(
                $sponsor->id,
                $amount,
                'commission',
                'SPONSOR-'.$purchase->id,
                "Sponsor bonus from {$purchase->user->name} for {$purchase->amount} CV"
            );

            return $commission;
        });
    }

    public function payMatchingBonus(CvPurchase $purchase): ?Commission
    {
        $node = MlmTree::where('user_id', $purchase->user_id)->first();

        if (! $node) {
            return null;
        }

        $upline = $node->parent;

        if (! $upline) {
            return null;
        }

        if ($this->alreadyPaid($purchase->id, CommissionType::Matching, $upline->user_id)) {
            return null;
        }

        $rule = CommissionRule::query()->active()->where('type', 'matching')->first();
        $rate = $rule ? (float) $rule->rate / 100 : 0.05;

        $volumes = $this->treeService->legVolumes($upline->user_id);
        $matchedVolume = min($volumes['left'], $volumes['right']);

        $amount = round($matchedVolume * $rate, 2);

        if ($amount <= 0) {
            return null;
        }

        return DB::transaction(function () use ($purchase, $upline, $amount, $rate) {
            $commission = $this->recordCommission($purchase, $upline->user_id, CommissionType::Matching, $amount, $rate);

            $this->walletService->credit(
                $upline->user_id,
                $amount,
                'commission',
                'MATCH-'.$purchase->id,
                "Matching bonus on {$purchase->amount} CV activation"
            );

            return $commission;
        });
    }

    public function payLevelCommissions(CvPurchase $purchase): array
    {
        $commissions = [];

        $node = MlmTree::where('user_id', $purchase->user_id)->first();

        if (! $node) {
            return $commissions;
        }

        for ($level = 1; $level <= self::MAX_LEVELS; $level++) {
            $uplineNode = $this->treeService->upline($node, $level);

            if (! $uplineNode) {
                continue;
            }

            $upline = User::find($uplineNode->user_id);

            if (! $upline || $upline->isAdmin()) {
                continue;
            }

            if ($this->alreadyPaid($purchase->id, CommissionType::Level, $upline->id, $level)) {
                continue;
            }

            $rule = CommissionRule::query()
                ->active()
                ->where('type', 'level')
                ->where('level', $level)
                ->first();

            $rate = $rule ? (float) $rule->rate / 100 : self::LEVEL_RATES[$level];

            $amount = round($purchase->amount * $rate, 2);

            if ($amount <= 0) {
                continue;
            }

            $commissions[$level] = DB::transaction(function () use ($purchase, $upline, $level, $amount, $rate) {
                $commission = $this->recordCommission(
                    $purchase,
                    $upline->id,
                    CommissionType::Level,
                    $amount,
                    $rate,
                    $level
                );

                $this->walletService->credit(
                    $upline->id,
                    $amount,
                    'commission',
                    'LEVEL-'.$level.'-'.$purchase->id,
                    "Level {$level} commission from {$purchase->user->name}"
                );

                return $commission;
            });
        }

        return $commissions;
    }

    public function alreadyPaid(int $purchaseId, CommissionType $type, int $userId, ?int $level = null): bool
    {
        return Commission::query()
            ->where('cv_purchase_id', $purchaseId)
            ->where('user_id', $userId)
            ->where('type', $type->value)
            ->where('status', '!=', 'reversed')
            ->when($level !== null, fn ($q) => $q->where('level', $level))
            ->exists();
    }

    public function markAllPaid(int $batchId): int
    {
        return Commission::query()
            ->where('status', 'pending')
            ->update(['status' => 'paid', 'paid_at' => now()]);
    }

    public function processRoyalty(int $periodYear, int $periodMonth): void
    {
        // placeholder for monthly royalty pool; wired via scheduled job
    }

    private function recordCommission(
        CvPurchase $purchase,
        int $userId,
        CommissionType $type,
        float $amount,
        float $rate,
        ?int $level = null
    ): Commission {
        return Commission::create([
            'user_id' => $userId,
            'from_user_id' => $purchase->user_id,
            'cv_purchase_id' => $purchase->id,
            'type' => $type->value,
            'amount' => $amount,
            'cv_volume' => $purchase->amount,
            'level' => $level,
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }
}
