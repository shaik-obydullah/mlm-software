@extends('layouts.app')

@section('title', 'Withdrawals')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Withdrawal Requests</h2>
                <p class="text-sm text-slate-500">Approve or reject member withdrawal requests.</p>
            </div>
            <form method="GET">
                <select name="status" onchange="this.form.submit()" class="rounded-lg border-slate-300 text-sm">
                    <option value="">All statuses</option>
                    @foreach(['pending', 'approved', 'processing', 'completed', 'rejected', 'cancelled'] as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-slate-500 border-b border-slate-200">
                            <th class="py-2">Member</th>
                            <th class="py-2">Bank</th>
                            <th class="py-2 text-right">Amount</th>
                            <th class="py-2 text-right">Fee</th>
                            <th class="py-2 text-right">Net</th>
                            <th class="py-2">Date</th>
                            <th class="py-2">Status</th>
                            <th class="py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($withdrawals as $w)
                            <tr class="border-b border-slate-100">
                                <td class="py-2.5">
                                    <div class="font-medium text-slate-800">{{ $w->user?->name }}</div>
                                    <div class="text-xs text-slate-400">{{ $w->user?->unique_id }}</div>
                                </td>
                                <td class="py-2.5 text-slate-600">
                                    {{ $w->bank_name }}<br>
                                    <span class="text-xs text-slate-400">•••• {{ substr($w->account_number ?? '', -4) }}</span>
                                </td>
                                <td class="py-2.5 text-right font-medium">{{ money($w->amount) }}</td>
                                <td class="py-2.5 text-right text-slate-500">{{ money($w->fee) }}</td>
                                <td class="py-2.5 text-right font-medium text-emerald-600">{{ money($w->net_amount) }}</td>
                                <td class="py-2.5 text-slate-600">{{ $w->created_at->format('d M Y') }}</td>
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-medium
                                        {{ $w->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : ($w->status === 'rejected' || $w->status === 'cancelled' ? 'bg-rose-50 text-rose-700' : ($w->status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-sky-50 text-sky-700')) }}">
                                        {{ ucfirst($w->status) }}
                                    </span>
                                    @if($w->remark)
                                        <div class="text-[10px] text-slate-400 mt-0.5">{{ $w->remark }}</div>
                                    @endif
                                </td>
                                <td class="py-2.5">
                                    @if(in_array($w->status, ['pending', 'processing']))
                                        <div class="flex justify-end gap-2">
                                            <form method="POST" action="{{ route('admin.withdrawals.approve', $w) }}">
                                                @csrf
                                                <button class="rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3 py-1.5">Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.withdrawals.reject', $w) }}"
                                                  x-data="{ remark: '' }"
                                                  @submit.prevent="if(confirm('Reject this withdrawal?')) { $refs.remark.value = remark; $el.submit() }">
                                                @csrf
                                                <input type="hidden" name="remark" x-ref="remark">
                                                <button type="button" class="rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold px-3 py-1.5"
                                                        @click="remark = prompt('Rejection reason (optional):') || ''; if(remark) $el.closest('form').dispatchEvent(new Event('submit', { bubbles: true }))">Reject</button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $withdrawals->links() }}
        </div>
    </div>
@endsection
