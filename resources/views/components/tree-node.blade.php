@props(['node', 'depth' => 0])

@if($node)
    <div class="flex flex-col items-center">
        <div class="tree-node" x-data="{ expanded: {{ $depth < 2 ? 'true' : 'false' }} }">
            <button type="button" @click="expanded = !expanded"
                    class="group flex items-center gap-2 rounded-lg border px-3 py-2 text-left transition-colors
                           {{ $depth === 0 ? 'border-brand-400 bg-brand-50' : 'border-slate-200 bg-white hover:border-brand-300' }}">
                <span class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-600">
                    {{ strtoupper(substr($node['name'], 0, 1)) }}
                </span>
                <span class="min-w-0">
                    <span class="block text-xs font-semibold text-slate-800 max-w-24 truncate">{{ $node['name'] }}</span>
                    <span class="block text-[10px] text-slate-500">{{ $node['unique_id'] }}</span>
                </span>
                <span class="text-[10px] text-slate-400 font-medium whitespace-nowrap">{{ number_format($node['cv_volume']) }} CV</span>
                @if(($node['left'] ?? null) || ($node['right'] ?? null))
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                @endif
            </button>

            @if(($node['left'] ?? null) || ($node['right'] ?? null))
                <div x-show="expanded" x-transition>
                    <div class="mt-2 flex gap-6 justify-center">
                        <div class="tree-connector flex-1 min-w-0">
                            @if($node['left'])
                                <x-tree-node :node="$node['left']" :depth="$depth + 1" />
                            @else
                                <div class="text-[10px] text-slate-400 text-center">Empty</div>
                            @endif
                        </div>
                        <div class="tree-connector flex-1 min-w-0">
                            @if($node['right'])
                                <x-tree-node :node="$node['right']" :depth="$depth + 1" />
                            @else
                                <div class="text-[10px] text-slate-400 text-center">Empty</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif
