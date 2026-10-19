<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(private WalletService $walletService) {}

    public function index(): View
    {
        $wallet = auth()->user()->wallet ?? $this->walletService->ensureWallet(auth()->id());
        $transactions = $wallet->transactions()->orderByDesc('id')->paginate(15);
        $withdrawals = WithdrawalRequest::where('user_id', auth()->id())->orderByDesc('id')->get();

        return view('wallet.index', compact('wallet', 'transactions', 'withdrawals'));
    }

    public function withdraw(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $user = auth()->user();

        if ($user->kyc_status !== 'approved') {
            return back()->with('error', 'KYC approval is required before withdrawing funds.');
        }

        $minAmount = (float) setting('min_withdrawal', 100);

        if ($validated['amount'] < $minAmount) {
            return back()->with('error', "Minimum withdrawal is {$minAmount}.");
        }

        $feePercent = (float) setting('withdrawal_fee_percent', 0);
        $fee = round($validated['amount'] * $feePercent / 100, 2);

        try {
            $this->walletService->hold($user->id, $validated['amount']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        WithdrawalRequest::create([
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'fee' => $fee,
            'net_amount' => round($validated['amount'] - $fee, 2),
            'method' => 'bank',
            'bank_name' => $user->bank_name,
            'account_name' => $user->bank_account_name,
            'account_number' => $user->bank_account_number,
            'ifsc' => $user->bank_ifsc,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Withdrawal request submitted.');
    }
}
