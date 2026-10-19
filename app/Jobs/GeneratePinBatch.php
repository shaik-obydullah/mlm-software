<?php

namespace App\Jobs;

use App\Services\PinService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GeneratePinBatch implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public function __construct(
        public readonly int $tierId,
        public readonly int $count,
        public readonly ?int $createdBy = null,
        public readonly ?string $expiresAt = null
    ) {}

    public function handle(PinService $pinService): void
    {
        $pins = $pinService->generate($this->createdBy ?? 1, $this->tierId, $this->count, $this->expiresAt);

        Log::info('PIN batch generated', [
            'tier_id' => $this->tierId,
            'count' => count($pins),
        ]);
    }
}
