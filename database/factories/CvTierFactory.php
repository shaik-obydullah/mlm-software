<?php

namespace Database\Factories;

use App\Models\CvTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CvTier>
 */
class CvTierFactory extends Factory
{
    protected $model = CvTier::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'amount' => fake()->randomElement([200, 400, 800, 1500, 2500, 3000, 4000]),
            'price' => fn (array $attrs) => $attrs['amount'],
            'description' => fake()->sentence(),
            'status' => 'active',
        ];
    }
}
