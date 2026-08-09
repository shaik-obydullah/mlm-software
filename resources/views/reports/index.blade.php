@extends('layouts.app')

@section('title', 'Reports')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Reports &amp; Statements</h2>
                <p class="text-sm text-slate-500">Export your earnings, transactions, withdrawals and downline.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('web.reports.export', 'commissions') }}" class="btn-secondary">CSV</a>
                <a href="{{ route('web.reports.export', ['type' => 'commissions', 'format' => 'pdf']) }}" class="btn-secondary">PDF</a>
            </div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card label="Total Earned" value="{{ money($wallet?->total_earned ?? 0) }}" color="brand" />
            <x-stat-card label="Withdrawn" value="{{ money($wallet?->total_withdrawn ?? 0) }}" color="sky" />
            <x-stat-card label="Available" value="{{ money($wallet?->balance ?? 0) }}" color="emerald" />
            <x-stat-card label="Downline" value="{{ $downline->count() }} members" color="amber" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="text-sm font-semibold text-slate-900 mb-4">Downline by Level</h3>
                @if($byLevel->isEmpty())
                    <p class="text-sm text-slate-500">No downline yet.</p>
                @else
                    <div class="space-y-3">
                        @foreach($byLevel as $level => $count)
                            <div>
                                <div class="flex justify-between text-xs text-slate-600 mb-1">
                                    <span>Level {{ $level }}</span>
                                    <span class="font-medium">{{ $count }} members</span>
                                </div>
                                <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full bg-brand-500" style="width: {{ ($count / $byLevel->max()) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="text-sm font-semibold text-slate-900 mb-4">Export Reports</h3>
                <div class="space-y-3">
                    @foreach(['commissions' => 'Commission history', 'transactions' => 'Wallet transactions', 'withdrawals' => 'Withdrawal history', 'downline' => 'Downline members'] as $type => $label)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 px-4 py-3">
                            <div>
                                <div class="text-sm font-medium text-slate-800">{{ $label }}</div>
                                <div class="text-xs text-slate-500">Download as CSV or PDF</div>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('web.reports.export', $type) }}" class="btn-secondary !py-1.5">CSV</a>
                                <a href="{{ route('web.reports.export', ['type' => $type, 'format' => 'pdf']) }}" class="btn-secondary !py-1.5">PDF</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <style>
        .btn-secondary {
            @apply inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors;
        }
    </style>
@endsection
