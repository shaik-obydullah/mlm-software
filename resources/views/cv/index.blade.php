@extends('layouts.app')

@section('title', 'CV Packages')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">CV Packages</h2>
            <p class="text-sm text-slate-500">Purchase CV to qualify for commissions. Activate with a PIN after payment.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($tiers as $tier)
                <div class="bg-white rounded-xl border border-slate-200 p-5 flex flex-col">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-slate-900">{{ $tier->name }}</h3>
                        <span class="text-[11px] font-medium px-2 py-1 rounded-full bg-amber-50 text-amber-700">{{ number_format($tier->amount) }} CV</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">{{ $tier->description }}</p>
                    <div class="mt-4 flex-1">
                        <div class="text-2xl font-bold text-slate-900">{{ money($tier->price ?? $tier->amount) }}</div>
                        <div class="text-xs text-slate-500 mt-1">≈ {{ $tier->amount }} CV value</div>
                    </div>
                    <form method="POST" action="{{ route('web.cv.purchase') }}" class="mt-4 space-y-2" x-data="{ method: 'bank' }">
                        @csrf
                        <input type="hidden" name="tier_id" value="{{ $tier->id }}">
                        <select name="payment_method" x-model="method" class="w-full rounded-lg border-slate-300 text-sm">
                            <option value="bank">Bank transfer</option>
                            <option value="wallet">Wallet balance</option>
                            <option value="pin">PIN</option>
                        </select>
                        <button type="submit" class="w-full rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold py-2 transition-colors">
                            Purchase
                        </button>
                    </form>
                </div>
            @endforeach
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-900 mb-4">Your Purchases</h3>
            @if($purchases->isEmpty())
                <p class="text-sm text-slate-500">No purchases yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-slate-500 border-b border-slate-200">
                                <th class="py-2">Date</th>
                                <th class="py-2">Package</th>
                                <th class="py-2">CV</th>
                                <th class="py-2">Method</th>
                                <th class="py-2">Status</th>
                                <th class="py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchases as $purchase)
                                <tr class="border-b border-slate-100">
                                    <td class="py-2.5 text-slate-600">{{ $purchase->created_at->format('d M Y') }}</td>
                                    <td class="py-2.5 font-medium">{{ $purchase->tier?->name }}</td>
                                    <td class="py-2.5">{{ number_format($purchase->amount) }} CV</td>
                                    <td class="py-2.5 capitalize text-slate-600">{{ $purchase->payment_method }}</td>
                                    <td class="py-2.5">
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-medium
                                            {{ $purchase->status === 'activated' ? 'bg-emerald-50 text-emerald-700' : ($purchase->status === 'paid' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                            {{ ucfirst($purchase->status) }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 text-right">
                                        @if($purchase->status === 'paid' && !$purchase->activation)
                                            <form method="POST" action="{{ route('web.cv.activate', $purchase) }}" class="inline-flex gap-2 items-center">
                                                @csrf
                                                <input type="text" name="pin_code" placeholder="PIN code" required
                                                       class="rounded-lg border-slate-300 text-sm w-36">
                                                <button class="rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3 py-2">
                                                    Activate
                                                </button>
                                            </form>
                                        @elseif($purchase->status === 'pending')
                                            <span class="text-xs text-slate-400">Awaiting payment confirmation</span>
                                        @elseif($purchase->status === 'activated')
                                            <span class="text-xs text-emerald-600">Active
                                                @if($purchase->activation?->expiry_date)
                                                    · till {{ $purchase->activation->expiry_date->format('d M Y') }}
                                                @endif
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $purchases->links() }}
            @endif
        </div>
    </div>
@endsection
