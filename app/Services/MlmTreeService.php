<?php

namespace App\Services;

use App\Models\CvPurchase;
use App\Models\MlmTree;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MlmTreeService
{
    public function place(User $user, int $sponsorId, ?string $position = null): MlmTree
    {
        return DB::transaction(function () use ($user, $sponsorId, $position) {
            if ($user->treeNode) {
                throw new \RuntimeException('User is already placed in the tree.');
            }

            if ($user->id === $sponsorId) {
                $node = $this->createRoot($user);
                $user->setRelation('treeNode', $node);

                return $node;
            }

            $sponsorNode = MlmTree::where('user_id', $sponsorId)->first()
                ?? $this->createRoot(User::findOrFail($sponsorId));

            [$parentNode, $finalPosition] = $this->resolvePlacementNode($sponsorNode, $position);

            $level = $parentNode->level + 1;

            $node = MlmTree::create([
                'user_id' => $user->id,
                'parent_id' => $parentNode->id,
                'position' => $finalPosition,
                'level' => $level,
                'path' => ($parentNode->path ?? '/').$parentNode->id.'/',
            ]);

            $user->setRelation('treeNode', $node);

            return $node;
        });
    }

    public function createRoot(User $user): MlmTree
    {
        $node = MlmTree::firstOrCreate(
            ['user_id' => $user->id],
            ['parent_id' => null, 'position' => null, 'level' => 0, 'path' => null]
        );

        $user->setRelation('treeNode', $node);

        return $node;
    }

    /**
     * Resolve where a new member should be placed under a sponsor node.
     *
     * @return array{0: MlmTree, 1: string}
     */
    public function resolvePlacementNode(MlmTree $root, ?string $requestedPosition = null): array
    {
        if ($requestedPosition) {
            if (! in_array($requestedPosition, ['left', 'right'], true)) {
                throw new \InvalidArgumentException('Position must be left or right.');
            }

            if (! $root->children()->where('position', $requestedPosition)->exists()) {
                return [$root, $requestedPosition];
            }
        }

        if (! $root->children()->where('position', 'left')->exists()) {
            return [$root, 'left'];
        }

        if (! $root->children()->where('position', 'right')->exists()) {
            return [$root, 'right'];
        }

        return $this->findBalancedPlacement($root);
    }

    /**
     * Resolve where a new member should be placed under a sponsor.
     *
     * @return array{0: MlmTree, 1: string}
     */
    public function resolvePlacement(int $sponsorId, ?string $requestedPosition = null): array
    {
        $root = MlmTree::where('user_id', $sponsorId)->first();

        if (! $root) {
            throw new \RuntimeException('Sponsor is not placed in the tree yet.');
        }

        return $this->resolvePlacementNode($root, $requestedPosition);
    }

    /**
     * Recursively walk the tree and pick the leg with lower volume
     * to keep the binary tree balanced.
     *
     * @return array{0: MlmTree, 1: string}
     */
    private function findBalancedPlacement(MlmTree $node): array
    {
        $leftVolume = $this->nodeLegVolume($node, 'left');
        $rightVolume = $this->nodeLegVolume($node, 'right');

        $preferredLeg = $leftVolume <= $rightVolume ? 'left' : 'right';
        $child = $node->children()->where('position', $preferredLeg)->first();

        if (! $child) {
            return [$node, $preferredLeg];
        }

        if (! $child->children()->exists()) {
            return [$child, 'left'];
        }

        return $this->findBalancedPlacement($child);
    }

    public function nodeLegVolume(MlmTree $node, string $position): float
    {
        $child = $node->children()->where('position', $position)->first();

        if (! $child) {
            return 0.0;
        }

        return $this->subtreeVolume($child);
    }

    /**
     * Total active CV volume for every node in a subtree.
     */
    public function subtreeVolume(MlmTree $node): float
    {
        $subtreeUserIds = $this->subtreeIds($node);

        if (empty($subtreeUserIds)) {
            return 0.0;
        }

        return (float) CvPurchase::query()
            ->whereIn('user_id', $subtreeUserIds)
            ->where('status', 'activated')
            ->sum('amount');
    }

    /**
     * All user ids under a node (including the node itself).
     */
    public function subtreeIds(MlmTree $node): array
    {
        $pattern = rtrim(($node->path ?? '/').$node->id.'/', '/').'%';

        return MlmTree::query()
            ->where('path', 'like', $pattern)
            ->orWhere('id', $node->id)
            ->pluck('user_id')
            ->all();
    }

    public function directDownline(int $userId): Collection
    {
        return MlmTree::query()
            ->where('parent_id', MlmTree::where('user_id', $userId)->value('id'))
            ->with('user')
            ->get();
    }

    public function allDownline(int $userId): Collection
    {
        $node = MlmTree::where('user_id', $userId)->first();

        if (! $node) {
            return collect();
        }

        $pattern = rtrim(($node->path ?? '/').$node->id.'/', '/').'%';

        return MlmTree::query()
            ->where('path', 'like', $pattern)
            ->with('user')
            ->get();
    }

    /**
     * Upline node at a given sponsorship/binary level (1 = parent).
     */
    public function upline(MlmTree $node, int $level): ?MlmTree
    {
        if ($level < 1) {
            return null;
        }

        $path = trim($node->path ?? '', '/');
        $segments = array_filter(explode('/', $path));

        $target = $segments[count($segments) - $level] ?? null;

        return $target ? MlmTree::find((int) $target) : null;
    }

    public function legVolumes(int $userId): array
    {
        $node = MlmTree::where('user_id', $userId)->first();

        if (! $node) {
            return ['left' => 0.0, 'right' => 0.0];
        }

        return [
            'left' => $this->nodeLegVolume($node, 'left'),
            'right' => $this->nodeLegVolume($node, 'right'),
        ];
    }

    public function treeArray(MlmTree $node): array
    {
        $left = $node->children()->where('position', 'left')->first();
        $right = $node->children()->where('position', 'right')->first();

        return [
            'id' => $node->user_id,
            'name' => $node->user->name,
            'unique_id' => $node->user->unique_id,
            'level' => $node->level,
            'cv_volume' => $this->subtreeVolume($node),
            'left' => $left ? $this->treeArray($left) : null,
            'right' => $right ? $this->treeArray($right) : null,
        ];
    }
}
