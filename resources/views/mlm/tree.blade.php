@extends('layouts.app')

@section('title', 'Binary Tree')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Your Binary Tree</h2>
                <p class="text-sm text-slate-500">Auto-placement balances weaker legs for maximum matching bonuses.</p>
            </div>
            <div class="flex gap-3 text-xs text-slate-600">
                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-emerald-500"></span> Left leg</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-sky-500"></span> Right leg</span>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <x-stat-card label="Left Leg Volume" value="{{ number_format($volumes['left']) }} CV" color="emerald" />
            <x-stat-card label="Right Leg Volume" value="{{ number_format($volumes['right']) }} CV" color="sky" />
            <x-stat-card label="Matched Volume" value="{{ number_format(min($volumes['left'], $volumes['right'])) }} CV" color="amber" />
            <x-stat-card label="Total Downline" value="{{ $downline->flatten()->count() }} members" color="brand" />
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6 overflow-x-auto">
            @if($tree)
                <div class="min-w-max mx-auto">
                    <x-tree-node :node="$tree" :depth="0" />
                </div>
            @else
                <p class="text-sm text-slate-500 text-center py-8">You are not placed in the tree yet.</p>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @foreach($downline as $level => $members)
                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <h3 class="text-sm font-semibold text-slate-900 mb-3">Level {{ $level }} — {{ $members->count() }} member(s)</h3>
                    <div class="space-y-2">
                        @foreach($members as $member)
                            <div class="flex items-center justify-between text-sm rounded-lg bg-slate-50 px-3 py-2">
                                <div class="min-w-0">
                                    <span class="font-medium text-slate-700">{{ $member->user?->name }}</span>
                                    <span class="text-xs text-slate-400 ml-1">{{ $member->user?->unique_id }}</span>
                                </div>
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $member->user?->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $member->user?->status }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
