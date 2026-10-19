<?php

namespace Tests\Feature;

use App\Models\MlmTree;
use App\Services\CvService;
use App\Services\MlmTreeService;
use RuntimeException;

class MlmTreeServiceTest extends FeatureTestCase
{
    public function test_a_user_can_be_placed_as_root(): void
    {
        $root = $this->member();

        $node = app(MlmTreeService::class)->place($root, $root->id);

        $this->assertDatabaseHas('mlm_trees', [
            'user_id' => $root->id,
            'parent_id' => null,
            'position' => null,
            'level' => 0,
        ]);
        $this->assertNull($node->path);
        $this->assertSame($root->treeNode->id, $node->id);
    }

    public function test_first_two_members_occupy_left_and_right_legs(): void
    {
        $root = $this->member();
        $this->place($root, $root->id);

        $left = $this->member();
        $this->place($left, $root->id);

        $right = $this->member();
        $this->place($right, $root->id);

        $this->assertSame('left', $left->treeNode->position);
        $this->assertSame('right', $right->treeNode->position);
        $this->assertSame(1, $left->treeNode->level);
        $this->assertSame(1, $right->treeNode->level);
        $this->assertSame($root->treeNode->id, $left->treeNode->parent_id);
        $this->assertSame('/'.$root->treeNode->id.'/', $left->treeNode->path);
    }

    public function test_new_members_are_balanced_into_lighter_legs(): void
    {
        $root = $this->member();
        $this->place($root, $root->id);

        $a = $this->member();
        $b = $this->member();
        $c = $this->member();

        $this->place($a, $root->id);
        $this->place($b, $root->id);
        $this->place($c, $root->id);

        $this->assertSame('left', $a->treeNode->position);
        $this->assertSame('right', $b->treeNode->position);
        $this->assertSame($a->treeNode->id, $c->treeNode->parent_id);
        $this->assertSame('left', $c->treeNode->position);
        $this->assertSame(2, $c->treeNode->level);
    }

    public function test_explicit_position_is_honoured_when_free(): void
    {
        $root = $this->member();
        $this->place($root, $root->id);

        $right = $this->member();
        $this->place($right, $root->id, 'right');

        $this->assertSame('right', $right->treeNode->position);
        $this->assertTrue($root->treeNode->children()->where('position', 'right')->exists());
        $this->assertFalse($root->treeNode->children()->where('position', 'left')->exists());
    }

    public function test_invalid_position_throws(): void
    {
        $root = $this->member();
        $this->place($root, $root->id);
        $user = $this->member();

        $this->expectException(\InvalidArgumentException::class);
        $this->place($user, $root->id, 'middle');
    }

    public function test_placing_an_already_placed_user_throws(): void
    {
        $root = $this->member();
        $this->place($root, $root->id);
        $user = $this->member();
        $this->place($user, $root->id);

        $this->expectException(RuntimeException::class);
        $this->place($user, $root->id);
    }

    public function test_direct_and_total_downline_counts(): void
    {
        $root = $this->member();
        $this->place($root, $root->id);

        $a = $this->member();
        $b = $this->member();
        $c = $this->member();
        $this->place($a, $root->id);
        $this->place($b, $root->id);
        $this->place($c, $b->id);

        $service = app(MlmTreeService::class);

        $this->assertSame(2, $service->directDownline($root->id)->count());
        $this->assertSame(3, $service->allDownline($root->id)->count());
        $this->assertSame(0, $service->directDownline($a->id)->count());
        $this->assertSame(0, $service->allDownline($a->id)->count());
        $this->assertSame(1, $service->directDownline($b->id)->count());
    }

    public function test_all_downline_is_empty_for_unplaced_user(): void
    {
        $user = $this->member();

        $this->assertTrue(app(MlmTreeService::class)->allDownline($user->id)->isEmpty());
    }

    public function test_upline_returns_ancestors_by_level(): void
    {
        $root = $this->member();
        $this->place($root, $root->id);

        $m1 = $this->member();
        $m2 = $this->member();
        $m3 = $this->member();
        $this->place($m1, $root->id);
        $this->place($m2, $m1->id);
        $this->place($m3, $m2->id);

        $service = app(MlmTreeService::class);
        $deepNode = $m3->treeNode;

        $this->assertSame($m2->treeNode->id, $service->upline($deepNode, 1)?->id);
        $this->assertSame($m1->treeNode->id, $service->upline($deepNode, 2)?->id);
        $this->assertSame($root->treeNode->id, $service->upline($deepNode, 3)?->id);
        $this->assertNull($service->upline($deepNode, 4));
    }

    public function test_leg_volumes_include_activated_cv_volume(): void
    {
        $root = $this->member();
        $this->place($root, $root->id);

        $left = $this->member();
        $right = $this->member();
        $this->place($left, $root->id);
        $this->place($right, $root->id);

        $this->activateCv($left, 400);
        $this->activateCv($right, 800);

        $volumes = app(MlmTreeService::class)->legVolumes($root->id);

        $this->assertSame(400.0, $volumes['left']);
        $this->assertSame(800.0, $volumes['right']);
    }

    public function test_leg_volumes_ignore_inactive_purchases(): void
    {
        $root = $this->member();
        $this->place($root, $root->id);

        $left = $this->member();
        $this->place($left, $root->id);

        $cv = app(CvService::class);
        $cv->purchase($left->id, $this->tier(400)->id, 'bank');

        $volumes = app(MlmTreeService::class)->legVolumes($root->id);

        $this->assertSame(0.0, $volumes['left']);
    }

    public function test_leg_volumes_are_zero_for_unplaced_user(): void
    {
        $user = $this->member();

        $this->assertSame(['left' => 0.0, 'right' => 0.0], app(MlmTreeService::class)->legVolumes($user->id));
    }

    public function test_tree_array_builds_nested_structure(): void
    {
        $root = $this->member();
        $this->place($root, $root->id);

        $left = $this->member();
        $right = $this->member();
        $this->place($left, $root->id);
        $this->place($right, $root->id);
        $this->activateCv($left, 400);

        $tree = app(MlmTreeService::class)->treeArray($root->treeNode);

        $this->assertSame($root->id, $tree['id']);
        $this->assertSame($root->name, $tree['name']);
        $this->assertSame(0, $tree['level']);
        $this->assertSame(400.0, $tree['cv_volume']);
        $this->assertSame($left->id, $tree['left']['id']);
        $this->assertSame(400.0, $tree['left']['cv_volume']);
        $this->assertSame($right->id, $tree['right']['id']);
        $this->assertSame(0.0, $tree['right']['cv_volume']);
        $this->assertNull($tree['left']['left']);
    }

    public function test_resolve_placement_for_unplaced_sponsor_throws(): void
    {
        $user = $this->member();

        $this->expectException(RuntimeException::class);
        app(MlmTreeService::class)->resolvePlacement($user->id);
    }

    public function test_create_root_is_idempotent(): void
    {
        $root = $this->member();
        $service = app(MlmTreeService::class);

        $first = $service->createRoot($root);
        $second = $service->createRoot($root);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, MlmTree::count());
    }
}
