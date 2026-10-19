<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use App\Models\User;
use App\Services\MlmTreeService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials.']);
        }

        $user = Auth::user();

        if (! $user->isActive()) {
            Auth::logout();

            return back()->withErrors(['email' => 'Your account is not active yet.']);
        }

        return redirect()->route('dashboard');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'sponsor_unique_id' => ['nullable', 'string', 'max:30', 'exists:users,unique_id'],
        ]);

        $sponsor = isset($validated['sponsor_unique_id'])
            ? User::where('unique_id', $validated['sponsor_unique_id'])->first()
            : null;

        $user = User::create([
            'unique_id' => 'MLM'.strtoupper(Str::random(6)),
            'sponsor_id' => $sponsor?->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
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

        $treeService = app(MlmTreeService::class);
        $treeService->place($user, $sponsor?->id ?? 1);

        app(WalletService::class)->ensureWallet($user->id);

        Auth::login($user);

        return redirect()->route('web.profile')->with('success', 'Welcome! Please complete your KYC and purchase a CV package to get started.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
