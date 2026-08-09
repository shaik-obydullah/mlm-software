<?php

namespace Database\Seeders;

use App\Models\CvTier;
use Illuminate\Database\Seeder;

class CvTierSeeder extends Seeder
{
    public function run(): void
    {
        $tiers = [
            ['name' => 'Bronze', 'amount' => 200, 'price' => 2000],
            ['name' => 'Silver', 'amount' => 400, 'price' => 4000],
            ['name' => 'Gold', 'amount' => 800, 'price' => 8000],
            ['name' => 'Platinum', 'amount' => 1500, 'price' => 15000],
            ['name' => 'Diamond', 'amount' => 2500, 'price' => 25000],
            ['name' => 'Royal Diamond', 'amount' => 3000, 'price' => 30000],
            ['name' => 'Crown', 'amount' => 4000, 'price' => 40000],
        ];

        foreach ($tiers as $tier) {
            CvTier::updateOrCreate(
                ['amount' => $tier['amount']],
                ['name' => $tier['name'], 'price' => $tier['price'], 'description' => $tier['name'].' CV package', 'status' => 'active']
            );
        }
    }
}
