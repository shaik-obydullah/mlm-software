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
                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Generate PINs</h3>
                    <form method="POST" action="{{ route('admin.pins.generate') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-700">CV Tier</label>
                            <select name="cv_tier_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                                @foreach($tiers as $tier)
                                    <option value="{{ $tier->id }}">{{ $tier->name }} — {{ $tier->amount }} CV</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Quantity</label>
                            <input type="number" name="count" min="1" max="100" value="10" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <button type="submit" class="w-full rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold py-2.5">Generate</button>
                    </form>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Assign PIN</h3>
                    <form method="POST" action="{{ route('admin.pins.assign') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-700">PIN</label>
                            <select name="pin_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                                @foreach($pins->where('status', 'available') as $pin)
                                    <option value="{{ $pin->id }}">{{ $pin->code }} ({{ $pin->tier?->amount }} CV)</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Member ID</label>
                            <input type="text" name="user_id" placeholder="User ID number" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <button type="submit" class="w-full rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold py-2.5">Assign</button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-slate-900">PINs</h3>
                    <form method="GET">
                        <select name="status" onchange="this.form.submit()" class="rounded-lg border-slate-300 text-sm">
                            <option value="">All statuses</option>
                            @foreach(['available', 'assigned', 'used', 'expired'] as $status)
                                <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
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
