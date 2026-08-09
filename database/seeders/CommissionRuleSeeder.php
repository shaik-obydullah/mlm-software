<?php

namespace Database\Seeders;

use App\Models\CommissionRule;
use Illuminate\Database\Seeder;

class CommissionRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            ['name' => 'Sponsor Bonus', 'type' => 'sponsor', 'rate' => 10.0000, 'min_volume' => 0, 'level' => null],
            ['name' => 'Matching Bonus', 'type' => 'matching', 'rate' => 5.0000, 'min_volume' => 0, 'level' => null],
            ['name' => 'Level 1', 'type' => 'level', 'rate' => 5.0000, 'min_volume' => 0, 'level' => 1],
            ['name' => 'Level 2', 'type' => 'level', 'rate' => 4.0000, 'min_volume' => 0, 'level' => 2],
            ['name' => 'Level 3', 'type' => 'level', 'rate' => 3.0000, 'min_volume' => 0, 'level' => 3],
            ['name' => 'Level 4', 'type' => 'level', 'rate' => 2.0000, 'min_volume' => 0, 'level' => 4],
            ['name' => 'Level 5', 'type' => 'level', 'rate' => 2.0000, 'min_volume' => 0, 'level' => 5],
            ['name' => 'Level 6', 'type' => 'level', 'rate' => 2.0000, 'min_volume' => 0, 'level' => 6],
            ['name' => 'Level 7', 'type' => 'level', 'rate' => 2.0000, 'min_volume' => 0, 'level' => 7],
        ];

        foreach ($rules as $rule) {
            CommissionRule::updateOrCreate(
                ['type' => $rule['type'], 'level' => $rule['level']],
                $rule + ['status' => 'active']
            );
        }
    }
}
