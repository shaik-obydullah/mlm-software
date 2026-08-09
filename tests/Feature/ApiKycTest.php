<?php

namespace Tests\Feature;

use App\Models\KycDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

class ApiKycTest extends FeatureTestCase
{
    public function test_member_can_upload_a_kyc_document(): void
    {
        Storage::fake('public');

        $user = $this->member(['kyc_status' => 'not_submitted']);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/kyc/upload', [
            'type' => 'id_card',
            'document' => UploadedFile::fake()->create('id.pdf', 100, 'application/pdf'),
        ])->assertStatus(201)
            ->assertJsonPath('document.status', 'pending');

        $this->assertDatabaseHas('kyc_documents', [
            'user_id' => $user->id,
            'type' => 'id_card',
            'status' => 'pending',
        ]);
        $this->assertSame('pending', $user->fresh()->kyc_status);
    }

    public function test_upload_validates_file_type_and_size(): void
    {
        Storage::fake('public');

        $user = $this->member();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/kyc/upload', [
            'type' => 'id_card',
            'document' => UploadedFile::fake()->create('doc.exe', 100),
        ])->assertStatus(422);

        $this->postJson('/api/v1/kyc/upload', [
            'type' => 'unknown_type',
            'document' => UploadedFile::fake()->create('id.pdf', 100, 'application/pdf'),
        ])->assertStatus(422);
    }

    public function test_admin_can_review_and_approve_documents(): void
    {
        $admin = $this->admin();
        $user = $this->member(['kyc_status' => 'pending']);
        $document = KycDocument::create([
            'user_id' => $user->id,
            'type' => 'id_card',
            'file_path' => 'kyc/test.jpg',
            'status' => 'pending',
        ]);

        Sanctum::actingAs($admin);

        $this->postJson("/api/v1/kyc/{$document->id}/review", [
            'status' => 'approved',
        ])->assertOk()
            ->assertJsonPath('document.status', 'approved');

        $this->assertSame('approved', $user->fresh()->kyc_status);
        $this->assertSame($admin->id, $document->fresh()->reviewed_by);
    }

    public function test_kyc_requires_all_documents_approved_before_user_is_approved(): void
    {
        $admin = $this->admin();
        $user = $this->member(['kyc_status' => 'pending']);
        KycDocument::create(['user_id' => $user->id, 'type' => 'id_card', 'file_path' => 'kyc/a.jpg', 'status' => 'approved']);
        KycDocument::create(['user_id' => $user->id, 'type' => 'selfie', 'file_path' => 'kyc/b.jpg', 'status' => 'pending']);

        Sanctum::actingAs($admin);

        $this->postJson('/api/v1/kyc/'.KycDocument::where('type', 'id_card')->first()->id.'/review', [
            'status' => 'approved',
        ])->assertOk();

        $this->assertSame('pending', $user->fresh()->kyc_status);
    }

    public function test_admin_can_reject_a_document(): void
    {
        $admin = $this->admin();
        $user = $this->member(['kyc_status' => 'pending']);
        $document = KycDocument::create([
            'user_id' => $user->id,
            'type' => 'id_card',
            'file_path' => 'kyc/test.jpg',
            'status' => 'pending',
        ]);

        Sanctum::actingAs($admin);

        $this->postJson("/api/v1/kyc/{$document->id}/review", [
            'status' => 'rejected',
            'rejection_reason' => 'Blurry image',
        ])->assertOk();

        $this->assertSame('rejected', $user->fresh()->kyc_status);
        $this->assertSame('Blurry image', $document->fresh()->rejection_reason);
    }

    public function test_member_can_only_see_their_own_documents(): void
    {
        $owner = $this->member();
        KycDocument::create(['user_id' => $owner->id, 'type' => 'id_card', 'file_path' => 'kyc/a.jpg', 'status' => 'pending']);

        $other = $this->member();
        KycDocument::create(['user_id' => $other->id, 'type' => 'selfie', 'file_path' => 'kyc/b.jpg', 'status' => 'pending']);

        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/kyc/documents')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.user_id', $owner->id);
    }

    public function test_admin_can_see_all_documents(): void
    {
        $admin = $this->admin();
        KycDocument::create(['user_id' => $this->member()->id, 'type' => 'id_card', 'file_path' => 'kyc/a.jpg', 'status' => 'pending']);

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/kyc/documents')->assertOk();
    }

    public function test_member_cannot_review_documents(): void
    {
        $member = $this->member();
        Sanctum::actingAs($member);

        $this->postJson('/api/v1/kyc/1/review', ['status' => 'approved'])->assertStatus(403);
    }
}
