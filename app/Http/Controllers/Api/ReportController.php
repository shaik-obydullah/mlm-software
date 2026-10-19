<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\CvPurchase;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Services\MlmTreeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function earnings(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = [
            'total_earned' => (float) $user->wallet?->total_earned ?? 0,
            'total_withdrawn' => (float) $user->wallet?->total_withdrawn ?? 0,
            'available_balance' => (float) $user->wallet?->balance ?? 0,
            'pending_withdrawals' => (float) $user->wallet?->locked_amount ?? 0,
            'monthly' => $this->monthlyEarnings($user->id),
        ];

        if ($request->user()->isAdmin()) {
            $data['platform'] = [
                'total_commissions' => (float) Commission::sum('amount'),
                'total_cv_sold' => (int) CvPurchase::where('status', 'activated')->sum('amount'),
                'active_members' => User::where('status', 'active')->count(),
                'total_withdrawn' => (float) WithdrawalRequest::where('status', 'completed')->sum('net_amount'),
            ];
        }

        return response()->json($data);
    }

    public function downline(Request $request): JsonResponse
    {
        $service = app(MlmTreeService::class);

        $all = $service->allDownline($request->user()->id);
        $volumes = $service->legVolumes($request->user()->id);

        $byLevel = $all->groupBy('level')->map(fn ($group) => $group->count());

        return response()->json([
            'total' => $all->count(),
            'left_volume' => $volumes['left'],
            'right_volume' => $volumes['right'],
            'by_level' => $byLevel,
            'downline' => $all->load('user:id,name,unique_id,status,created_at'),
        ]);
    }

    public function commissions(Request $request): JsonResponse
    {
        $query = Commission::with('user:id,name,unique_id', 'fromUser:id,name,unique_id')->orderByDesc('id');

        if (! $request->user()->isAdmin()) {
            $query->where('user_id', $request->user()->id);
        }

        return response()->json($query->paginate(25));
    }

    public function withdrawals(Request $request): JsonResponse
    {
        $query = WithdrawalRequest::with('user:id,name,unique_id')->orderByDesc('id');

        if (! $request->user()->isAdmin()) {
            $query->where('user_id', $request->user()->id);
        }

        return response()->json($query->paginate(25));
    }

    public function export(Request $request, string $type): JsonResponse|StreamedResponse
    {
        $user = $request->user();

        $rows = match ($type) {
            'commissions' => $this->commissionRows($user),
            'transactions' => $this->transactionRows($user),
            'withdrawals' => $this->withdrawalRows($user),
            'downline' => $this->downlineRows($user),
            default => abort(404, 'Unknown export type.'),
        };

        $filename = $type.'-'.date('Y-m-d');

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('exports.table', [
                'title' => ucfirst($type).' Report',
                'headers' => array_keys($rows[0] ?? []),
                'rows' => $rows,
            ]);

            return $pdf->download($filename.'.pdf');
        }

        $headers = array_keys($rows[0] ?? []);

        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, $headers);
        foreach ($rows as $row) {
            fputcsv($csv, array_values($row));
        }
        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        return Response::streamDownload(fn () => print ($content), $filename.'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function monthlyEarnings(int $userId): array
    {
        $transactions = Transaction::whereHas('wallet', fn ($q) => $q->where('user_id', $userId))
            ->where('type', 'credit')
            ->where('created_at', '>=', now()->subMonths(12))
            ->get(['amount', 'created_at']);

        return $transactions
            ->groupBy(fn ($t) => $t->created_at->format('Y-m'))
            ->map(fn ($group) => (float) $group->sum('amount'))
            ->sortKeys()
            ->all();
    }

    private function commissionRows($user): array
    {
        $query = Commission::with('fromUser')->orderByDesc('id');
        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        return $query->get()->map(fn ($c) => [
            'Date' => $c->created_at->format('Y-m-d H:i'),
            'Type' => ucfirst($c->type),
            'Level' => $c->level ?? '-',
            'From' => $c->fromUser?->unique_id ?? '-',
            'CV Volume' => $c->cv_volume,
            'Amount' => $c->amount,
            'Status' => $c->status,
        ])->all();
    }

    private function transactionRows($user): array
    {
        $walletId = $user->wallet?->id;

        if (! $user->isAdmin() && ! $walletId) {
            return [];
        }

        $query = Transaction::with('wallet.user')->orderByDesc('id');
        if (! $user->isAdmin()) {
            $query->where('wallet_id', $walletId);
        }

        return $query->get()->map(fn ($t) => [
            'Date' => $t->created_at->format('Y-m-d H:i'),
            'Member' => $t->wallet?->user?->unique_id ?? '-',
            'Type' => ucfirst($t->type),
            'Category' => ucfirst($t->category),
            'Amount' => $t->amount,
            'Balance' => $t->balance_after,
            'Reference' => $t->reference ?? '-',
        ])->all();
    }

    private function withdrawalRows($user): array
    {
        $query = WithdrawalRequest::with('user')->orderByDesc('id');
        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        return $query->get()->map(fn ($w) => [
            'Date' => $w->created_at->format('Y-m-d H:i'),
            'Member' => $w->user?->unique_id ?? '-',
            'Amount' => $w->amount,
            'Fee' => $w->fee,
            'Net' => $w->net_amount,
            'Bank' => $w->bank_name,
            'Account' => $w->account_number,
            'Status' => $w->status,
        ])->all();
    }

    private function downlineRows($user): array
    {
        $service = app(MlmTreeService::class);

        return $service->allDownline($user->id)
            ->map(fn ($node) => [
                'Level' => $node->level,
                'Position' => ucfirst($node->position ?? '-'),
                'Member' => $node->user?->unique_id ?? '-',
                'Name' => $node->user?->name ?? '-',
                'Status' => $node->user?->status ?? '-',
                'Joined' => $node->user?->created_at?->format('Y-m-d') ?? '-',
            ])->all();
    }
}
