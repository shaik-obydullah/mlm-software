@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">My Profile</h2>
            <p class="text-sm text-slate-500">Manage your details, nominee and bank information.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden self-start">
                <div class="h-20 bg-gradient-to-r from-brand-500 to-brand-700"></div>
                <div class="px-5 -mt-10 flex flex-col items-center text-center">
                    <div class="w-20 h-20 rounded-full bg-brand-500 text-white flex items-center justify-center text-3xl font-bold ring-4 ring-white shadow-md shadow-brand-500/30">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h3 class="mt-3 font-semibold text-slate-900">{{ $user->name }}</h3>
                    <p class="text-xs text-slate-500">{{ $user->unique_id }} · {{ $user->email }}</p>
                    <div class="flex gap-2 mt-3">
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold capitalize
                            {{ $user->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $user->status }}</span>
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold
                            {{ $user->kyc_status === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($user->kyc_status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">KYC: {{ str_replace('_', ' ', $user->kyc_status) }}</span>
                    </div>
                </div>

                <div class="mx-5 mt-5 grid grid-cols-3 divide-x divide-slate-100 border-t border-slate-100">
                    <div class="py-3 pr-2 text-center">
                        <div class="text-sm font-semibold text-slate-900">{{ $user->sponsor?->unique_id ?? '—' }}</div>
                        <div class="text-[11px] text-slate-400">Sponsor</div>
                    </div>
                    <div class="py-3 px-2 text-center">
                        <div class="text-sm font-semibold text-slate-900">{{ $user->referrals->count() }}</div>
                        <div class="text-[11px] text-slate-400">Referrals</div>
                    </div>
                    <div class="py-3 pl-2 text-center">
                        <div class="text-sm font-semibold text-slate-900">{{ $user->created_at->format('d M Y') }}</div>
                        <div class="text-[11px] text-slate-400">Since</div>
                    </div>
                </div>

                <div class="px-5 pb-5">
                    <div class="rounded-lg bg-slate-50 border border-slate-200 p-3">
                        <div class="flex items-center gap-1.5 text-xs text-slate-500 mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101M10.172 13.828a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            Your referral link
                        </div>
                        <div x-data="{ copied: false }" class="flex items-center gap-2">
                            <input readonly :value="'{{ url('/register') }}?sponsor={{ $user->unique_id }}'"
                                   class="flex-1 rounded-lg border-slate-200 text-xs bg-white px-3 py-2">
                            <button type="button" @click="copy($el.previousElementSibling.value); copied = true; setTimeout(() => copied = false, 2000)"
                                    class="text-xs font-semibold text-brand-600 hover:text-brand-700 whitespace-nowrap" x-text="copied ? 'Copied!' : 'Copy'"></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-brand-500 to-brand-700 text-white">
                        <div class="w-9 h-9 rounded-lg bg-white/15 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold leading-tight">Personal Information</h3>
                            <p class="text-xs text-white/80">Your contact and nominee details</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('web.profile.update') }}" class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Full name</label>
                            <input type="text" name="name" value="{{ $user->name }}" required class="mt-1.5 w-full rounded-xl border-slate-300 bg-slate-50/50 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:bg-white focus:border-brand-500 focus:ring-brand-500 focus:ring-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Phone</label>
                            <input type="text" name="phone" value="{{ $user->phone }}" class="mt-1.5 w-full rounded-xl border-slate-300 bg-slate-50/50 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:bg-white focus:border-brand-500 focus:ring-brand-500 focus:ring-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Nominee name</label>
                            <input type="text" name="nominee_name" value="{{ $user->nominee_name }}" class="mt-1.5 w-full rounded-xl border-slate-300 bg-slate-50/50 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:bg-white focus:border-brand-500 focus:ring-brand-500 focus:ring-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Nominee relation</label>
                            <input type="text" name="nominee_relation" value="{{ $user->nominee_relation }}" class="mt-1.5 w-full rounded-xl border-slate-300 bg-slate-50/50 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:bg-white focus:border-brand-500 focus:ring-brand-500 focus:ring-2">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Nominee phone</label>
                            <input type="text" name="nominee_phone" value="{{ $user->nominee_phone }}" class="mt-1.5 w-full rounded-xl border-slate-300 bg-slate-50/50 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:bg-white focus:border-brand-500 focus:ring-brand-500 focus:ring-2">
                        </div>
                        <div class="sm:col-span-2 pt-2">
                            <button type="submit" class="rounded-xl bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white text-sm font-semibold px-6 py-2.5 shadow-md shadow-brand-600/25 transition-all">Save personal info</button>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-sky-500 to-sky-700 text-white">
                        <div class="w-9 h-9 rounded-lg bg-white/15 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold leading-tight">Bank Details</h3>
                            <p class="text-xs text-white/80">Used for your withdrawals</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('web.profile.update') }}" class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Bank name</label>
                            <input type="text" name="bank_name" value="{{ $user->bank_name }}" class="mt-1.5 w-full rounded-xl border-slate-300 bg-slate-50/50 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:bg-white focus:border-sky-500 focus:ring-sky-500 focus:ring-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Account name</label>
                            <input type="text" name="bank_account_name" value="{{ $user->bank_account_name }}" class="mt-1.5 w-full rounded-xl border-slate-300 bg-slate-50/50 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:bg-white focus:border-sky-500 focus:ring-sky-500 focus:ring-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Account number</label>
                            <input type="text" name="bank_account_number" value="{{ $user->bank_account_number }}" class="mt-1.5 w-full rounded-xl border-slate-300 bg-slate-50/50 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:bg-white focus:border-sky-500 focus:ring-sky-500 focus:ring-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">IFSC</label>
                            <input type="text" name="bank_ifsc" value="{{ $user->bank_ifsc }}" class="mt-1.5 w-full rounded-xl border-slate-300 bg-slate-50/50 px-4 py-3 text-sm text-slate-900 uppercase shadow-sm transition focus:bg-white focus:border-sky-500 focus:ring-sky-500 focus:ring-2">
                        </div>
                        <div class="sm:col-span-2 pt-2">
                            <button type="submit" class="rounded-xl bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white text-sm font-semibold px-6 py-2.5 shadow-md shadow-sky-600/25 transition-all">Save bank details</button>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-emerald-500 to-emerald-700 text-white">
                        <div class="w-9 h-9 rounded-lg bg-white/15 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold leading-tight">KYC Verification</h3>
                            <p class="text-xs text-white/80">Upload identity documents for review</p>
                        </div>
                    </div>
                    <div class="p-5">
                        <form method="POST" action="{{ route('web.profile.kyc') }}" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Document type</label>
                                <select name="type" class="mt-1.5 w-full rounded-xl border-slate-300 bg-slate-50/50 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 focus:ring-2">
                                    <option value="id_card">ID Card</option>
                                    <option value="passport">Passport</option>
                                    <option value="driving_license">Driving license</option>
                                    <option value="address_proof">Address proof</option>
                                    <option value="selfie">Selfie</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">File (max 5MB)</label>
                                <input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf" required class="mt-1.5 w-full text-sm">
                            </div>
                            <div>
                                <button type="submit" class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-semibold px-6 py-3 shadow-md shadow-emerald-600/25 transition-all">Upload document</button>
                            </div>
                        </form>

                        @if($user->kycDocuments->isNotEmpty())
                            <div class="mt-5 border-t border-slate-100 pt-4 space-y-2">
                                @foreach($user->kycDocuments as $doc)
                                    <div class="flex items-center justify-between rounded-xl bg-slate-50 border border-slate-100 px-4 py-3 text-sm">
                                        <span class="flex items-center gap-2 capitalize text-slate-700">
                                            <span class="w-7 h-7 rounded-lg bg-white border border-slate-200 flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            </span>
                                            {{ str_replace('_', ' ', $doc->type) }}
                                        </span>
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold
                                            {{ $doc->status === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($doc->status === 'rejected' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">
                                            {{ ucfirst($doc->status) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
