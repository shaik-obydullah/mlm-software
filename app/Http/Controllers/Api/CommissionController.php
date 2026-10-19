<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\CvPurchase;
use App\Models\User;
use App\Services\CommissionService;
use App\Services\MlmTreeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function __construct(
        private CommissionService $commissionService,
        private MlmTreeService $treeService
    ) {}

    public function rules(): JsonResponse
    {
        return response()->json([
            'rules' => CommissionRule::active()->orderBy('type')->orderBy('level')->get(),
        ]);
    }

    public function calculate(Request $request, int $userId): JsonResponse
    {
        $user = User::findOrFail($userId);

        if (! $request->user()->isAdmin() && $request->user()->id !== $userId) {
            abort(403);
        }

        $node = $user->treeNode;
        $volumes = $this->treeService->legVolumes($userId);

        $matching = min($volumes['left'], $volumes['right']) * 0.05;

        $levels = [];
        $levelRate = $this->levelRates();

        if ($node) {
            for ($lvl = 1; $lvl <= CommissionService::MAX_LEVELS; $lvl++) {
                $uplineNode = $this->treeService->upline($node, $lvl);
                $levels[$lvl] = $uplineNode ? [
                    'user_id' => $uplineNode->user_id,
                    'name' => $uplineNode->user->name,
                    'rate' => $levelRate[$lvl],
                ] : null;
            }
        }

        return response()->json([
            'user_id' => $userId,
            'leg_volumes' => $volumes,
            'estimated_matching_bonus' => round($matching, 2),
            'level_structure' => $levels,
        ]);
    }

    public function history(int $userId): JsonResponse
    {
        $this->authorizeUser($userId);

        $commissions = Commission::where('user_id', $userId)
            ->with(['fromUser:id,name,unique_id', 'purchase.tier'])
            ->orderByDesc('id')
            ->paginate(15);

        return response()->json($commissions);
    }

    public function process(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'purchase_id' => ['required', 'exists:cv_purchases,id'],
        ]);

        $purchase = CvPurchase::findOrFail($validated['purchase_id']);

        if ($purchase->status !== 'activated') {
            return response()->json(['message' => 'Only activated purchases generate commissions.'], 422);
        }

        $results = $this->commissionService->processForPurchase($purchase);

        return response()->json([
            'message' => 'Commissions processed.',
            'results' => $results,
        ]);
    }

    private function levelRates(): array
    {
        return CommissionRule::active()
            ->where('type', 'level')
            ->pluck('rate', 'level')
            ->map(fn ($rate) => (float) $rate / 100)
            ->all();
    }

    private function authorizeUser(int $userId): void
    {
        $current = request()->user();

        if (! $current->isAdmin() && $current->id !== $userId) {
            abort(403, 'You can only access your own data.');
        }
    }
}
