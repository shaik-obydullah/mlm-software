@extends('layouts.app')

@section('title', 'KYC Review')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">KYC Verification</h2>
            <p class="text-sm text-slate-500">Review member identity documents.</p>
        </div>

        @if($documents->isEmpty())
            <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
                <p class="text-sm text-slate-500">No pending KYC documents.</p>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($documents as $doc)
                    <div class="bg-white rounded-xl border border-slate-200 p-5">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="font-semibold text-slate-900">{{ $doc->user?->name }}</h3>
                                <p class="text-xs text-slate-500">{{ $doc->user?->unique_id }} · {{ $doc->user?->email }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-[11px] font-medium capitalize">
                                {{ str_replace('_', ' ', $doc->type) }}
                            </span>
                        </div>

                        <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank"
                           class="flex items-center justify-between rounded-lg border border-slate-200 px-4 py-3 mb-4 hover:border-brand-300 transition-colors">
                            <span class="text-sm text-slate-700">View document</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>

                        <form method="POST" action="{{ route('admin.kyc.review', $doc) }}" class="flex gap-2">
                            @csrf
                            <input type="text" name="rejection_reason" placeholder="Reason (if rejecting)" class="flex-1 rounded-lg border-slate-300 text-sm">
                            <button name="status" value="approved" class="rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-4 py-2">Approve</button>
                            <button name="status" value="rejected" class="rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold px-4 py-2">Reject</button>
                        </form>
                    </div>
                @endforeach
            </div>
            {{ $documents->links() }}
        @endif
    </div>
@endsection
