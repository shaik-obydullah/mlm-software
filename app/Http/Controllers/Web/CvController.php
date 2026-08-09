<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CvPurchase;
use App\Models\CvTier;
use App\Services\CvService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CvController extends Controller
{
    public function __construct(private CvService $cvService) {}

    public function index(): View
    {
        $tiers = CvTier::where('status', 'active')->orderBy('amount')->get();
        $purchases = CvPurchase::where('user_id', auth()->id())
            ->with('tier', 'activation')->orderByDesc('id')->paginate(10);

        return view('cv.index', compact('tiers', 'purchases'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tier_id' => ['required', 'exists:cv_tiers,id'],
            'payment_method' => ['required', 'in:bank,wallet,pin'],
        ]);

        try {
            $purchase = $this->cvService->purchase(
                auth()->id(),
                $validated['tier_id'],
                $validated['payment_method']
            );

            $message = $validated['payment_method'] === 'wallet'
                ? 'CV package purchased and paid from wallet. Activate with a PIN.'
                : 'CV package purchased. Complete payment to activate.';

            return back()->with('success', $message);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function activate(Request $request, int $purchase): RedirectResponse
    {
        $validated = $request->validate([
            'pin_code' => ['required', 'string', 'max:50'],
        ]);

        try {
            $this->cvService->activate(auth()->id(), $purchase, $validated['pin_code']);

            return back()->with('success', 'CV activated! Commissions are being credited.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
