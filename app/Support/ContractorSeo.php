<?php

namespace App\Support;

use App\Models\Contractor;
use App\Models\ContractorCategory;
use App\Models\GeoUnit;
use App\Models\ResourceType;

final class ContractorSeo
{
    /**
     * @return array{seo_h1: string, seo_title: string, seo_description: string}
     */
    public static function defaults(Contractor $contractor): array
    {
        return self::defaultsFromData([], $contractor);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{seo_h1: string, seo_title: string, seo_description: string}
     */
    public static function defaultsFromData(array $data, ?Contractor $contractor = null): array
    {
        $name = trim((string) ($data['short_name'] ?? $contractor?->short_name ?? ''));
        $titleName = $name !== '' ? $name : 'Организация';

        $categoryIds = self::relationIds($data, 'categories', $contractor, 'categories');
        $resourceIds = [
            ...self::relationIds($data, 'smrResourceTypes', $contractor, 'smrResourceTypes'),
            ...self::relationIds($data, 'pirResourceTypes', $contractor, 'pirResourceTypes'),
        ];
        $territoryIds = self::relationIds($data, 'territory_ids', $contractor, 'territories');

        $categories = self::namesInOrder(ContractorCategory::class, $categoryIds);
        $resources = self::namesInOrder(ResourceType::class, array_values(array_unique($resourceIds)));
        $regions = self::regionNames($territoryIds);

        $categoriesText = implode(', ', $categories) ?: 'организация';
        $resourcesText = implode(', ', $resources) ?: 'инженерная инфраструктура';
        $regionsText = implode(', ', $regions) ?: 'указанная территория';
        $titleBody = sprintf(
            '%s - %s по направлению %s в %s',
            $titleName,
            $categoriesText,
            $resourcesText,
            $regionsText,
        );
        $settings = OrganizationSeoSettings::all();
        $description = implode(' ', array_filter([
            trim((string) ($settings['description_prefix'] ?? '')),
            $titleBody,
            trim((string) ($settings['description_suffix'] ?? '')),
        ], static fn (string $value): bool => $value !== ''));

        return [
            'seo_h1' => $name,
            'seo_title' => $titleBody.' – Многоресурсов',
            'seo_description' => $description,
        ];
    }

    /**
     * @return array{h1: string, title: string, description: string}
     */
    public static function resolve(Contractor $contractor): array
    {
        $defaults = self::defaults($contractor);

        return [
            'h1' => filled($contractor->seo_h1) ? (string) $contractor->seo_h1 : $defaults['seo_h1'],
            'title' => filled($contractor->seo_title) ? (string) $contractor->seo_title : $defaults['seo_title'],
            'description' => filled($contractor->seo_description)
                ? (string) $contractor->seo_description
                : $defaults['seo_description'],
        ];
    }

    /**
     * @return array{seo_h1: string, seo_title: string, seo_description: string, slug: string|null}
     */
    public static function formValues(Contractor $contractor): array
    {
        $defaults = self::defaults($contractor);

        return [
            'seo_h1' => filled($contractor->seo_h1) ? (string) $contractor->seo_h1 : $defaults['seo_h1'],
            'seo_title' => filled($contractor->seo_title) ? (string) $contractor->seo_title : $defaults['seo_title'],
            'seo_description' => filled($contractor->seo_description)
                ? (string) $contractor->seo_description
                : $defaults['seo_description'],
            'slug' => filled($contractor->slug)
                ? (string) $contractor->slug
                : Contractor::generateUniqueSlug((string) $contractor->short_name, $contractor->id),
        ];
    }

    /**
     * Keep generated values virtual in storage and persist only manual overrides.
     * This lets changes to categories, resources or territories update defaults.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function prepareForSave(array $data, ?Contractor $contractor = null): array
    {
        $defaults = self::defaultsFromData($data, $contractor);

        foreach (['seo_h1', 'seo_title', 'seo_description'] as $field) {
            $value = trim((string) ($data[$field] ?? ''));

            $data[$field] = $value === '' || $value === trim($defaults[$field])
                ? null
                : $value;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int>
     */
    private static function relationIds(array $data, string $key, ?Contractor $contractor, string $relation): array
    {
        if (array_key_exists($key, $data)) {
            $value = $data[$key];
        } elseif ($contractor !== null) {
            $value = $contractor->{$relation}->pluck('id')->all();
        } else {
            $value = [];
        }

        return collect(is_array($value) ? $value : [$value])
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  class-string<ContractorCategory|ResourceType>  $model
     * @param  array<int>  $ids
     * @return array<int, string>
     */
    private static function namesInOrder(string $model, array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $names = $model::query()
            ->whereIn('id', $ids)
            ->pluck('name', 'id');

        return collect($ids)
            ->map(fn (int $id): ?string => isset($names[$id]) ? (string) $names[$id] : null)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int>  $selectedIds
     * @return array<int, string>
     */
    private static function regionNames(array $selectedIds): array
    {
        if ($selectedIds === []) {
            return [];
        }

        $unitsById = [];
        $pendingIds = array_values(array_unique($selectedIds));

        while ($pendingIds !== []) {
            $units = GeoUnit::query()
                ->select(['id', 'name', 'parent_id', 'admin_level'])
                ->whereIn('id', $pendingIds)
                ->get();
            $nextPendingIds = [];

            foreach ($units as $unit) {
                $id = (int) $unit->id;
                $unitsById[$id] = $unit;

                if ($unit->parent_id !== null && ! isset($unitsById[(int) $unit->parent_id])) {
                    $nextPendingIds[] = (int) $unit->parent_id;
                }
            }

            $pendingIds = array_values(array_unique($nextPendingIds));
        }

        $regions = [];

        foreach ($selectedIds as $selectedId) {
            $currentId = (int) $selectedId;
            $visited = [];

            while ($currentId > 0 && ! isset($visited[$currentId])) {
                $visited[$currentId] = true;
                $unit = $unitsById[$currentId] ?? null;

                if ($unit === null) {
                    break;
                }

                if ((int) $unit->admin_level === 4 || $unit->parent_id === null) {
                    $name = trim((string) $unit->name);

                    if ($name !== '') {
                        $regions[] = self::formatRegionForTitle($name);
                    }

                    break;
                }

                $currentId = (int) $unit->parent_id;
            }
        }

        return array_values(array_unique($regions));
    }

    private static function formatRegionForTitle(string $name): string
    {
        if (preg_match('/^(.+ая) автономная область$/u', $name, $matches) === 1) {
            return mb_substr($matches[1], 0, -2).'ой автономной области';
        }

        if (preg_match('/^(.+ая) область$/u', $name, $matches) === 1) {
            return mb_substr($matches[1], 0, -2).'ой области';
        }

        if (preg_match('/^(.+ий) автономный округ$/u', $name, $matches) === 1) {
            return mb_substr($matches[1], 0, -2).'ом автономном округе';
        }

        if (str_starts_with($name, 'Республика ')) {
            return 'Республике '.mb_substr($name, mb_strlen('Республика '));
        }

        return match ($name) {
            'Карелия' => 'Карелии',
            default => $name,
        };
    }
}
