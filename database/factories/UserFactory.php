<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'unique_id' => 'MLM'.strtoupper(Str::random(6)),
            'sponsor_id' => null,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'phone' => fake()->phoneNumber(),
            'role' => 'member',
            'status' => 'active',
            'kyc_status' => 'approved',
            'nominee_name' => fake()->name(),
            'nominee_relation' => fake()->randomElement(['spouse', 'father', 'mother', 'sibling']),
            'nominee_phone' => fake()->phoneNumber(),
            'bank_name' => fake()->company(),
            'bank_account_name' => fake()->name(),
            'bank_account_number' => fake()->numerify('############'),
            'bank_ifsc' => strtoupper(fake()->regexify('[A-Z]{4}0[A-Z0-9]{6}')),
            'balance' => 0,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Admin',
            'email' => 'admin@mlm.local',
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    public function agent(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'agent',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'kyc_status' => 'not_submitted',
        ]);
    }

    public function kycPending(): static
    {
        return $this->state(fn (array $attributes) => [
            'kyc_status' => 'pending',
        ]);
    }
}
