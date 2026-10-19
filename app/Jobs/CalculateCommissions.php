<?php

namespace App\Jobs;

use App\Models\CvPurchase;
use App\Services\CommissionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CalculateCommissions implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public int $tries = 2;

    public function __construct(public readonly int $purchaseId) {}

    public function handle(CommissionService $commissionService): void
    {
        $purchase = CvPurchase::with('user.sponsor')->find($this->purchaseId);

        if (! $purchase) {
            return;
        }

        if ($purchase->status !== 'activated') {
            return;
        }

        $results = $commissionService->processForPurchase($purchase);

        Log::info('Commissions calculated', [
            'purchase_id' => $this->purchaseId,
            'results' => $results,
        ]);
    }
}
