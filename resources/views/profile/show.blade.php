@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">My Profile</h2>
            <p class="text-sm text-slate-500">Manage your details, nominee and bank information.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <div class="flex flex-col items-center text-center pb-4 border-b border-slate-100">
                    <div class="w-16 h-16 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-2xl font-bold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h3 class="mt-3 font-semibold text-slate-900">{{ $user->name }}</h3>
                    <p class="text-xs text-slate-500">{{ $user->unique_id }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ $user->email }}</p>
                    <div class="flex gap-2 mt-3">
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-medium capitalize
                            {{ $user->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $user->status }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-medium
                            {{ $user->kyc_status === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($user->kyc_status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">KYC: {{ str_replace('_', ' ', $user->kyc_status) }}</span>
                    </div>
                </div>

                <div class="pt-4 space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Sponsor</span><span class="font-medium">{{ $user->sponsor?->unique_id ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Direct referrals</span><span class="font-medium">{{ $user->referrals->count() }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Member since</span><span class="font-medium">{{ $user->created_at->format('d M Y') }}</span></div>
                </div>

                <div class="mt-4 rounded-lg bg-slate-50 border border-slate-200 p-3">
                    <div class="text-xs text-slate-500 mb-1">Your referral link</div>
                    <div x-data="{ copied: false }" class="flex items-center gap-2">
                        <input readonly :value="'{{ url('/register') }}?sponsor={{ $user->unique_id }}'"
                               class="flex-1 rounded-lg border-slate-300 text-xs bg-white">
                        <button type="button" @click="copy($el.previousElementSibling.value); copied = true; setTimeout(() => copied = false, 2000)"
                                class="text-xs font-medium text-brand-600 hover:text-brand-700 whitespace-nowrap" x-text="copied ? 'Copied!' : 'Copy'"></button>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Personal Information</h3>
                    <form method="POST" action="{{ route('web.profile.update') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Full name</label>
                            <input type="text" name="name" value="{{ $user->name }}" required class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Phone</label>
                            <input type="text" name="phone" value="{{ $user->phone }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Nominee name</label>
                            <input type="text" name="nominee_name" value="{{ $user->nominee_name }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Nominee relation</label>
                            <input type="text" name="nominee_relation" value="{{ $user->nominee_relation }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Nominee phone</label>
                            <input type="text" name="nominee_phone" value="{{ $user->nominee_phone }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div class="sm:col-span-2 pt-2">
                            <button type="submit" class="rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2">Save personal info</button>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Bank Details <span class="text-xs font-normal text-slate-400">(used for withdrawals)</span></h3>
                    <form method="POST" action="{{ route('web.profile.update') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Bank name</label>
                            <input type="text" name="bank_name" value="{{ $user->bank_name }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Account name</label>
                            <input type="text" name="bank_account_name" value="{{ $user->bank_account_name }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Account number</label>
                            <input type="text" name="bank_account_number" value="{{ $user->bank_account_number }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">IFSC</label>
                            <input type="text" name="bank_ifsc" value="{{ $user->bank_ifsc }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div class="sm:col-span-2 pt-2">
                            <button type="submit" class="rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2">Save bank details</button>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">KYC Verification</h3>
                    <form method="POST" action="{{ route('web.profile.kyc') }}" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Document type</label>
                            <select name="type" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                                <option value="id_card">ID Card</option>
                                <option value="passport">Passport</option>
                                <option value="driving_license">Driving license</option>
                                <option value="address_proof">Address proof</option>
                                <option value="selfie">Selfie</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">File (max 5MB)</label>
                            <input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf" required class="mt-1 w-full text-sm">
                        </div>
                        <div>
                            <button type="submit" class="w-full rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2">Upload document</button>
                        </div>
                    </form>

                    <div class="mt-4 space-y-2">
                        @foreach($user->kycDocuments as $doc)
                            <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 text-sm">
                                <span class="capitalize text-slate-700">{{ str_replace('_', ' ', $doc->type) }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium
                                    {{ $doc->status === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($doc->status === 'rejected' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">
                                    {{ ucfirst($doc->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
