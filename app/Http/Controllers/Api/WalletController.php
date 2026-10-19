<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\WithdrawalRequest;
use App\Services\AuditService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(private WalletService $walletService, private AuditService $audit) {}

    public function balance(Request $request): JsonResponse
    {
        $wallet = $request->user()->wallet
            ?? $this->walletService->ensureWallet($request->user()->id);

        return response()->json([
            'wallet' => $wallet,
            'available' => $wallet->balance,
            'locked' => $wallet->locked_amount,
            'total_earned' => $wallet->total_earned,
            'total_withdrawn' => $wallet->total_withdrawn,
        ]);
    }

    public function transactions(Request $request): JsonResponse
    {
        $wallet = $this->walletService->ensureWallet($request->user()->id);

        $transactions = Transaction::where('wallet_id', $wallet->id)
            ->when($request->category, fn ($q) => $q->where('category', $request->category))
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->orderByDesc('id')
            ->paginate(15);

        return response()->json($transactions);
    }

    public function requestWithdrawal(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'bank_name' => ['required', 'string', 'max:255'],
            'account_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:50'],
            'ifsc' => ['required', 'string', 'max:30'],
        ]);

        $user = $request->user();

        if ($user->kyc_status !== 'approved') {
            return response()->json(['message' => 'KYC approval is required before withdrawing funds.'], 422);
        }

        $feePercent = (float) setting('withdrawal_fee_percent', 0);
        $minAmount = (float) setting('min_withdrawal', 100);

        if ($validated['amount'] < $minAmount) {
            return response()->json(['message' => "Minimum withdrawal is {$minAmount}."], 422);
        }

        $fee = round($validated['amount'] * $feePercent / 100, 2);
        $net = round($validated['amount'] - $fee, 2);

        try {
            $this->walletService->hold(
                $user->id,
                $validated['amount'],
                'WDL-PENDING'
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $withdrawal = WithdrawalRequest::create([
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'fee' => $fee,
            'net_amount' => $net,
            'method' => 'bank',
            'bank_name' => $validated['bank_name'],
            'account_name' => $validated['account_name'],
            'account_number' => $validated['account_number'],
            'ifsc' => $validated['ifsc'],
            'status' => 'pending',
        ]);

        $this->audit->log('withdrawal_requested', $withdrawal, null, $withdrawal->toArray());

        return response()->json([
            'message' => 'Withdrawal request submitted.',
            'withdrawal' => $withdrawal,
        ], 201);
    }

    public function withdrawalHistory(Request $request): JsonResponse
    {
        $withdrawals = $request->user()->withdrawalRequests()
            ->orderByDesc('id')
            ->paginate(15);

        return response()->json($withdrawals);
    }

    public function approveWithdrawal(Request $request, int $id): JsonResponse
    {
        $withdrawal = WithdrawalRequest::findOrFail($id);

        if (! in_array($withdrawal->status, ['pending', 'processing'])) {
            return response()->json(['message' => 'Withdrawal cannot be approved.'], 422);
        }

        $withdrawal->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        $this->walletService->markWithdrawn(
            $withdrawal->user_id,
            $withdrawal->amount,
            'WDL-'.$withdrawal->id
        );

        $this->audit->log('withdrawal_approved', $withdrawal);

        return response()->json(['message' => 'Withdrawal approved.', 'withdrawal' => $withdrawal]);
    }

    public function rejectWithdrawal(Request $request, int $id): JsonResponse
    {
        $withdrawal = WithdrawalRequest::findOrFail($id);

        if (! in_array($withdrawal->status, ['pending', 'processing', 'approved'])) {
            return response()->json(['message' => 'Withdrawal cannot be rejected.'], 422);
        }

        $this->walletService->release(
            $withdrawal->user_id,
            $withdrawal->amount,
            'WDL-'.$withdrawal->id
        );

        $withdrawal->update([
            'status' => 'rejected',
            'remark' => $request->input('remark'),
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        $this->audit->log('withdrawal_rejected', $withdrawal);

        return response()->json(['message' => 'Withdrawal rejected and funds released.', 'withdrawal' => $withdrawal]);
    }
}
