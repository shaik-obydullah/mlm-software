<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\KycDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user = auth()->user()->load('kycDocuments', 'referrals:id,name,unique_id,status,created_at');

        return view('profile.show', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'nominee_name' => ['nullable', 'string', 'max:255'],
            'nominee_relation' => ['nullable', 'string', 'max:50'],
            'nominee_phone' => ['nullable', 'string', 'max:30'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_ifsc' => ['nullable', 'string', 'max:30'],
        ]);

        auth()->user()->update($validated);

        return back()->with('success', 'Profile updated.');
    }

    public function uploadKyc(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:id_card,passport,driving_license,address_proof,selfie'],
            'document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $path = $request->file('document')->store('kyc/'.auth()->id(), 'public');

        KycDocument::create([
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'file_path' => $path,
            'status' => 'pending',
        ]);

        auth()->user()->update(['kyc_status' => 'pending']);

        return back()->with('success', 'KYC document uploaded for review.');
    }

    public function referralLink(): string
    {
        return route('web.register', ['sponsor' => auth()->user()->unique_id]);
    }
}
