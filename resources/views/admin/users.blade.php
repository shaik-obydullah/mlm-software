@extends('layouts.app')

@section('title', 'Members')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Member Management</h2>
                <p class="text-sm text-slate-500">View and manage all members.</p>
            </div>
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, ID..."
                       class="rounded-lg border-slate-300 text-sm w-64">
                <button type="submit" class="rounded-lg bg-brand-600 text-white text-sm px-4 py-2">Search</button>
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
