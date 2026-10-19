<?php

namespace Tests\Feature;

use App\Enums\PinStatus;
use App\Models\Pin;
use App\Services\CvService;
use App\Services\PinService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use RuntimeException;

class PinServiceTest extends FeatureTestCase
{
    public function test_generate_creates_the_requested_number_of_pins(): void
    {
        $user = $this->admin();
        $tier = $this->tier(200);
        $service = app(PinService::class);

        $pins = $service->generate($user->id, $tier->id, 3);

        $this->assertCount(3, $pins);
        $this->assertSame(3, Pin::count());
        $this->assertSame(PinStatus::Available->value, $pins[0]->status);
        $this->assertSame($tier->id, $pins[0]->cv_tier_id);
        $this->assertNotSame($pins[0]->code, $pins[1]->code);
    }

    public function test_generate_rejects_out_of_range_counts(): void
    {
        $user = $this->admin();
        $tier = $this->tier(200);
        $service = app(PinService::class);

        $this->expectException(\InvalidArgumentException::class);
        $service->generate($user->id, $tier->id, 501);
    }

    public function test_generate_requires_existing_tier(): void
    {
        $user = $this->admin();
        $service = app(PinService::class);

        $this->expectException(ModelNotFoundException::class);
        $service->generate($user->id, 99999);
    }

    public function test_assign_marks_pin_as_assigned(): void
    {
        $admin = $this->admin();
        $member = $this->member();
        $service = app(PinService::class);

        $pin = $service->generate($admin->id, $this->tier(200)->id)[0];
        $assigned = $service->assign($pin->id, $member->id);

        $this->assertSame(PinStatus::Assigned->value, $assigned->status);
        $this->assertSame($member->id, $assigned->assigned_to);
        $this->assertNotNull($assigned->assigned_at);
    }

    public function test_assign_rejects_unavailable_pins(): void
    {
        $admin = $this->admin();
        $member = $this->member();
        $service = app(PinService::class);

        $pin = $service->generate($admin->id, $this->tier(200)->id)[0];
        $service->assign($pin->id, $member->id);

        $this->expectException(RuntimeException::class);
        $service->assign($pin->id, $member->id);
    }

    public function test_use_consumes_a_valid_pin(): void
    {
        $admin = $this->admin();
        $member = $this->member();
        $service = app(PinService::class);

        $pin = $service->generate($admin->id, $this->tier(200)->id)[0];
        $purchase = app(CvService::class)->purchase($member->id, $this->tier(200)->id, 'bank');
        $used = $service->use($pin->code, $member->id, $purchase->id);

        $this->assertSame(PinStatus::Used->value, $used->status);
        $this->assertSame($member->id, $used->used_by);
        $this->assertSame($purchase->id, $used->purchase_id);
        $this->assertNotNull($used->used_at);
    }

    public function test_use_rejects_unknown_code(): void
    {
        $member = $this->member();
        $service = app(PinService::class);

        $this->expectException(RuntimeException::class);
        $service->use('NOT-A-CODE', $member->id, 1);
    }

    public function test_use_rejects_pin_assigned_to_another_user(): void
    {
        $admin = $this->admin();
        $owner = $this->member();
        $other = $this->member();
        $service = app(PinService::class);

        $pin = $service->generate($admin->id, $this->tier(200)->id)[0];
        $service->assign($pin->id, $owner->id);

        $this->expectException(RuntimeException::class);
        $service->use($pin->code, $other->id, 1);
    }

    public function test_validate_checks_tier_matching(): void
    {
        $admin = $this->admin();
        $member = $this->member();
        $service = app(PinService::class);

        $pin = $service->generate($admin->id, $this->tier(200)->id)[0];
        $service->validate($pin->code, $member->id, $pin->cv_tier_id);

        $this->expectException(RuntimeException::class);
        $service->validate($pin->code, $member->id, $this->tier(400)->id);
    }

    public function test_expire_marks_past_due_pins_as_expired(): void
    {
        $admin = $this->admin();
        $service = app(PinService::class);

        $service->generate($admin->id, $this->tier(200)->id, 2);
        $expiredPin = Pin::factory()->create([
            'created_by' => $admin->id,
            'cv_tier_id' => $this->tier(200)->id,
            'status' => 'available',
            'expires_at' => Carbon::yesterday(),
        ]);

        $count = $service->expire();

        $this->assertSame(1, $count);
        $this->assertSame(PinStatus::Expired->value, $expiredPin->fresh()->status);
        $this->assertSame(2, Pin::where('status', 'available')->count());
    }

    public function test_available_returns_only_available_pins_for_tier(): void
    {
        $admin = $this->admin();
        $member = $this->member();
        $service = app(PinService::class);

        $tier200 = $this->tier(200);
        $tier400 = $this->tier(400);

        $service->generate($admin->id, $tier200->id, 2);
        $pin = $service->generate($admin->id, $tier400->id, 1)[0];
        $service->assign($pin->id, $member->id);

        $available = $service->available($tier200->id);

        $this->assertCount(2, $available);
        $this->assertCount(0, $service->available($tier400->id));
    }
}
