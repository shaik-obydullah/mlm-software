<?php

namespace App\Services;

use App\Models\CvPurchase;
use App\Models\InterestAccrual;
use App\Models\InterestRate;
use Illuminate\Support\Facades\DB;

class InterestService
{
    public function __construct(private WalletService $walletService) {}

    /**
     * Credit monthly interest on all active CV for a given period (YYYY-MM).
     */
    public function processMonthly(string $period): int
    {
        [$year, $month] = array_map('intval', explode('-', $period));

        $rate = InterestRate::query()->active()
            ->orderByDesc('effective_date')
            ->first();

        if (! $rate) {
            return 0;
        }

        $monthlyRate = (float) $rate->rate / 100;

        $purchases = CvPurchase::query()
            ->where('status', 'activated')
            ->with('activation')
            ->get();

        $credited = 0;

        foreach ($purchases as $purchase) {
            $credited += $this->accrueForPurchase($purchase, $period, $monthlyRate);
        }

        return $credited;
    }

    private function accrueForPurchase(CvPurchase $purchase, string $period, float $monthlyRate): int
    {
        $exists = InterestAccrual::query()
            ->where('user_id', $purchase->user_id)
            ->where('cv_purchase_id', $purchase->id)
            ->where('period', $period)
            ->exists();

        if ($exists) {
            return 0;
        }

        if ($purchase->activation && $purchase->activation->activation_date->format('Y-m') > $period) {
            return 0;
        }

        $amount = round($purchase->amount * $monthlyRate, 2);

        if ($amount <= 0) {
            return 0;
        }

        return DB::transaction(function () use ($purchase, $period, $monthlyRate, $amount) {
            $accrual = InterestAccrual::create([
                'user_id' => $purchase->user_id,
                'cv_purchase_id' => $purchase->id,
                'amount' => $amount,
                'rate' => $monthlyRate * 100,
                'period' => $period,
                'status' => 'paid',
            ]);

            $this->walletService->credit(
                $purchase->user_id,
                $amount,
                'interest',
                'INT-'.$accrual->id,
                "Monthly interest for {$period}"
            );

            return 1;
        });
    }
}
