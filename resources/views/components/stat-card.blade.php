@props(['label', 'value', 'icon' => null, 'color' => 'brand'])

@php
    $colors = [
        'brand' => 'bg-brand-50 text-brand-700',
        'emerald' => 'bg-emerald-50 text-emerald-700',
        'amber' => 'bg-amber-50 text-amber-700',
        'sky' => 'bg-sky-50 text-sky-700',
        'rose' => 'bg-rose-50 text-rose-700',
        'violet' => 'bg-violet-50 text-violet-700',
    ];
@endphp

<div class="rounded-xl border border-slate-200 bg-white p-4">
    <div class="flex items-center justify-between">
        <div>
            <div class="text-xs text-slate-500 font-medium">{{ $label }}</div>
            <div class="text-xl font-bold text-slate-900 mt-1">{{ $value }}</div>
        </div>
        @if($icon)
            <div class="w-10 h-10 rounded-lg {{ $colors[$color] ?? $colors['brand'] }} flex items-center justify-center">
                @switch($icon)
                    @case('wallet')
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h14a2 2 0 002-2v-5m0 0l-3-1m3 1v-1a1 1 0 00-1-1h-4a1 1 0 00-1 1v1"/></svg>
                        @break
                    @case('chart')
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13l4-4 4 4 6-6m0 0v5m0-5h-5"/></svg>
                        @break
                    @case('cv')
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                        @break
                    @case('users')
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        @break
                @endswitch
            </div>
        @endif
    </div>
</div>
