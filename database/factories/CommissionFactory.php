<?php

namespace Database\Factories;

use App\Models\Commission;
use App\Models\CvPurchase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Commission>
 */
class CommissionFactory extends Factory
{
    protected $model = Commission::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'from_user_id' => User::factory(),
            'cv_purchase_id' => CvPurchase::factory(),
            'type' => fake()->randomElement(['sponsor', 'matching', 'level']),
            'amount' => fake()->randomFloat(2, 10, 500),
            'cv_volume' => fake()->randomElement([200, 400, 800, 1500]),
            'level' => null,
            'status' => 'paid',
            'paid_at' => now(),
        ];
    }

    public function level(int $level): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'level',
            'level' => $level,
        ]);
    }
}
