<?php

namespace Database\Seeders;

use App\Models\Commission;
use App\Models\CvTier;
use App\Models\KycDocument;
use App\Models\MlmTree;
use App\Models\Pin;
use App\Models\Sponsor;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Services\CvService;
use App\Services\MlmTreeService;
use App\Services\PinService;
use App\Services\WalletService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    public function __construct(
        private WalletService $walletService,
        private PinService $pinService,
        private CvService $cvService,
        private MlmTreeService $treeService,
    ) {}

    public function run(): void
    {
        $admin = User::where('email', 'admin@mlm.local')->first();
        $root = User::where('email', 'root@mlm.local')->first();

        if (! $admin || ! $root) {
            $this->command?->error('Run the DemoDataSeeder first (php artisan db:seed).');

            return;
        }

        $this->seedAdminWallet($admin);
        $this->seedMonthlyEarnings($admin);
        $this->seedAdminCommissions($admin);
        $this->seedAdminWithdrawals($admin);
        $this->seedWithdrawals($admin, $root);
        $this->seedTickets($admin, $root);
        $this->seedKyc($admin);
        $this->seedPins($admin);
        $this->seedTreeRestructure($admin, $root);
        $this->seedRightLegCv($root);

        $this->command?->info('Test data seeded successfully.');
    }

    private function seedAdminWallet(User $admin): void
    {
        if ($admin->wallet?->balance == 0) {
            $this->walletService->credit($admin->id, 2500, 'bonus', 'TEST-DEMO-BONUS', 'Demo sponsor bonus');
            $this->walletService->credit($admin->id, 860, 'interest', 'TEST-INTEREST-001', 'Monthly interest credit');
        }
    }

    private function seedAdminCommissions(User $admin): void
    {
        if (Commission::where('user_id', $admin->id)->exists()) {
            return;
        }

        $members = MlmTree::with('user')->where('level', '>', 0)->inRandomOrder()->take(5)->get();

        $commissions = [
            ['sponsor', 240, 200],
            ['matching', 125, 180],
            ['level', 96, 160],
            ['sponsor', 320, 220],
            ['royalty', 45, 140],
        ];

        foreach ($commissions as $i => [$type, $amount, $cv]) {
            $from = $members->get($i);

            $this->walletService->credit(
                $admin->id,
                $amount,
                'commission',
                'TEST-COMM-'.Str::upper(Str::random(6)),
                ucfirst($type).' commission'
            );

            $createdAt = now()->subDays(rand(1, 25));

            Commission::create([
                'user_id' => $admin->id,
                'from_user_id' => $from?->user_id,
                'type' => $type,
                'amount' => $amount,
                'cv_volume' => $cv,
                'level' => $type === 'level' ? 2 : null,
                'status' => 'paid',
                'paid_at' => $createdAt,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }

    private function seedMonthlyEarnings(User $admin): void
    {
        if (Transaction::where('reference', 'like', 'TEST-MONTHLY-%')->count() >= 6) {
            return;
        }

        $months = [
            ['interest', 820, 5],
            ['commission', 640, 4],
            ['interest', 905, 3],
            ['commission', 715, 2],
            ['interest', 780, 1],
            ['commission', 860, 0],
        ];

        foreach ($months as [$category, $amount, $monthsAgo]) {
            $transaction = $this->walletService->credit(
                $admin->id,
                $amount,
                $category,
                'TEST-MONTHLY-'.Str::upper(Str::random(6)),
                $category === 'interest' ? 'Monthly interest credit' : 'Commission payout'
            );

            $date = now()->subMonths($monthsAgo)->startOfMonth()->addDays(rand(5, 20));

            $transaction->update([
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }

    private function seedAdminWithdrawals(User $admin): void
    {
        if (WithdrawalRequest::where('user_id', $admin->id)->exists()) {
            return;
        }

        $this->walletService->hold($admin->id, 500);
        $this->walletService->markWithdrawn($admin->id, 500);

        WithdrawalRequest::create([
            'user_id' => $admin->id,
            'amount' => 500,
            'fee' => 10,
            'net_amount' => 490,
            'method' => 'bank',
            'bank_name' => 'HDFC Bank',
            'account_name' => $admin->name,
            'account_number' => '50100234567890',
            'ifsc' => 'HDFC0001234',
            'status' => 'completed',
            'remark' => 'Paid via bank transfer',
            'approved_by' => $admin->id,
            'approved_at' => now()->subDays(3),
            'completed_at' => now()->subDays(2),
        ]);

        $this->walletService->hold($admin->id, 300);

        WithdrawalRequest::create([
            'user_id' => $admin->id,
            'amount' => 300,
            'fee' => 6,
            'net_amount' => 294,
            'method' => 'bank',
            'bank_name' => 'HDFC Bank',
            'account_name' => $admin->name,
            'account_number' => '50100234567890',
            'ifsc' => 'HDFC0001234',
            'status' => 'pending',
        ]);
    }

    private function seedWithdrawals(User $admin, User $root): void
    {
        $tara = User::where('email', 'tara.pillai@mlm.local')->first();
        $aditya = User::where('email', 'aditya.gupta@mlm.local')->first();
        $ananya = User::where('email', 'ananya.iyer@mlm.local')->first();
        $ishita = User::where('email', 'ishita.bose@mlm.local')->first();

        $bank = [
            'bank_name' => 'State Bank of India',
            'bank_account_name' => 'Test Member',
            'bank_account_number' => '35670012345678',
            'bank_ifsc' => 'SBIN0001234',
        ];

        foreach ([$tara, $aditya, $ananya, $ishita] as $member) {
            if ($member) {
                $member->update($bank);
            }
        }

        $withdrawals = [
            [$root, 150, 'pending'],
            [$tara, 120, 'pending'],
            [$ishita, 110, 'pending'],
            [$aditya, 100, 'completed'],
            [$ananya, 90, 'rejected'],
        ];

        foreach ($withdrawals as [$user, $amount, $status]) {
            if (! $user || WithdrawalRequest::where('user_id', $user->id)->exists()) {
                continue;
            }

            $user->load('wallet');

            if ($user->wallet?->balance < $amount) {
                $this->walletService->credit(
                    $user->id,
                    $amount - $user->wallet->balance + 50,
                    'commission',
                    'TEST-TOPUP-'.Str::upper(Str::random(6)),
                    'Demo commission top-up'
                );
            }

            $this->walletService->hold($user->id, $amount);

            $fee = round($amount * 2 / 100, 2);

            $withdrawal = WithdrawalRequest::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'fee' => $fee,
                'net_amount' => round($amount - $fee, 2),
                'method' => 'bank',
                'bank_name' => 'State Bank of India',
                'account_name' => $user->bank_account_name,
                'account_number' => $user->bank_account_number,
                'ifsc' => $user->bank_ifsc,
                'status' => 'pending',
            ]);

            if ($status === 'completed') {
                $this->walletService->markWithdrawn($user->id, $amount);
                $withdrawal->update([
                    'status' => 'completed',
                    'remark' => 'Paid via bank transfer',
                    'approved_by' => $admin->id,
                    'approved_at' => now()->subDays(2),
                    'completed_at' => now()->subDay(),
                ]);
            } elseif ($status === 'rejected') {
                $this->walletService->release($user->id, $amount);
                $withdrawal->update([
                    'status' => 'rejected',
                    'remark' => 'Bank details did not match',
                    'approved_by' => $admin->id,
                    'approved_at' => now()->subDays(3),
                ]);
            }
        }
    }

    private function seedTickets(User $admin, User $root): void
    {
        if (Ticket::count() > 0) {
            return;
        }

        $members = User::whereIn('email', [
            'aarav.sharma@mlm.local',
            'priya.patel@mlm.local',
            'vikram.singh@mlm.local',
        ])->get();

        $tickets = [
            [0, 'Withdrawal not received yet', 'withdrawal', 'high', 'open'],
            [1, 'How do commissions work?', 'commission', 'medium', 'pending'],
            [2, 'Login issue on mobile', 'technical', 'low', 'open'],
        ];

        foreach ($tickets as [$i, $subject, $category, $priority, $status]) {
            $member = $members[$i] ?? $root;

            $ticket = Ticket::create([
                'user_id' => $member->id,
                'subject' => $subject,
                'category' => $category,
                'priority' => $priority,
                'status' => $status,
            ]);

            TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => $member->id,
                'message' => 'I have submitted this request, please help.',
            ]);

            TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => $admin->id,
                'message' => 'Thanks for reaching out. Our team is looking into it and will update you shortly.',
            ]);
        }
    }

    private function seedKyc(User $admin): void
    {
        if (KycDocument::count() > 0) {
            return;
        }

        $members = User::whereIn('email', [
            'rohan.mehta@mlm.local',
            'sneha.reddy@mlm.local',
            'kabir.khan@mlm.local',
        ])->get();

        $docs = [
            [0, 'id_card'],
            [0, 'selfie'],
            [1, 'address_proof'],
            [2, 'passport'],
        ];

        foreach ($docs as [$i, $type]) {
            $member = $members[$i] ?? null;

            if (! $member) {
                continue;
            }

            KycDocument::create([
                'user_id' => $member->id,
                'type' => $type,
                'file_path' => 'kyc/'.$member->unique_id.'/'.$type.'.jpg',
                'status' => 'pending',
            ]);
        }
    }

    private function seedPins(User $admin): void
    {
        if (Pin::count() > 0) {
            return;
        }

        $tier = \App\Models\CvTier::orderBy('amount')->first();
        $root = User::where('email', 'root@mlm.local')->first();

        if (! $tier) {
            return;
        }

        $this->pinService->generate($admin->id, $tier->id, 10);

        $pins = $this->pinService->available($tier->id);

        if ($pins->isNotEmpty() && $root) {
            $pin = $pins->first();
            $this->pinService->assign($pin->id, $root->id);

            $used = $pins->get(1);
            if ($used) {
                $used->update([
                    'used_by' => $root->id,
                    'status' => 'used',
                    'used_at' => now()->subDays(5),
                ]);
            }
        }
    }

    private function seedTreeRestructure(User $admin, User $root): void
    {
        if ($admin->treeNode) {
            return;
        }

        $rootNode = MlmTree::where('user_id', $root->id)->first();

        if (! $rootNode) {
            return;
        }

        $adminNode = $this->treeService->createRoot($admin);

        $oldRootPath = '/'.$rootNode->id.'/';
        $newLeftPath = '/'.$adminNode->id.'/';
        $newRightPath = '/'.$adminNode->id.'/';

        $priya = MlmTree::where('user_id', User::where('email', 'priya.patel@mlm.local')->value('id'))->first();

        $rootNode->update([
            'parent_id' => $adminNode->id,
            'position' => 'left',
            'level' => 1,
            'path' => $newLeftPath,
        ]);

        $leftSubtree = MlmTree::where('path', 'like', $oldRootPath.'%');

        if ($priya) {
            $leftSubtree->where('id', '!=', $priya->id)
                ->where('path', 'not like', $oldRootPath.$priya->id.'/%');
        }

        $leftSubtree->update([
            'path' => DB::raw("REPLACE(path, '$oldRootPath', '$newLeftPath'.$rootNode->id.'/')"),
            'level' => DB::raw('level + 1'),
        ]);

        if ($priya) {
            $priya->update([
                'parent_id' => $adminNode->id,
                'position' => 'right',
                'level' => 1,
                'path' => $newRightPath,
            ]);

            MlmTree::where('path', 'like', $oldRootPath.$priya->id.'/%')
                ->update(['path' => DB::raw("REPLACE(path, '$oldRootPath', '$newRightPath')")]);
        }
    }

    private function seedRightLegCv(User $root): void
    {
        if (User::where('email', 'balance01@mlm.local')->exists()) {
            return;
        }

        $tiers = \App\Models\CvTier::where('status', 'active')->pluck('id', 'amount');

        $priya = User::where('email', 'priya.patel@mlm.local')->first();
        $placeUnder = $priya ?? $root;

        $members = [
            ['balance01@mlm.local', 'Rohan Khanna', 'MLMB001', 1500],
            ['balance02@mlm.local', 'Priyanka Saxena', 'MLMB002', 1500],
            ['balance03@mlm.local', 'Varun Malhotra', 'MLMB003', 800],
            ['balance04@mlm.local', 'Kavya Nair', 'MLMB004', 400],
            ['balance05@mlm.local', 'Arnav Deshmukh', 'MLMB005', 200],
            ['balance06@mlm.local', 'Ritika Sharma', 'MLMB006', 1500],
            ['balance07@mlm.local', 'Mohit Kapoor', 'MLMB007', 800],
            ['balance08@mlm.local', 'Sana Qureshi', 'MLMB008', 800],
        ];

        foreach ($members as [$email, $name, $uniqueId, $amount]) {
            $user = User::create([
                'unique_id' => $uniqueId,
                'sponsor_id' => $root->id,
                'name' => $name,
                'email' => $email,
                'password' => bcrypt('password'),
                'role' => 'member',
                'status' => 'active',
                'kyc_status' => 'approved',
            ]);
            $user->assignRole('member');

            Sponsor::updateOrCreate(
                ['sponsee_id' => $user->id],
                ['sponsor_id' => $root->id, 'type' => 'direct']
            );

            $this->treeService->place($user, $placeUnder->id);
            $this->walletService->ensureWallet($user->id);

            $this->activateCv($user, $tiers[$amount] ?? $tiers->first());
        }

        if ($priya && ! $priya->purchases()->where('status', 'activated')->exists()) {
            $this->activateCv($priya, $tiers[800] ?? $tiers->first());
        }
    }

    private function activateCv(User $user, ?int $tierId): void
    {
        if (! $tierId) {
            return;
        }

        try {
            $purchase = $this->cvService->purchase($user->id, $tierId, 'bank');
            $this->cvService->markPaid($purchase->id);
            $this->cvService->activate($user->id, $purchase->id);
        } catch (\Throwable $e) {
            $this->command?->warn('Skipped CV activation for '.$user->email.': '.$e->getMessage());
        }
    }
}
