<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Services\MlmTreeService;
use Illuminate\View\View;

class CommissionController extends Controller
{
    public function __construct(private MlmTreeService $treeService) {}

    public function index(): View
    {
        $user = auth()->user();

        $commissions = Commission::where('user_id', $user->id)
            ->with('fromUser:id,name,unique_id', 'purchase.tier')
            ->orderByDesc('id')
            ->paginate(15);

        $totals = [
            'sponsor' => (float) Commission::where('user_id', $user->id)->where('type', 'sponsor')->sum('amount'),
            'matching' => (float) Commission::where('user_id', $user->id)->where('type', 'matching')->sum('amount'),
            'level' => (float) Commission::where('user_id', $user->id)->where('type', 'level')->sum('amount'),
            'all' => (float) Commission::where('user_id', $user->id)->sum('amount'),
        ];

        $volumes = $this->treeService->legVolumes($user->id);

        return view('commissions.index', compact('commissions', 'totals', 'volumes'));
    }
}
