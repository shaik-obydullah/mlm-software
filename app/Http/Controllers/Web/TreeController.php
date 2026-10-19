<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\MlmTreeService;
use Illuminate\View\View;

class TreeController extends Controller
{
    public function __construct(private MlmTreeService $treeService) {}

    public function index(): View
    {
        $user = auth()->user();
        $node = $user->treeNode;
        $tree = $node ? $this->treeService->treeArray($node) : null;
        $volumes = $this->treeService->legVolumes($user->id);
        $downline = $this->treeService->allDownline($user->id)->groupBy('level');

        return view('mlm.tree', compact('tree', 'volumes', 'downline'));
    }
}
