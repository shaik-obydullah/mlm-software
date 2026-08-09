@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Welcome back, {{ auth()->user()->name }}!</h2>
            <p class="text-sm text-slate-500">Here's an overview of your MLM account.</p>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card label="Wallet Balance" value="{{ money($stats['balance']) }}" icon="wallet" color="emerald" />
            <x-stat-card label="Total Earned" value="{{ money($stats['total_earned']) }}" icon="chart" color="brand" />
            <x-stat-card label="Active CV" value="{{ number_format($stats['active_cv']) }} CV" icon="cv" color="amber" />
            <x-stat-card label="Downline" value="{{ $stats['downline'] }} members" icon="users" color="sky" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-slate-900">Binary Tree Balance</h3>
                        <a href="{{ route('web.tree') }}" class="text-xs text-brand-600 hover:text-brand-700 font-medium">View tree →</a>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-4">
                            <div class="text-xs text-emerald-700 font-medium">Left Leg</div>
                            <div class="text-xl font-bold text-emerald-800 mt-1">{{ number_format($volumes['left']) }} CV</div>
                        </div>
                        <div class="rounded-lg bg-sky-50 border border-sky-200 p-4">
                            <div class="text-xs text-sky-700 font-medium">Right Leg</div>
                            <div class="text-xl font-bold text-sky-800 mt-1">{{ number_format($volumes['right']) }} CV</div>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-slate-500">
                        Matched volume: <span class="font-semibold text-slate-700">{{ number_format(min($volumes['left'], $volumes['right'])) }} CV</span>
                        · Estimated matching bonus: <span class="font-semibold text-emerald-600">{{ money(min($volumes['left'], $volumes['right']) * 0.05) }}</span>
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
                    @if($monthly->isEmpty())
                        <p class="text-sm text-slate-500">No earnings yet.</p>
                    @else
                        <div class="flex items-end gap-2 h-40">
                            @foreach($monthly as $month => $total)
                                <div class="flex-1 flex flex-col items-center gap-1" title="{{ $month }}: {{ money($total) }}">
                                    <div class="w-full rounded-t bg-brand-500 hover:bg-brand-600 transition-colors"
                                         style="height: {{ ($total / $monthly->max()) * 140 }}px"></div>
                                    <span class="text-[10px] text-slate-500">{{ substr($month, 5) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Recent Activity</h3>
                    @if($recentTransactions->isEmpty())
                        <p class="text-sm text-slate-500">No transactions yet.</p>
                    @else
                        <div class="space-y-3">
                            @foreach($recentTransactions as $t)
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-2 h-2 rounded-full shrink-0 {{ $t->type === 'credit' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
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

    <style>
        .badge {
            @apply inline-block px-2 py-0.5 rounded-full text-[11px] font-medium;
        }
        .badge.sponsor { @apply bg-emerald-50 text-emerald-700; }
        .badge.matching { @apply bg-sky-50 text-sky-700; }
        .badge.level { @apply bg-amber-50 text-amber-700; }
        .badge.royalty { @apply bg-violet-50 text-violet-700; }
    </style>
@endsection
