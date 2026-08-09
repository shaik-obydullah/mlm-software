@extends('layouts.app')

@section('title', 'Wallet')

@section('content')
    <div class="space-y-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card label="Available Balance" value="{{ money($wallet->balance) }}" icon="wallet" color="emerald" />
            <x-stat-card label="Locked (Pending)" value="{{ money($wallet->locked_amount) }}" color="amber" />
            <x-stat-card label="Total Earned" value="{{ money($wallet->total_earned) }}" color="brand" />
            <x-stat-card label="Total Withdrawn" value="{{ money($wallet->total_withdrawn) }}" color="sky" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="text-sm font-semibold text-slate-900 mb-4">Request Withdrawal</h3>
                <form method="POST" action="{{ route('web.wallet.withdraw') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Amount</label>
                        <input type="number" name="amount" min="1" step="0.01" required
                               class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div class="text-xs text-slate-500 space-y-1">
                        <p>Available: <span class="font-medium text-slate-700">{{ money($wallet->balance) }}</span></p>
                        <p>Withdrawal fee: {{ (float) setting('withdrawal_fee_percent', 0) }}%</p>
                        <p>Minimum: {{ money((float) setting('min_withdrawal', 100)) }}</p>
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold py-2.5 transition-colors">
                        Submit request
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Transactions</h3>
                    @if($transactions->isEmpty())
                        <p class="text-sm text-slate-500">No transactions yet.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-xs text-slate-500 border-b border-slate-200">
                                        <th class="py-2">Date</th>
                                        <th class="py-2">Category</th>
                                        <th class="py-2">Reference</th>
                                        <th class="py-2 text-right">Amount</th>
                                        <th class="py-2 text-right">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $t)
                                        <tr class="border-b border-slate-100">
                                            <td class="py-2.5 text-slate-600">{{ $t->created_at->format('d M Y H:i') }}</td>
                                            <td class="py-2.5">
                                                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600 capitalize">{{ $t->category }}</span>
                                            </td>
                                            <td class="py-2.5 text-slate-500 text-xs">{{ $t->reference ?? '—' }}</td>
                                            <td class="py-2.5 text-right font-medium {{ in_array($t->type, ['credit', 'release']) ? 'text-emerald-600' : 'text-rose-600' }}">
                                                {{ in_array($t->type, ['credit', 'release']) ? '+' : '−' }}{{ money($t->amount) }}
                                            </td>
                                            <td class="py-2.5 text-right text-slate-600">{{ money($t->balance_after) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $transactions->links() }}
                    @endif
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Withdrawal Requests</h3>
                    @if($withdrawals->isEmpty())
                        <p class="text-sm text-slate-500">No withdrawal requests.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-xs text-slate-500 border-b border-slate-200">
                                        <th class="py-2">Date</th>
                                        <th class="py-2 text-right">Amount</th>
                                        <th class="py-2 text-right">Fee</th>
                                        <th class="py-2 text-right">Net</th>
                                        <th class="py-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($withdrawals as $w)
                                        <tr class="border-b border-slate-100">
                                            <td class="py-2.5 text-slate-600">{{ $w->created_at->format('d M Y') }}</td>
                                            <td class="py-2.5 text-right">{{ money($w->amount) }}</td>
                                            <td class="py-2.5 text-right text-slate-500">{{ money($w->fee) }}</td>
                                            <td class="py-2.5 text-right font-medium">{{ money($w->net_amount) }}</td>
                                            <td class="py-2.5">
                                                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium
                                                    {{ $w->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : ($w->status === 'rejected' ? 'bg-rose-50 text-rose-700' : ($w->status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-sky-50 text-sky-700')) }}">
                                                    {{ ucfirst($w->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
