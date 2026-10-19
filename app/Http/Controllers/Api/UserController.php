<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use App\Services\MlmTreeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->with('sponsor:id,name,unique_id')
            ->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('unique_id', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%");
            }))
            ->when($request->role, fn ($q) => $q->where('role', $request->role))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15));

        return response()->json($users);
    }

    public function show(int $id): JsonResponse
    {
        $user = User::with([
            'sponsor:id,name,unique_id',
            'referrals:id,name,unique_id,status,created_at',
            'wallet',
            'treeNode',
            'kycDocuments',
        ])->findOrFail($id);

        $this->authorizeUser($user);

        return response()->json(['user' => $user]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $this->authorizeUser($user);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'nominee_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'nominee_relation' => ['sometimes', 'nullable', 'string', 'max:50'],
            'nominee_phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'bank_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'bank_account_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'bank_account_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            'bank_ifsc' => ['sometimes', 'nullable', 'string', 'max:30'],
            'status' => ['sometimes', 'in:active,inactive,suspended,pending'],
        ]);

        if (isset($validated['status']) && ! $request->user()->can('manage users')) {
            unset($validated['status']);
        }

        $old = $user->only(array_keys($validated));

        $user->update($validated);

        if (isset($validated['status'])) {
            $user->syncRoles([$user->role]);
        }

        $this->audit->log('updated', $user, $old, $user->only(array_keys($validated)));

        return response()->json(['message' => 'User updated.', 'user' => $user->fresh()]);
    }

    public function downline(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $this->authorizeUser($user);

        $downline = app(MlmTreeService::class)->allDownline($id)
            ->map(fn ($node) => $node->user);

        return response()->json([
            'total' => $downline->count(),
            'downline' => $downline,
        ]);
    }

    public function commissions(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $this->authorizeUser($user);

        $commissions = $user->commissions()
            ->with('fromUser:id,name,unique_id')
            ->orderByDesc('id')
            ->paginate(15);

        return response()->json($commissions);
    }

    private function authorizeUser(User $user): void
    {
        $current = request()->user();

        if (! $current->isAdmin() && $current->id !== $user->id) {
            abort(403, 'You can only access your own data.');
        }
    }
}
