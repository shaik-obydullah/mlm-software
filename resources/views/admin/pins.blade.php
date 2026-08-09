@extends('layouts.app')

@section('title', 'PIN Management')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">PIN Management</h2>
            <p class="text-sm text-slate-500">Generate activation PINs and assign them to members.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="space-y-6">
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-brand-600 to-brand-700 px-5 py-4 text-white">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-white/15 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold leading-tight">Generate PINs</h3>
                                <p class="text-xs text-brand-100">Create activation PINs for a tier</p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.pins.generate') }}" class="p-5 space-y-4" x-data="{ count: 10 }">
                        @csrf
                        <div>
                            <label for="cv_tier_id" class="block text-sm font-medium text-slate-700">CV Tier</label>
                            <select id="cv_tier_id" name="cv_tier_id"
                                    class="mt-1.5 w-full rounded-xl border-slate-300 bg-slate-50/50 py-3 pl-3 pr-4 text-sm text-slate-900 shadow-sm transition focus:bg-white focus:border-brand-500 focus:ring-brand-500 focus:ring-2">
                                @foreach($tiers as $tier)
                                    <option value="{{ $tier->id }}">{{ $tier->name }} — {{ $tier->amount }} CV</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="count" class="block text-sm font-medium text-slate-700">Quantity</label>
                            <div class="mt-1.5 flex items-center rounded-xl border border-slate-300 bg-slate-50/50 shadow-sm overflow-hidden focus-within:border-brand-500 focus-within:ring-brand-500 focus-within:ring-2">
                                <button type="button" @click="count > 1 && count--"
                                        class="w-11 h-11 flex items-center justify-center text-slate-500 hover:bg-slate-100 active:bg-slate-200 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6"/></svg>
                                </button>
                                <input id="count" type="number" name="count" x-model.number="count" min="1" max="100"
                                       class="w-full flex-1 bg-transparent border-0 text-center text-sm font-semibold text-slate-900 focus:ring-0 focus:border-0">
                                <button type="button" @click="count < 100 && count++"
                                        class="w-11 h-11 flex items-center justify-center text-slate-500 hover:bg-slate-100 active:bg-slate-200 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-slate-400">Up to 100 PINs per batch · <span class="text-brand-600 font-medium" x-text="count + ' PIN(s)'"></span></p>
                        </div>

                        <button type="submit"
                                class="w-full rounded-xl bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white text-sm font-semibold py-3 shadow-md shadow-brand-600/25 transition-all hover:shadow-lg hover:shadow-brand-600/30 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                            Generate
                        </button>
                    </form>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 px-5 py-4 text-white">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-white/15 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 4v.01M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold leading-tight">Assign PIN</h3>
                                <p class="text-xs text-emerald-100">Attach an available PIN to a member</p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.pins.assign') }}" class="p-5 space-y-4">
                        @csrf
                        <div>
                            <label for="pin_id" class="block text-sm font-medium text-slate-700">PIN</label>
                            <select id="pin_id" name="pin_id"
                                    class="mt-1.5 w-full rounded-xl border-slate-300 bg-slate-50/50 py-3 pl-3 pr-4 text-sm text-slate-900 shadow-sm transition focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 focus:ring-2">
                                @forelse($pins->where('status', 'available') as $pin)
                                    <option value="{{ $pin->id }}">{{ $pin->code }} ({{ $pin->tier?->amount }} CV)</option>
                                @empty
                                    <option value="">No available PINs — generate some first</option>
                                @endforelse
                            </select>
                        </div>
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-slate-700">Member ID</label>
                            <div class="relative mt-1.5">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <svg class="w-4.5 h-4.5" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </span>
                                <input id="user_id" type="text" name="user_id" value="{{ old('user_id') }}" placeholder="e.g. 176"
                                       class="w-full rounded-xl border-slate-300 bg-slate-50/50 py-3 pl-10 pr-4 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 focus:ring-2">
                            </div>
                            <p class="mt-1 text-xs text-slate-400">Enter the numeric member ID shown in the Members page</p>
                        </div>
                        <button type="submit"
                                class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-semibold py-3 shadow-md shadow-emerald-600/25 transition-all hover:shadow-lg hover:shadow-emerald-600/30 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                            Assign
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <h3 class="text-sm font-semibold text-slate-900">PINs</h3>
                    <form method="GET" class="flex items-center gap-2">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.35-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search PIN code..."
                                   class="w-44 rounded-xl border-slate-300 bg-slate-50/50 py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition focus:bg-white focus:border-brand-500 focus:ring-brand-500 focus:ring-2">
                        </div>
                        <select name="status" onchange="this.form.submit()"
                                class="rounded-xl border-slate-300 bg-slate-50/50 py-2.5 pl-3 pr-8 text-sm text-slate-900 shadow-sm transition focus:bg-white focus:border-brand-500 focus:ring-brand-500 focus:ring-2">
                            <option value="">All statuses</option>
                            @foreach(['available', 'assigned', 'used', 'expired'] as $status)
                                <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2.5 shadow-md shadow-brand-600/25 transition-colors">Search</button>
                        @if(request('search') || request('status'))
                            <a href="{{ route('admin.pins') }}" class="rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-600 text-sm font-medium px-4 py-2.5 transition-colors">Clear</a>
                        @endif
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-slate-500 border-b border-slate-200">
                                <th class="py-2">Code</th>
                                <th class="py-2">Tier</th>
                                <th class="py-2">Assigned To</th>
                                <th class="py-2">Expires</th>
                                <th class="py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pins as $pin)
                                <tr class="border-b border-slate-100">
                                    <td class="py-2.5 font-mono text-slate-700">{{ $pin->code }}</td>
                                    <td class="py-2.5">{{ $pin->tier?->name }} ({{ $pin->tier?->amount }} CV)</td>
                                    <td class="py-2.5 text-slate-600">{{ $pin->assignedUser?->unique_id ?? '—' }}</td>
                                    <td class="py-2.5 text-slate-600">{{ $pin->expires_at?->format('d M Y') ?? 'Never' }}</td>
                                    <td class="py-2.5">
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-medium
                                            {{ $pin->status === 'available' ? 'bg-emerald-50 text-emerald-700' : ($pin->status === 'used' ? 'bg-slate-100 text-slate-600' : ($pin->status === 'expired' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700')) }}">
                                            {{ ucfirst($pin->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $pins->links() }}
            </div>
        </div>
    </div>
@endsection
