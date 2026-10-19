@extends('layouts.app')

@section('title', 'Members')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Member Management</h2>
                <p class="text-sm text-slate-500">View and manage all members.</p>
            </div>
            <form method="GET" class="flex items-center gap-2">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.35-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, ID..."
                           class="w-64 rounded-xl border-slate-300 bg-slate-50/50 py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition focus:bg-white focus:border-brand-500 focus:ring-brand-500 focus:ring-2">
                </div>
                <button type="submit" class="rounded-xl bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white text-sm font-semibold px-4 py-2.5 shadow-md shadow-brand-600/25 transition-colors">Search</button>
                @if(request('search'))
                    <a href="{{ route('admin.users') }}" class="rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-600 text-sm font-medium px-4 py-2.5 transition-colors">Clear</a>
                @endif
            </form>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-slate-500 border-b border-slate-200">
                            <th class="py-2">Member</th>
                            <th class="py-2">ID</th>
                            <th class="py-2">Sponsor</th>
                            <th class="py-2">Role</th>
                            <th class="py-2">KYC</th>
                            <th class="py-2">Status</th>
                            <th class="py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr class="border-b border-slate-100">
                                <td class="py-2.5">
                                    <div class="font-medium text-slate-800">{{ $user->name }}</div>
                                    <div class="text-xs text-slate-400">{{ $user->email }}</div>
                                </td>
                                <td class="py-2.5 text-slate-600">{{ $user->unique_id }}</td>
                                <td class="py-2.5 text-slate-600">{{ $user->sponsor?->unique_id ?? '—' }}</td>
                                <td class="py-2.5 capitalize">{{ $user->role }}</td>
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-medium
                                        {{ $user->kyc_status === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($user->kyc_status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                        {{ str_replace('_', ' ', $user->kyc_status) }}
                                    </span>
                                </td>
                                <td class="py-2.5">
                                    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="inline-flex items-center gap-2">
                                        @csrf @method('PUT')
                                        <select name="status" onchange="this.form.submit()"
                                                class="rounded-lg border-slate-300 text-xs py-1 {{ $user->status === 'active' ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200' }}">
                                            @foreach(['active', 'inactive', 'pending', 'suspended'] as $status)
                                                <option value="{{ $status }}" {{ $user->status === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td class="py-2.5 text-right">
                                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="{{ $user->status }}">
                                        <select name="role" onchange="this.form.submit()" class="rounded-lg border-slate-300 text-xs py-1">
                                            @foreach(['member', 'agent', 'admin'] as $role)
                                                <option value="{{ $role }}" {{ $user->role === $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $users->links() }}
        </div>
    </div>
@endsection
