<?php

namespace App\Services;

use App\Models\CvTier;
use App\Models\Pin;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class PinService
{
    public function generate(int $createdBy, int $tierId, int $count = 1, ?string $expiresAt = null): array
    {
        if ($count < 1 || $count > 500) {
            throw new \InvalidArgumentException('Count must be between 1 and 500.');
        }

        $tier = CvTier::findOrFail($tierId);

        $pins = [];

        for ($i = 0; $i < $count; $i++) {
            $pins[] = Pin::create([
                'code' => $this->uniqueCode(),
                'cv_tier_id' => $tier->id,
                'created_by' => $createdBy,
                'status' => 'available',
                'expires_at' => $expiresAt ? now()->parse($expiresAt) : null,
            ]);
        }

        return $pins;
    }

    public function assign(int $pinId, int $userId): Pin
    {
        $pin = Pin::whereKey($pinId)->available()->first();

        if (! $pin) {
            throw new RuntimeException('PIN is not available for assignment.');
        }

        $pin->update([
            'assigned_to' => $userId,
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        return $pin->fresh();
    }

    public function use(string $code, int $userId, int $purchaseId): Pin
    {
        $pin = Pin::where('code', $code)->valid()->first();

        if (! $pin) {
            throw new RuntimeException('Invalid or expired PIN.');
        }

        if ($pin->status === 'used') {
            throw new RuntimeException('PIN has already been used.');
        }

        if ($pin->assigned_to !== null && $pin->assigned_to !== $userId) {
            throw new RuntimeException('PIN is assigned to another user.');
        }

        $pin->update([
            'used_by' => $userId,
            'purchase_id' => $purchaseId ?: null,
            'status' => 'used',
            'used_at' => now(),
        ]);

        return $pin->fresh();
    }

    public function validate(string $code, int $userId, ?int $tierId = null): Pin
    {
        $pin = Pin::where('code', $code)->valid()->first();

        if (! $pin) {
            throw new RuntimeException('Invalid or expired PIN.');
        }

        if ($pin->status === 'used') {
            throw new RuntimeException('PIN has already been used.');
        }

        if ($pin->assigned_to !== null && $pin->assigned_to !== $userId) {
            throw new RuntimeException('PIN is assigned to another user.');
        }

        if ($tierId !== null && $pin->cv_tier_id !== $tierId) {
            throw new RuntimeException('PIN does not match the selected CV tier.');
        }

        return $pin;
    }

    public function available(?int $tierId = null): Collection
    {
        return Pin::query()
            ->with(['tier', 'assignedUser'])
            ->when($tierId, fn ($q) => $q->where('cv_tier_id', $tierId))
            ->available()
            ->orderByDesc('id')
            ->get();
    }

    public function expire(): int
    {
        return Pin::query()
            ->where('status', '!=', 'used')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);
    }

    private function uniqueCode(): string
    {
        do {
            $code = Str::upper(Str::random(4)).'-'.random_int(100000, 999999);
        } while (Pin::where('code', $code)->exists());

        return $code;
    }
}
