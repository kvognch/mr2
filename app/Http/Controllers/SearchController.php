<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use App\Models\ContractorCategory;
use App\Models\GeoUnit;
use App\Models\ResourceType;
use App\Support\HomepageSettings;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(): View
    {
        $settings = HomepageSettings::all();

        $contractors = Contractor::query()
            ->with(['categories:id,name', 'rating:id,name,sort_order', 'territories:id,name', 'smrResourceTypes:id', 'pirResourceTypes:id'])
            ->where('status', 'approved')
            ->orderBy('short_name')
            ->get();

        $contractorItems = $contractors->map(function (Contractor $contractor): array {
            return [
                'id' => $contractor->id,
                'short_name' => $contractor->short_name,
                'slug' => $contractor->slug,
                'rating_name' => (string) ($contractor->rating?->name ?? ''),
                'rating_sort_order' => (int) ($contractor->rating?->sort_order ?? 9999),
                'category_ids' => $contractor->categories->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
                'territory_ids' => $contractor->territories->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
                'smr_resource_ids' => $contractor->smrResourceTypes->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
                'pir_resource_ids' => $contractor->pirResourceTypes->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
                'business_segments' => collect($contractor->business_segments ?? [])
                    ->map(fn ($segment) => (string) $segment)
                    ->filter()
                    ->values()
                    ->all(),
            ];
        })->values();

        $categories = ContractorCategory::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (ContractorCategory $category): array => [
                'id' => (int) $category->id,
                'name' => $category->name,
            ])
            ->values();

        $resourceTypes = ResourceType::query()
            ->orderBy('name')
            ->get(['id', 'name', 'abbreviation'])
            ->map(fn (ResourceType $resourceType): array => [
                'id' => (int) $resourceType->id,
                'name' => $resourceType->name,
                'abbreviation' => (string) $resourceType->abbreviation,
            ])
            ->values();

        $territoryTree = $this->buildActiveTerritoryTree();
        $territoryDescendants = $this->buildDescendantsMap($territoryTree);

        return view('search.index', [
            'settings' => $settings,
            'contractors' => $contractorItems->all(),
            'categories' => $categories->all(),
            'resourceTypes' => $resourceTypes->all(),
            'territoryTree' => $territoryTree,
            'territoryDescendants' => $territoryDescendants,
            'yandexMapsApiKey' => (string) env('YANDEX_MAPS_API_KEY', ''),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildActiveTerritoryTree(): array
    {
        $activeUnits = GeoUnit::query()
            ->active()
            ->where('admin_level', '>=', 4)
            ->get(['id', 'parent_id', 'name', 'admin_level']);

        if ($activeUnits->isEmpty()) {
            return [];
        }

        $requiredIds = [];
        foreach ($activeUnits as $unit) {
            $requiredIds[(int) $unit->id] = true;
        }

        $pendingParentIds = $activeUnits
            ->pluck('parent_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        while ($pendingParentIds !== []) {
            $parents = GeoUnit::query()
                ->whereIn('id', $pendingParentIds)
                ->where('admin_level', '>=', 4)
                ->get(['id', 'parent_id']);

            $nextParentIds = [];
            foreach ($parents as $parent) {
                $id = (int) $parent->id;
                if (! isset($requiredIds[$id])) {
                    $requiredIds[$id] = true;
                }

                if ($parent->parent_id !== null) {
                    $parentId = (int) $parent->parent_id;
                    if (! isset($requiredIds[$parentId])) {
                        $nextParentIds[] = $parentId;
                    }
                }
            }

            $pendingParentIds = array_values(array_unique($nextParentIds));
        }

        $units = GeoUnit::query()
            ->whereIn('id', array_keys($requiredIds))
            ->where('admin_level', '>=', 4)
            ->orderBy('admin_level')
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name', 'admin_level', 'resource_schemes']);

        $byId = [];
        foreach ($units as $unit) {
            $byId[(int) $unit->id] = [
                'id' => (int) $unit->id,
                'parent_id' => $unit->parent_id !== null ? (int) $unit->parent_id : null,
                'name' => $unit->name,
                'level' => (int) $unit->admin_level,
                'resource_schemes' => collect($unit->resource_schemes ?? [])
                    ->map(function ($scheme): ?array {
                        $title = trim((string) data_get($scheme, 'title', ''));
                        $path = trim((string) data_get($scheme, 'file', ''));

                        if ($title === '' || $path === '') {
                            return null;
                        }

                        return [
                            'title' => $title,
                            'url' => Storage::disk('public')->url($path),
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all(),
                'children' => [],
            ];
        }

        $roots = [];
        foreach ($byId as $id => &$node) {
            $parentId = $node['parent_id'];
            if ($parentId !== null && isset($byId[$parentId])) {
                $byId[$parentId]['children'][] = &$node;
                continue;
            }

            if ($node['level'] === 4) {
                $roots[] = &$node;
            }
        }
        unset($node);

        $this->sortTreeNodes($roots);

        return $roots;
    }

    /**
     * @param array<int, array<string, mixed>> $nodes
     */
    private function sortTreeNodes(array &$nodes): void
    {
        usort($nodes, fn (array $a, array $b): int => strcmp((string) $a['name'], (string) $b['name']));

        foreach ($nodes as &$node) {
            if (! empty($node['children'])) {
                $this->sortTreeNodes($node['children']);
            }
        }
        unset($node);
    }

    /**
     * @param array<int, array<string, mixed>> $tree
     * @return array<int, array<int>>
     */
    private function buildDescendantsMap(array $tree): array
    {
        $map = [];

        $walk = function (array $node) use (&$walk, &$map): array {
            $descendants = [];

            foreach ($node['children'] as $child) {
                $descendants[] = (int) $child['id'];
                $descendants = array_merge($descendants, $walk($child));
            }

            $map[(int) $node['id']] = array_values(array_unique($descendants));

            return $descendants;
        };

        foreach ($tree as $node) {
            $walk($node);
        }

        return $map;
    }
}
