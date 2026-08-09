<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\CvPurchase;
use App\Models\KycDocument;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Services\MlmTreeService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private MlmTreeService $treeService) {}

    public function index(): View
    {
        $user = auth()->user();
        $wallet = $user->wallet;

        $stats = [
            'balance' => $wallet?->balance ?? 0,
            'locked' => $wallet?->locked_amount ?? 0,
            'total_earned' => $wallet?->total_earned ?? 0,
            'total_withdrawn' => $wallet?->total_withdrawn ?? 0,
            'active_cv' => (float) CvPurchase::where('user_id', $user->id)->where('status', 'activated')->sum('amount'),
            'downline' => $this->treeService->allDownline($user->id)->count(),
            'direct' => $user->referrals()->count(),
            'commissions' => (float) Commission::where('user_id', $user->id)->sum('amount'),
        ];

        $volumes = $this->treeService->legVolumes($user->id);

        $recentCommissions = Commission::where('user_id', $user->id)
            ->with('fromUser:id,name,unique_id')
            ->orderByDesc('id')->limit(5)->get();

        $recentTransactions = Transaction::whereHas('wallet', fn ($q) => $q->where('user_id', $user->id))
            ->orderByDesc('id')->limit(5)->get();

        $monthly = Transaction::whereHas('wallet', fn ($q) => $q->where('user_id', $user->id))
            ->where('type', 'credit')
            ->where('created_at', '>=', now()->subMonths(6))
            ->get(['amount', 'created_at'])
            ->groupBy(fn ($t) => $t->created_at->format('Y-m'))
            ->map(fn ($g) => (float) $g->sum('amount'))
            ->sortKeys();

        if ($user->isAdmin()) {
            $stats['platform'] = [
                'members' => User::where('role', '!=', 'admin')->count(),
                'active_members' => User::where('status', 'active')->count(),
                'total_cv' => (int) CvPurchase::where('status', 'activated')->sum('amount'),
                'pending_withdrawals' => WithdrawalRequest::where('status', 'pending')->count(),
                'open_tickets' => Ticket::whereIn('status', ['open', 'pending'])->count(),
                'pending_kyc' => KycDocument::where('status', 'pending')->count(),
            ];
        }

        return view('dashboard.index', compact('stats', 'volumes', 'recentCommissions', 'recentTransactions', 'monthly'));
    }
}
