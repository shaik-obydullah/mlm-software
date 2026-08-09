<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KycDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KycController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:id_card,passport,driving_license,address_proof,selfie'],
            'document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $path = $request->file('document')->store('kyc/'.$request->user()->id, 'public');

        $document = KycDocument::create([
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'file_path' => $path,
            'status' => 'pending',
        ]);

        $request->user()->update(['kyc_status' => 'pending']);

        return response()->json([
            'message' => 'KYC document uploaded for review.',
            'document' => $document,
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $documents = KycDocument::with('user:id,name,unique_id')
            ->when(! $request->user()->isAdmin(), fn ($q) => $q->where('user_id', $request->user()->id))
            ->orderByDesc('id')
            ->paginate(15);

        return response()->json($documents);
    }

    public function review(Request $request, int $kyc): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $document = KycDocument::findOrFail($kyc);

        $document->update([
            'status' => $validated['status'],
            'rejection_reason' => $validated['rejection_reason'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $user = $document->user;

        if ($validated['status'] === 'approved') {
            $allApproved = $user->kycDocuments()->where('status', '!=', 'approved')->doesntExist();
            if ($allApproved) {
                $user->update(['kyc_status' => 'approved']);
            }
        } else {
            $user->update(['kyc_status' => 'rejected']);
        }

        return response()->json([
            'message' => 'KYC document reviewed.',
            'document' => $document->fresh(),
        ]);
    }
}
