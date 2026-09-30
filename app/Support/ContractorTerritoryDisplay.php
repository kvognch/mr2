<?php

namespace App\Support;

use App\Models\GeoUnit;
use Illuminate\Support\Collection;

class ContractorTerritoryDisplay
{
    /**
     * Keep only assigned territories that do not have another assigned ancestor.
     *
     * @param  Collection<int, Collection<int, GeoUnit>>  $territoryGroups  Groups keyed by contractor ID.
     * @return Collection<int, Collection<int, GeoUnit>>
     */
    public static function forAssignments(Collection $territoryGroups): Collection
    {
        $territoryGroups = $territoryGroups->map(fn (Collection $territories): Collection => $territories->values());

        if ($territoryGroups->isEmpty()) {
            return $territoryGroups;
        }

        $selectedIdsByOwner = $territoryGroups->map(fn (Collection $territories): array => $territories
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all());
        $unitsById = $territoryGroups
            ->flatten(1)
            ->keyBy(fn (GeoUnit $territory): int => (int) $territory->id)
            ->all();
        $pendingParentIds = $territoryGroups
            ->flatten(1)
            ->pluck('parent_id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->reject(fn (int $id): bool => isset($unitsById[$id]))
            ->unique()
            ->values()
            ->all();

        while ($pendingParentIds !== []) {
            $parents = GeoUnit::query()
                ->whereIn('id', $pendingParentIds)
                ->get(['id', 'parent_id']);
            $nextParentIds = [];

            foreach ($parents as $parent) {
                $parentId = (int) $parent->id;
                $unitsById[$parentId] = $parent;

                if ($parent->parent_id !== null) {
                    $nextParentId = (int) $parent->parent_id;

                    if (! isset($unitsById[$nextParentId])) {
                        $nextParentIds[] = $nextParentId;
                    }
                }
            }

            $pendingParentIds = array_values(array_unique($nextParentIds));
        }

        return $territoryGroups->map(function (Collection $territories, int|string $ownerId) use ($selectedIdsByOwner, $unitsById): Collection {
            $selectedIdSet = array_fill_keys($selectedIdsByOwner->get($ownerId, []), true);

            return $territories
                ->filter(function (GeoUnit $territory) use ($selectedIdSet, $unitsById): bool {
                    $parentId = $territory->parent_id !== null ? (int) $territory->parent_id : null;
                    $visited = [];

                    while ($parentId !== null && ! isset($visited[$parentId])) {
                        if (isset($selectedIdSet[$parentId])) {
                            return false;
                        }

                        $visited[$parentId] = true;
                        $parent = $unitsById[$parentId] ?? null;
                        $parentId = $parent?->parent_id !== null ? (int) $parent->parent_id : null;
                    }

                    return true;
                })
                ->values();
        });
    }
}
