@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">System Settings</h2>
            <p class="text-sm text-slate-500">Configure bonuses, fees and platform settings.</p>
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf
            @foreach($settings as $group => $items)
                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4 capitalize">{{ $group }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($items as $setting)
                            <div>
                                <label class="block text-sm font-medium text-slate-700">{{ ucwords(str_replace('_', ' ', $setting->key)) }}</label>
                                <input type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}"
                                       class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="flex justify-end">
                <button type="submit" class="rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-6 py-2.5">Save settings</button>
            </div>
        </form>
    </div>
@endsection
