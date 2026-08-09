@extends('layouts.app')

@section('title', 'Commissions')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Commission Earnings</h2>
            <p class="text-sm text-slate-500">Sponsor, matching and level commissions credited to your wallet.</p>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card label="Sponsor Bonus" value="{{ money($totals['sponsor']) }}" color="emerald" />
            <x-stat-card label="Matching Bonus" value="{{ money($totals['matching']) }}" color="sky" />
            <x-stat-card label="Level Commission" value="{{ money($totals['level']) }}" color="amber" />
            <x-stat-card label="Total" value="{{ money($totals['all']) }}" color="brand" />
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-900 mb-4">Leg Volumes</h3>
            <div class="grid grid-cols-2 gap-4 max-w-lg">
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-center">
                    <div class="text-xs text-emerald-700 font-medium">Left Leg</div>
                    <div class="text-lg font-bold text-emerald-800 mt-1">{{ number_format($volumes['left']) }} CV</div>
                </div>
                <div class="rounded-lg bg-sky-50 border border-sky-200 p-4 text-center">
                    <div class="text-xs text-sky-700 font-medium">Right Leg</div>
                    <div class="text-lg font-bold text-sky-800 mt-1">{{ number_format($volumes['right']) }} CV</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-900 mb-4">Commission History</h3>
            @if($commissions->isEmpty())
                <p class="text-sm text-slate-500">No commissions yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-slate-500 border-b border-slate-200">
                                <th class="py-2">Date</th>
                                <th class="py-2">Type</th>
                                <th class="py-2">Level</th>
                                <th class="py-2">From</th>
                                <th class="py-2">CV Volume</th>
                                <th class="py-2 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($commissions as $c)
                                <tr class="border-b border-slate-100">
                                    <td class="py-2.5 text-slate-600">{{ $c->created_at->format('d M Y H:i') }}</td>
                                    <td class="py-2.5">
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-medium
                                            {{ $c->type === 'sponsor' ? 'bg-emerald-50 text-emerald-700' : ($c->type === 'matching' ? 'bg-sky-50 text-sky-700' : 'bg-amber-50 text-amber-700') }}">
                                            {{ ucfirst($c->type) }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 text-slate-600">{{ $c->level ?? '—' }}</td>
                                    <td class="py-2.5 text-slate-600">{{ $c->fromUser?->unique_id ?? '—' }}</td>
                                    <td class="py-2.5">{{ number_format($c->cv_volume) }} CV</td>
                                    <td class="py-2.5 text-right font-medium text-emerald-600">+{{ money($c->amount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $commissions->links() }}
            @endif
        </div>
    </div>
@endsection
