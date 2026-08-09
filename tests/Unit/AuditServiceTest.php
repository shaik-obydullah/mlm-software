<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_creates_an_audit_entry(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $entry = app(AuditService::class)->log('test_action', $user, ['a' => 1], ['a' => 2], 'Test description');

        $this->assertDatabaseHas('audit_logs', [
            'id' => $entry->id,
            'user_id' => $user->id,
            'action' => 'test_action',
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'description' => 'Test description',
        ]);

        $this->assertSame(['a' => 1], $entry->old_values);
        $this->assertSame(['a' => 2], $entry->new_values);
    }

    public function test_log_handles_null_subject_and_request(): void
    {
        $entry = app(AuditService::class)->log('system_action');

        $this->assertDatabaseHas('audit_logs', [
            'id' => $entry->id,
            'action' => 'system_action',
            'subject_type' => null,
            'subject_id' => null,
        ]);
    }
}
