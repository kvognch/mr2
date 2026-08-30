<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GeoUnit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeoController extends Controller
{
    public function tree(Request $request): JsonResponse
    {
        $parentId = $request->query('parent_id');

        $items = GeoUnit::query()
            ->when($parentId !== null, fn ($q) => $q->where('parent_id', (int) $parentId), fn ($q) => $q->whereNull('parent_id'))
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name', 'is_active']);

        return response()->json(['data' => $items]);
    }

    public function unit(int $id): JsonResponse
    {
        $unit = GeoUnit::query()
            ->with('children:id,parent_id,name,is_active')
            ->findOrFail($id);

        return response()->json(['data' => $unit]);
    }

    public function mapFeatures(Request $request): JsonResponse
    {
        $level = $request->query('level');
        $regionId = $request->query('region_id');
        $regionScopeIds = null;

        if ($regionId !== null) {
            $regionId = filter_var($regionId, FILTER_VALIDATE_INT);

            if ($regionId === false || $regionId <= 0) {
                return response()->json(['data' => []]);
            }

            $regionScopeIds = $this->getRegionScopeIds((int) $regionId);

            if ($regionScopeIds === []) {
                return response()->json(['data' => []]);
            }
        }

        if ($regionScopeIds !== null) {
            $scopedUnits = GeoUnit::query()
                ->whereIn('id', $regionScopeIds)
                ->get([
                    'id',
                    'parent_id',
                    'name',
                    'level',
                    'admin_level',
                    'geometry_yandex',
                    'bbox_min_lat',
                    'bbox_min_lon',
                    'bbox_max_lat',
                    'bbox_max_lon',
                    'is_active',
                ]);
            $unitsById = $scopedUnits->keyBy(fn (GeoUnit $unit): int => (int) $unit->id);
            $activeUnits = $scopedUnits->filter(fn (GeoUnit $unit): bool => (bool) $unit->is_active);
            $parentIdsWithActiveDescendants = [];

            foreach ($activeUnits as $unit) {
                $parentId = $unit->parent_id !== null ? (int) $unit->parent_id : null;
                $visitedParentIds = [];

                while (
                    $parentId !== null
                    && $unitsById->has($parentId)
                    && ! isset($visitedParentIds[$parentId])
                ) {
                    $visitedParentIds[$parentId] = true;
                    $parentIdsWithActiveDescendants[$parentId] = true;
                    $parent = $unitsById->get($parentId);
                    $parentId = $parent->parent_id !== null ? (int) $parent->parent_id : null;
                }
            }

            $units = $activeUnits
                ->filter(fn (GeoUnit $unit): bool => ! isset($parentIdsWithActiveDescendants[(int) $unit->id]))
                ->filter(fn (GeoUnit $unit): bool => $unit->geometry_yandex !== null)
                ->when($level, fn ($items) => $items->where('level', $level));
        } else {
            $units = GeoUnit::query()
                ->active()
                ->where(function (Builder $query): void {
                    $query
                        ->whereNull('admin_level')
                        ->orWhere('admin_level', '>', 4);
                })
                ->whereNotNull('geometry_yandex')
                ->when($level, fn ($q) => $q->where('level', $level))
                ->get();
        }

        $items = [];

        foreach ($units as $unit) {
            $items[] = [
                'id' => $unit->id,
                'name' => $unit->name,
                'level' => $unit->level,
                'admin_level' => $unit->admin_level,
                'geometry' => $unit->geometry_yandex,
                'bbox' => [
                    'min_lat' => $unit->bbox_min_lat,
                    'min_lon' => $unit->bbox_min_lon,
                    'max_lat' => $unit->bbox_max_lat,
                    'max_lon' => $unit->bbox_max_lon,
                ],
            ];
        }

        return response()->json(['data' => $items]);
    }

    /**
     * @return array<int>
     */
    private function getRegionScopeIds(int $regionId): array
    {
        $units = GeoUnit::query()
            ->where('admin_level', '>=', 4)
            ->get(['id', 'parent_id', 'admin_level']);

        $region = $units->first(
            fn (GeoUnit $unit): bool => (int) $unit->id === $regionId && (int) $unit->admin_level === 4,
        );

        if ($region === null) {
            return [];
        }

        $childrenByParent = [];
        foreach ($units as $unit) {
            if ($unit->parent_id === null) {
                continue;
            }

            $childrenByParent[(int) $unit->parent_id][] = (int) $unit->id;
        }

        $scopeIds = [$regionId];
        $pendingIds = [$regionId];

        while ($pendingIds !== []) {
            $parentId = array_shift($pendingIds);

            foreach ($childrenByParent[$parentId] ?? [] as $childId) {
                if (in_array($childId, $scopeIds, true)) {
                    continue;
                }

                $scopeIds[] = $childId;
                $pendingIds[] = $childId;
            }
        }

        return $scopeIds;
    }
}
