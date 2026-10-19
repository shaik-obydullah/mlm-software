@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    @php
        $groups = [
            'Platform' => [
                'desc' => 'General platform identity and currency',
                'accent' => 'from-brand-500 to-brand-700',
                'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
                'fields' => [
                    'site_name' => ['label' => 'Site name', 'hint' => 'Shown in the browser title and branding', 'suffix' => null],
                    'currency' => ['label' => 'Currency', 'hint' => 'Display currency for all amounts', 'suffix' => null],
                ],
            ],
            'Commissions' => [
                'desc' => 'Bonus rates paid on the binary tree',
                'accent' => 'from-emerald-500 to-emerald-700',
                'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                'fields' => [
                    'sponsor_bonus_percent' => ['label' => 'Sponsor bonus', 'hint' => '% of referred member\u2019s CV paid to the sponsor', 'suffix' => '%'],
                    'matching_bonus_percent' => ['label' => 'Matching bonus', 'hint' => '% of the weaker binary leg\u2019s volume', 'suffix' => '%'],
                ],
            ],
            'Withdrawals' => [
                'desc' => 'Fees and limits for payout requests',
                'accent' => 'from-sky-500 to-sky-700',
                'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
                'fields' => [
                    'withdrawal_fee_percent' => ['label' => 'Withdrawal fee', 'hint' => 'Fee deducted from every payout', 'suffix' => '%'],
                    'min_withdrawal' => ['label' => 'Minimum withdrawal', 'hint' => 'Lowest amount a member can request', 'suffix' => '{{ $cur }}'],
                ],
            ],
            'CV & Interest' => [
                'desc' => 'Package validity and monthly interest accrual',
                'accent' => 'from-amber-500 to-amber-700',
                'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'fields' => [
                    'cv_validity_months' => ['label' => 'CV validity', 'hint' => 'Months an activated CV remains active', 'suffix' => 'months'],
                    'interest_rate_percent' => ['label' => 'Interest rate', 'hint' => 'Monthly interest credited to active CV', 'suffix' => '%'],
                    'interest_frequency' => ['label' => 'Interest frequency', 'hint' => 'How often interest is accrued', 'suffix' => null],
                ],
            ],
        ];

        $cur = $settings->get('general')->firstWhere('key', 'currency')->value ?? '₹';
    @endphp

    <div class="space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">System Settings</h2>
            <p class="text-sm text-slate-500">Configure bonuses, fees and platform settings.</p>
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf
            @foreach($groups as $group => $config)
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 bg-gradient-to-r {{ $config['accent'] }} text-white">
                        <div class="w-9 h-9 rounded-lg bg-white/15 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $config['icon'] }}"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold leading-tight">{{ $group }}</h3>
                            <p class="text-xs text-white/80">{{ $config['desc'] }}</p>
                        </div>
                    </div>

                    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($config['fields'] as $key => $field)
                            @php
                                $setting = $settings->get('general')->firstWhere('key', $key);
                                $suffix = $field['suffix'] === '{{ $cur }}' ? $cur : $field['suffix'];
                            @endphp
                            <div>
                                <label for="settings-{{ $key }}" class="block text-sm font-medium text-slate-700">{{ $field['label'] }}</label>
                                <div class="relative mt-1.5">
                                    <input id="settings-{{ $key }}" type="text" name="settings[{{ $key }}]" value="{{ $setting?->value }}"
                                           class="w-full rounded-xl border-slate-300 bg-slate-50/50 py-3 {{ $suffix ? 'pr-12 pl-4' : 'px-4' }} text-sm text-slate-900 shadow-sm transition focus:bg-white focus:border-brand-500 focus:ring-brand-500 focus:ring-2">
                                    @if($suffix)
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-sm text-slate-400">{{ $suffix }}</span>
                                    @endif
                                </div>
                                <p class="mt-1 text-xs text-slate-400">{{ $field['hint'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.settings') }}" class="rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold px-6 py-2.5 transition-colors">Reset</a>
                <button type="submit" class="rounded-xl bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white text-sm font-semibold px-6 py-2.5 shadow-md shadow-brand-600/25 transition-all">Save settings</button>
            </div>
        </form>
    </div>
@endsection
