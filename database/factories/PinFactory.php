<?php

namespace Database\Factories;

use App\Models\CvTier;
use App\Models\Pin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Pin>
 */
class PinFactory extends Factory
{
    protected $model = Pin::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(Str::random(4)).'-'.fake()->unique()->numerify('######'),
            'cv_tier_id' => CvTier::factory(),
            'created_by' => User::factory(),
            'assigned_to' => null,
            'used_by' => null,
            'purchase_id' => null,
            'status' => 'available',
            'expires_at' => null,
            'assigned_at' => null,
            'used_at' => null,
        ];
    }

    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => User::factory(),
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);
    }
}
