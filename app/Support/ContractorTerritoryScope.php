<?php

namespace App\Support;

final class ContractorTerritoryScope
{
    /**
     * Build the search scope for assigned territories without expanding ancestors back into their other descendants.
     *
     * @param  array<int, int>  $assignedTerritoryIds
     * @param  array<int, int|null>  $parentIds
     * @param  array<int, array<int>>  $descendantsByTerritory
     * @return array<int, int>
     */
    public static function forAssignments(
        array $assignedTerritoryIds,
        array $parentIds,
        array $descendantsByTerritory,
    ): array {
        $scopeIds = [];

        foreach ($assignedTerritoryIds as $assignedTerritoryId) {
            $territoryId = (int) $assignedTerritoryId;

            if ($territoryId <= 0) {
                continue;
            }

            $scopeIds[$territoryId] = true;

            foreach ($descendantsByTerritory[$territoryId] ?? [] as $descendantId) {
                $descendantId = (int) $descendantId;

                if ($descendantId > 0) {
                    $scopeIds[$descendantId] = true;
                }
            }

            $visited = [$territoryId => true];
            $parentId = $parentIds[$territoryId] ?? null;

            while ($parentId !== null) {
                $parentId = (int) $parentId;

                if ($parentId <= 0 || isset($visited[$parentId])) {
                    break;
                }

                $visited[$parentId] = true;
                $scopeIds[$parentId] = true;
                $parentId = $parentIds[$parentId] ?? null;
            }
        }

        return array_map('intval', array_keys($scopeIds));
    }
}
