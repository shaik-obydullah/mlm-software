@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Welcome back, {{ auth()->user()->name }}!</h2>
            <p class="text-sm text-slate-500">Here's an overview of your MLM account.</p>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card label="Wallet Balance" value="{{ money($stats['balance']) }}" icon="wallet" gradient="emerald" />
            <x-stat-card label="Total Earned" value="{{ money($stats['total_earned']) }}" icon="chart" gradient="brand" />
            <x-stat-card label="Active CV" value="{{ number_format($stats['active_cv']) }} CV" icon="cv" gradient="amber" />
            <x-stat-card label="Downline" value="{{ $stats['downline'] }} members" icon="users" gradient="sky" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                        <h3 class="text-sm font-semibold text-slate-900">Binary Tree Balance</h3>
                        <a href="{{ route('web.tree') }}" class="text-xs text-brand-600 hover:text-brand-700 font-medium">View tree →</a>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="rounded-lg bg-gradient-to-br from-emerald-500 to-emerald-600 text-white p-4 shadow-md shadow-emerald-500/20">
                                <div class="text-xs text-emerald-50 font-medium">Left Leg</div>
                                <div class="text-xl font-bold mt-1">{{ number_format($volumes['left']) }} CV</div>
                            </div>
                            <div class="rounded-lg bg-gradient-to-br from-sky-500 to-sky-600 text-white p-4 shadow-md shadow-sky-500/20">
                                <div class="text-xs text-sky-50 font-medium">Right Leg</div>
                                <div class="text-xl font-bold mt-1">{{ number_format($volumes['right']) }} CV</div>
                            </div>
                        </div>
                        <div class="mt-3 text-xs text-slate-500">
                            Matched volume: <span class="font-semibold text-slate-700">{{ number_format(min($volumes['left'], $volumes['right'])) }} CV</span>
                            · Estimated matching bonus: <span class="font-semibold text-emerald-600">{{ money(min($volumes['left'], $volumes['right']) * 0.05) }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-slate-900">Recent Commissions</h3>
                        <a href="{{ route('web.commissions') }}" class="text-xs text-brand-600 hover:text-brand-700 font-medium">All →</a>
                    </div>
                    @if($recentCommissions->isEmpty())
                        <p class="text-sm text-slate-500">No commissions yet. Activate a CV package to start earning.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-xs text-slate-500 border-b border-slate-200">
                                        <th class="py-2">Date</th>
                                        <th class="py-2">Type</th>
                                        <th class="py-2">From</th>
                                        <th class="py-2 text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentCommissions as $c)
                                        <tr class="border-b border-slate-100">
                                            <td class="py-2 text-slate-600">{{ $c->created_at->format('d M Y') }}</td>
                                            <td class="py-2"><span class="badge {{ $c->type }}">{{ ucfirst($c->type) }}</span></td>
                                            <td class="py-2 text-slate-600">{{ $c->fromUser?->unique_id ?? '—' }}</td>
                                            <td class="py-2 text-right font-medium text-emerald-600">+{{ money($c->amount) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                @if(!empty($stats['platform']))
                    <div class="bg-white rounded-xl border border-slate-200 p-5">
                        <h3 class="text-sm font-semibold text-slate-900 mb-4">Platform Overview</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <x-stat-card label="Total Members" value="{{ $stats['platform']['members'] }}" />
                            <x-stat-card label="Active Members" value="{{ $stats['platform']['active_members'] }}" />
                            <x-stat-card label="Total CV Sold" value="{{ number_format($stats['platform']['total_cv']) }} CV" />
                            <x-stat-card label="Pending Withdrawals" value="{{ $stats['platform']['pending_withdrawals'] }}" />
                            <x-stat-card label="Open Tickets" value="{{ $stats['platform']['open_tickets'] }}" />
                            <x-stat-card label="Pending KYC" value="{{ $stats['platform']['pending_kyc'] }}" />
                        </div>
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Monthly Earnings</h3>
                    @php
                        $chartMax = max((float) $monthly->max(), 1);
                        $monthLabels = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        $chartColors = [
                            'from-rose-500 to-rose-400',
                            'from-amber-500 to-amber-400',
                            'from-emerald-500 to-emerald-400',
                            'from-sky-500 to-sky-400',
                            'from-violet-500 to-violet-400',
                            'from-brand-500 to-brand-400',
                        ];
                    @endphp
                    @if($monthly->isEmpty())
                        <div class="py-8 text-center">
                            <p class="text-sm text-slate-500">No earnings yet. Activate a CV package to start earning.</p>
                        </div>
                    @else
                        <div class="flex items-end gap-2 h-44">
                            @foreach($monthly as $i => $total)
                                <div class="flex-1 flex flex-col items-center gap-1 group"
                                     title="{{ $i }}: {{ money($total) }}">
                                    <span class="text-[10px] font-semibold text-slate-600 opacity-0 group-hover:opacity-100 transition-opacity">{{ money($total) }}</span>
                                    <div class="w-full rounded-t-md bg-gradient-to-t shadow-md {{ $chartColors[$loop->index % count($chartColors)] }} group-hover:opacity-80 transition-opacity"
                                         style="height: {{ max(($total / $chartMax) * 132, 4) }}px"></div>
                                    <span class="text-[10px] text-slate-500">{{ $monthLabels[(int) substr($i, 5)] ?? substr($i, 5) }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                            <span>Last 6 months</span>
                            <span class="font-semibold text-brand-600">Total: {{ money($monthly->sum()) }}</span>
                        </div>
                    @endif
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Recent Activity</h3>
                    @if($recentTransactions->isEmpty())
                        <p class="text-sm text-slate-500">No transactions yet.</p>
                    @else
                        <div class="space-y-2.5">
                            @foreach($recentTransactions as $t)
                                @php
                                    $activityColors = [
                                        'commission' => ['bg-emerald-100 text-emerald-700', 'bg-emerald-500'],
                                        'withdrawal' => ['bg-sky-100 text-sky-700', 'bg-sky-500'],
                                        'interest' => ['bg-amber-100 text-amber-700', 'bg-amber-500'],
                                        'bonus' => ['bg-violet-100 text-violet-700', 'bg-violet-500'],
                                        'purchase' => ['bg-rose-100 text-rose-700', 'bg-rose-500'],
                                        'adjustment' => ['bg-slate-100 text-slate-600', 'bg-slate-400'],
                                    ];
                                    $act = $activityColors[$t->category] ?? $activityColors['adjustment'];
                                @endphp
                                <div class="flex items-center justify-between text-sm p-2.5 rounded-lg bg-slate-50/70 border border-slate-100">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="w-7 h-7 rounded-lg {{ $act[0] }} flex items-center justify-center text-[10px] font-bold uppercase shrink-0">{{ substr($t->category, 0, 2) }}</span>
                                        <span class="text-slate-600 truncate">{{ ucfirst($t->category) }}</span>
                                    </div>
                                    <span class="font-medium {{ $t->type === 'credit' ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $t->type === 'credit' ? '+' : '−' }}{{ money($t->amount) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
