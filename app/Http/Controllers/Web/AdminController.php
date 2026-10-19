<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CvTier;
use App\Models\KycDocument;
use App\Models\Pin;
use App\Models\Setting;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Services\AuditService;
use App\Services\PinService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(
        private PinService $pinService,
        private WalletService $walletService,
        private AuditService $audit
    ) {}

    public function users(Request $request): View
    {
        $users = User::with('sponsor:id,name,unique_id')
            ->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('unique_id', 'like', "%{$request->search}%");
            }))
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.users', compact('users'));
    }

    public function updateUser(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive,suspended,pending'],
            'role' => ['sometimes', 'in:admin,agent,member'],
        ]);

        $user = User::findOrFail($id);

        $old = ['status' => $user->status];
        $user->update($validated);

        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        $this->audit->log('status_changed', $user, $old, $validated);

        return back()->with('success', "{$user->name} updated.");
    }

    public function pins(Request $request): View
    {
        $tiers = CvTier::all();
        $pins = Pin::with(['tier', 'assignedUser:id,name,unique_id'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q) => $q->where('code', 'like', "%{$request->search}%"))
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.pins', compact('tiers', 'pins'));
    }

    public function generatePins(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cv_tier_id' => ['required', 'exists:cv_tiers,id'],
            'count' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $this->pinService->generate(auth()->id(), $validated['cv_tier_id'], $validated['count']);

        return back()->with('success', "{$validated['count']} PIN(s) generated.");
    }

    public function assignPin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pin_id' => ['required', 'exists:pins,id'],
            'user_id' => ['required', 'exists:users,id'],
        ]);

        try {
            $this->pinService->assign($validated['pin_id'], $validated['user_id']);

            return back()->with('success', 'PIN assigned.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function withdrawals(Request $request): View
    {
        $withdrawals = WithdrawalRequest::with('user:id,name,unique_id')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.withdrawals', compact('withdrawals'));
    }

    public function approveWithdrawal(int $id): RedirectResponse
    {
        $withdrawal = WithdrawalRequest::findOrFail($id);

        if (! in_array($withdrawal->status, ['pending', 'processing'])) {
            return back()->with('error', 'Cannot approve this withdrawal.');
        }

        $withdrawal->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $this->walletService->markWithdrawn($withdrawal->user_id, $withdrawal->amount);

        $this->audit->log('withdrawal_approved', $withdrawal);

        return back()->with('success', 'Withdrawal approved.');
    }

    public function rejectWithdrawal(Request $request, int $id): RedirectResponse
    {
        $withdrawal = WithdrawalRequest::findOrFail($id);

        $this->walletService->release($withdrawal->user_id, $withdrawal->amount);

        $withdrawal->update([
            'status' => 'rejected',
            'remark' => $request->input('remark'),
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $this->audit->log('withdrawal_rejected', $withdrawal);

        return back()->with('success', 'Withdrawal rejected, funds released.');
    }

    public function kyc(): View
    {
        $documents = KycDocument::with('user:id,name,unique_id,email')
            ->where('status', 'pending')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.kyc', compact('documents'));
    }

    public function reviewKyc(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $document = KycDocument::findOrFail($id);

        $document->update([
            'status' => $validated['status'],
            'rejection_reason' => $validated['rejection_reason'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $user = $document->user;

        if ($validated['status'] === 'approved') {
            if ($user->kycDocuments()->where('status', '!=', 'approved')->doesntExist()) {
                $user->update(['kyc_status' => 'approved']);
            }
        } else {
            $user->update(['kyc_status' => 'rejected']);
        }

        $this->audit->log('kyc_reviewed', $document);

        return back()->with('success', 'KYC document reviewed.');
    }

    public function settings(): View
    {
        $settings = Setting::orderBy('group')->orderBy('key')->get()->groupBy('group');

        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string'],
        ]);

        foreach ($validated['settings'] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Settings saved.');
    }
}
