<?php

namespace App\Jobs;

use App\Services\InterestService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessInterestAccrual implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public function __construct(public readonly string $period) {}

    public function handle(InterestService $interestService): void
    {
        $credited = $interestService->processMonthly($this->period);

        Log::info('Interest accrual processed', [
            'period' => $this->period,
            'credited' => $credited,
        ]);
    }
}
