<?php

namespace Database\Seeders;

use App\Models\CvTier;
use App\Models\MlmTree;
use App\Models\Sponsor;
use App\Models\User;
use App\Services\CvService;
use App\Services\MlmTreeService;
use App\Services\WalletService;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function __construct(
        private MlmTreeService $treeService,
        private CvService $cvService,
        private WalletService $walletService
    ) {}

    public function run(): void
    {
        if (User::where('email', 'admin@mlm.local')->doesntExist()) {
            $admin = User::create([
                'unique_id' => 'MLMADMIN',
                'name' => 'Admin',
                'email' => 'admin@mlm.local',
                'password' => bcrypt('password'),
                'phone' => '+910000000000',
                'role' => 'admin',
                'status' => 'active',
                'kyc_status' => 'approved',
            ]);
            $admin->assignRole('admin');
            $this->walletService->ensureWallet($admin->id);
        }

        $root = User::firstOrCreate(
            ['email' => 'root@mlm.local'],
            ['unique_id' => 'MLMROOT', 'name' => 'Root Sponsor', 'password' => bcrypt('password'),
                'phone' => '+910000000001', 'role' => 'member', 'status' => 'active', 'kyc_status' => 'approved', ]
        );
        $root->assignRole('member');

        if (! $root->treeNode) {
            $this->treeService->place($root, $root->id);
        }
        $this->walletService->ensureWallet($root->id);

        $sponsor = $root;

        $names = [
            'Aarav Sharma', 'Priya Patel', 'Rohan Mehta', 'Ananya Iyer', 'Vikram Singh',
            'Sneha Reddy', 'Arjun Nair', 'Divya Rao', 'Kabir Khan', 'Meera Joshi',
            'Rahul Verma', 'Ishita Bose', 'Aditya Gupta', 'Pooja Menon', 'Nikhil Das',
            'Sara Fernandes', 'Karan Malhotra', 'Neha Kulkarni', 'Sameer Ali', 'Riya Chawla',
            'Dev Sharma', 'Tara Pillai', 'Yash Desai', 'Lakshmi Subramanian', 'Faizan Qureshi',
            'Zoya Sheikh', 'Gaurav Thakur', 'Aisha Banerjee', 'Manav Ahuja', 'Nandini Hegde',
        ];

        $tiers = CvTier::where('status', 'active')->pluck('id', 'amount');

        foreach ($names as $index => $name) {
            $user = User::firstOrCreate(
                ['email' => strtolower(str_replace(' ', '.', $name)).'@mlm.local'],
                ['unique_id' => 'MLM'.str_pad((string) (1000 + $index), 5, '0', STR_PAD_LEFT),
                    'name' => $name, 'password' => bcrypt('password'), 'phone' => '+91'.fake()->numerify('9#########'),
                    'role' => $index % 3 === 0 ? 'agent' : 'member', 'status' => 'active', 'kyc_status' => 'approved', ]
            );
            $user->assignRole($index % 3 === 0 ? 'agent' : 'member');

            Sponsor::updateOrCreate(
                ['sponsee_id' => $user->id],
                ['sponsor_id' => $sponsor->id, 'type' => 'direct']
            );

            if (! $user->treeNode) {
                $this->treeService->place($user, $sponsor->id);
            }

            $this->walletService->ensureWallet($user->id);
        }

        $this->activateSampleCv($tiers);

        $this->command?->info('Demo data seeded: admin@mlm.local / password');
    }

    private function activateSampleCv($tiers): void
    {
        $members = MlmTree::with('user')->where('level', '>', 0)->inRandomOrder()->limit(24)->get();

        foreach ($members as $i => $node) {
            $amount = match ($i % 4) {
                0 => 400,
                1 => 800,
                2 => 200,
                default => 1500,
            };

            $tierId = $tiers[$amount] ?? $tiers->first();

            if (! $tierId) {
                continue;
            }

            $purchase = $this->cvService->purchase($node->user_id, $tierId, 'bank');

            if (! $purchase->activation) {
                try {
                    $this->cvService->markPaid($purchase->id);
                    $this->cvService->activate($node->user_id, $purchase->id);
                } catch (\Throwable $e) {
                    // skip member whose purchase chain is incomplete
                }
            }
        }
    }
}
