<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use App\Models\User;
use App\Services\AuditService;
use App\Services\MlmTreeService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:30'],
            'sponsor_unique_id' => ['nullable', 'string', 'max:30', 'exists:users,unique_id'],
        ]);

        $sponsor = null;

        if (isset($validated['sponsor_unique_id'])) {
            $sponsor = User::where('unique_id', $validated['sponsor_unique_id'])->first();
        }

        $user = User::create([
            'unique_id' => $this->generateUniqueId(),
            'sponsor_id' => $sponsor?->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => 'member',
            'status' => 'pending',
            'kyc_status' => 'not_submitted',
        ]);

        $user->assignRole('member');

        if ($sponsor) {
            Sponsor::create([
                'sponsor_id' => $sponsor->id,
                'sponsee_id' => $user->id,
            ]);
        }

        app(MlmTreeService::class)->place($user, $sponsor?->id ?? $user->id);

        app(WalletService::class)->ensureWallet($user->id);

        $this->audit->log('registered', $user, null, $user->toArray());

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful. Please complete KYC to activate your account.',
            'token' => $token,
            'user' => $user->load('sponsor', 'wallet', 'treeNode'),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $user = Auth::user();

        if (! $user->isActive()) {
            return response()->json(['message' => 'Your account is not active.'], 403);
        }

        $this->audit->log('login', $user);

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->load('sponsor', 'wallet', 'treeNode'),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user()->load(
            'sponsor',
            'wallet',
            'treeNode',
            'referrals:id,name,unique_id,status,created_at',
            'kycDocuments'
        );

        return response()->json(['user' => $user]);
    }

    private function generateUniqueId(): string
    {
        do {
            $id = 'MLM'.strtoupper(Str::random(6));
        } while (User::where('unique_id', $id)->exists());

        return $id;
    }
}
