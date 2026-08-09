<?php

namespace Database\Seeders;

use App\Models\InterestRate;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SystemConfigSeeder extends Seeder
{
    public function run(): void
    {
        InterestRate::updateOrCreate(
            ['effective_date' => now()->toDateString()],
            ['rate' => 2.0000, 'status' => 'active']
        );

        $settings = [
            'site_name' => 'MLM Software',
            'currency' => 'INR',
            'withdrawal_fee_percent' => 2,
            'min_withdrawal' => 100,
            'cv_validity_months' => 12,
            'interest_rate_percent' => 2,
            'interest_frequency' => 'monthly',
            'matching_bonus_percent' => 5,
            'sponsor_bonus_percent' => 10,
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
