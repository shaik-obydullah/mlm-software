<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\WithdrawalRequest;
use App\Services\MlmTreeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private MlmTreeService $treeService) {}

    public function index(): View
    {
        $user = auth()->user();
        $wallet = $user->wallet;

        $downline = $this->treeService->allDownline($user->id);
        $byLevel = $downline->groupBy('level')->map->count();

        return view('reports.index', compact('wallet', 'downline', 'byLevel'));
    }

    public function export(string $type): Response
    {
        $user = auth()->user();

        $rows = match ($type) {
            'commissions' => Commission::where('user_id', $user->id)->get()->map(fn ($c) => [
                'Date' => $c->created_at->format('Y-m-d H:i'),
                'Type' => ucfirst($c->type),
                'Level' => $c->level ?? '-',
                'Amount' => $c->amount,
                'Status' => $c->status,
            ]),
            'transactions' => ($user->wallet?->transactions ?? collect())->map(fn ($t) => [
                'Date' => $t->created_at->format('Y-m-d H:i'),
                'Type' => ucfirst($t->type),
                'Category' => ucfirst($t->category),
                'Amount' => $t->amount,
                'Balance' => $t->balance_after,
            ]),
            'withdrawals' => WithdrawalRequest::where('user_id', $user->id)->get()->map(fn ($w) => [
                'Date' => $w->created_at->format('Y-m-d'),
                'Amount' => $w->amount,
                'Fee' => $w->fee,
                'Net' => $w->net_amount,
                'Status' => $w->status,
            ]),
            'downline' => $downline = $this->treeService->allDownline($user->id)->map(fn ($node) => [
                'Level' => $node->level,
                'Position' => ucfirst($node->position ?? '-'),
                'Member' => $node->user?->unique_id ?? '-',
                'Name' => $node->user?->name ?? '-',
                'Status' => $node->user?->status ?? '-',
            ]),
            default => abort(404),
        };

        $defaultHeaders = [
            'commissions' => ['Date', 'Type', 'Level', 'Amount', 'Status'],
            'transactions' => ['Date', 'Type', 'Category', 'Amount', 'Balance'],
            'withdrawals' => ['Date', 'Amount', 'Fee', 'Net', 'Status'],
            'downline' => ['Level', 'Position', 'Member', 'Name', 'Status'],
        ];

        $title = ucfirst($type).' Report';
        $headers = $rows->first() ? array_keys($rows->first()) : ($defaultHeaders[$type] ?? []);

        if (request('format') === 'pdf') {
            $pdf = Pdf::loadView('exports.table', [
                'title' => $title,
                'headers' => $headers,
                'rows' => $rows->all(),
            ]);

            return $pdf->download($type.'-'.date('Y-m-d').'.pdf');
        }

        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, $headers);
        foreach ($rows as $row) {
            fputcsv($csv, array_values($row));
        }
        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$type.'-'.date('Y-m-d').'.csv"',
        ]);
    }
}
