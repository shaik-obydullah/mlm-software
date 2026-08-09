<?php

namespace Database\Factories;

use App\Models\CvPurchase;
use App\Models\CvTier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CvPurchase>
 */
class CvPurchaseFactory extends Factory
{
    protected $model = CvPurchase::class;

    public function definition(): array
    {
        $tier = CvTier::inRandomOrder()->first() ?? CvTier::factory()->create();

        return [
            'user_id' => User::factory(),
            'tier_id' => $tier->id,
            'amount' => $tier->amount,
            'price' => $tier->price ?? $tier->amount,
            'payment_method' => 'bank',
            'status' => 'pending',
            'transaction_id' => 'TXN-'.fake()->unique()->numerify('########'),
            'pin_code' => null,
            'paid_at' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function activated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'activated',
            'paid_at' => now()->subDays(1),
        ]);
    }
}
