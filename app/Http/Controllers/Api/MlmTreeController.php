<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MlmTreeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MlmTreeController extends Controller
{
    public function __construct(private MlmTreeService $treeService) {}

    public function tree(Request $request, int $userId): JsonResponse
    {
        $user = User::findOrFail($userId);

        if (! $request->user()->isAdmin() && $request->user()->id !== $userId) {
            abort(403);
        }

        $node = $user->treeNode;

        if (! $node) {
            return response()->json(['tree' => null]);
        }

        $maxDepth = $request->integer('depth', 6);
        $node->load('user');

        return response()->json([
            'tree' => $this->treeService->treeArray($node),
            'depth' => $maxDepth,
        ]);
    }

    public function sponsor(int $userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $sponsor = $user->sponsor;

        return response()->json([
            'sponsor' => $sponsor ? $sponsor->only(['id', 'name', 'unique_id', 'phone']) : null,
        ]);
    }

    public function downline(Request $request, int $userId): JsonResponse
    {
        $direct = $this->treeService->directDownline($userId);
        $all = $this->treeService->allDownline($userId);
        $volumes = $this->treeService->legVolumes($userId);

        return response()->json([
            'direct_count' => $direct->count(),
            'total_downline' => $all->count(),
            'left_volume' => $volumes['left'],
            'right_volume' => $volumes['right'],
            'matched_volume' => min($volumes['left'], $volumes['right']),
            'direct' => $direct,
            'downline' => $all,
        ]);
    }

    public function place(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'sponsor_id' => ['required', 'exists:users,id'],
            'position' => ['nullable', 'in:left,right'],
        ]);

        $user = User::findOrFail($validated['user_id']);

        if (! $request->user()->isAdmin()) {
            abort(403, 'Only admins can manually place users.');
        }

        $node = $this->treeService->place($user, $validated['sponsor_id'], $validated['position'] ?? null);

        return response()->json([
            'message' => 'User placed in the binary tree.',
            'node' => $node->load('parent.user'),
        ]);
    }
}
