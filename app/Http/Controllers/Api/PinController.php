<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CvTier;
use App\Services\AuditService;
use App\Services\PinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PinController extends Controller
{
    public function __construct(private PinService $pinService, private AuditService $audit) {}

    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cv_tier_id' => ['required', 'exists:cv_tiers,id'],
            'count' => ['required', 'integer', 'min:1', 'max:500'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $pins = $this->pinService->generate(
            $request->user()->id,
            $validated['cv_tier_id'],
            $validated['count'],
            $validated['expires_at'] ?? null
        );

        $this->audit->log('generated_pins', CvTier::find($validated['cv_tier_id']),
            null, ['count' => $validated['count']]);

        return response()->json([
            'message' => count($pins).' PIN(s) generated.',
            'pins' => $pins,
        ], 201);
    }

    public function assign(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pin_id' => ['required', 'exists:pins,id'],
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $pin = $this->pinService->assign($validated['pin_id'], $validated['user_id']);

        return response()->json([
            'message' => 'PIN assigned successfully.',
            'pin' => $pin->load('tier', 'assignedUser:id,name,unique_id'),
        ]);
    }

    public function available(Request $request): JsonResponse
    {
        return response()->json([
            'pins' => $this->pinService->available($request->integer('cv_tier_id', null)),
        ]);
    }

    public function use(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'cv_tier_id' => ['nullable', 'exists:cv_tiers,id'],
        ]);

        try {
            $pin = $this->pinService->validate(
                $validated['code'],
                $request->user()->id,
                $validated['cv_tier_id'] ?? null
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'PIN is valid.',
            'pin' => $pin->load('tier'),
        ]);
    }
}
