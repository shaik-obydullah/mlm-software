<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CvActivation;
use App\Models\CvPurchase;
use App\Models\CvTier;
use App\Services\CvService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CvController extends Controller
{
    public function __construct(private CvService $cvService) {}

    public function tiers(): JsonResponse
    {
        return response()->json([
            'tiers' => CvTier::where('status', 'active')->orderBy('amount')->get(),
        ]);
    }

    public function purchase(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tier_id' => ['required', 'exists:cv_tiers,id'],
            'payment_method' => ['required', 'in:bank,wallet,pin'],
            'transaction_id' => ['nullable', 'string', 'max:100'],
        ]);

        $purchase = $this->cvService->purchase(
            $request->user()->id,
            $validated['tier_id'],
            $validated['payment_method'],
            $validated['transaction_id'] ?? null
        );

        return response()->json([
            'message' => $validated['payment_method'] === 'wallet'
                ? 'CV purchased and paid from wallet. Awaiting PIN activation.'
                : 'CV purchase created. Please complete payment to activate.',
            'purchase' => $purchase->load('tier'),
        ], 201);
    }

    public function activate(Request $request, int $purchase): JsonResponse
    {
        $validated = $request->validate([
            'pin_code' => ['nullable', 'string', 'max:50'],
        ]);

        $purchase = $this->cvService->activate(
            $request->user()->id,
            $purchase,
            $validated['pin_code'] ?? null
        );

        return response()->json([
            'message' => 'CV activated successfully. Commissions are being processed.',
            'purchase' => $purchase->load('tier', 'activation'),
        ]);
    }

    public function markPaid(Request $request, int $purchase): JsonResponse
    {
        $purchase = $this->cvService->markPaid($purchase, $request->user()->id);

        return response()->json([
            'message' => 'Purchase marked as paid.',
            'purchase' => $purchase->load('tier', 'user:id,name,unique_id'),
        ]);
    }

    public function purchases(int $userId): JsonResponse
    {
        $this->authorizeUser($userId);

        $purchases = CvPurchase::where('user_id', $userId)
            ->with(['tier', 'activation'])
            ->orderByDesc('id')
            ->paginate(15);

        return response()->json($purchases);
    }

    public function activations(int $userId): JsonResponse
    {
        $this->authorizeUser($userId);

        $activations = CvActivation::where('user_id', $userId)
            ->with(['purchase.tier'])
            ->orderByDesc('id')
            ->paginate(15);

        return response()->json($activations);
    }

    private function authorizeUser(int $userId): void
    {
        $current = request()->user();

        if (! $current->isAdmin() && $current->id !== $userId) {
            abort(403, 'You can only access your own data.');
        }
    }
}
