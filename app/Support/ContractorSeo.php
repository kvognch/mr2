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
        $name = self::displayName((string) ($data['short_name'] ?? $contractor?->short_name ?? ''));
        $titleName = $name !== '' ? $name : 'Организация';

        $categoryIds = self::relationIds($data, 'categories', $contractor, 'categories');
        $resourceIds = [
            ...self::relationIds($data, 'smrResourceTypes', $contractor, 'smrResourceTypes'),
            ...self::relationIds($data, 'pirResourceTypes', $contractor, 'pirResourceTypes'),
        ];
        $territoryIds = self::relationIds($data, 'territory_ids', $contractor, 'territories');

        $categories = self::namesInOrder(ContractorCategory::class, $categoryIds);
        $resources = self::namesInOrder(ResourceType::class, array_values(array_unique($resourceIds)));
        $region = self::regionPhrase($territoryIds);

        $categoriesText = implode(', ', $categories);
        $resourcesText = implode(', ', $resources);

        $activity = implode(' ', array_filter([
            $categoriesText,
            $resourcesText !== '' ? ($categoriesText !== '' ? 'по направлению ' : '').$resourcesText : '',
        ]));
        $titleActivity = implode(' ', array_filter([$activity, $region]));
        $title = implode(' — ', array_filter([$titleName, $titleActivity, 'МногоРесурсов']));

        $description = $titleName.($activity !== '' ? ' — '.$activity : '');
        $description = rtrim($description, '.!? ').'. Информация об организации, направлениях деятельности и территории работы на МногоРесурсов.';

        return [
            'seo_h1' => $name,
            'seo_title' => $title,
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
            'h1' => filled($contractor->seo_h1) ? self::displayName((string) $contractor->seo_h1) : $defaults['seo_h1'],
            'title' => filled($contractor->seo_title) ? self::plainText((string) $contractor->seo_title) : $defaults['seo_title'],
            'description' => filled($contractor->seo_description)
                ? self::plainText((string) $contractor->seo_description)
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
            if ($contractor->relationLoaded($relation)) {
                $value = $contractor->getRelation($relation)->pluck('id')->all();
            } else {
                $related = $contractor->{$relation}();
                $value = $related->pluck($related->getRelated()->getQualifiedKeyName())->all();
            }
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
            ->map(self::seoLabel(...))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /** @param array<int, int> $selectedIds */
    private static function regionPhrase(array $selectedIds): ?string
    {
        if ($selectedIds === []) {
            return null;
        }

        $region = GeoUnit::query()
            ->whereKey($selectedIds)
            ->orderByRaw('CASE WHEN admin_level > 0 THEN 0 ELSE 1 END')
            ->orderBy('admin_level')
            ->orderBy('id')
            ->first(['id', 'name', 'name_locative']);

        if ($region === null) {
            return null;
        }

        $phrase = self::plainText((string) $region->name_locative);
        $name = self::plainText((string) $region->name);

        return $phrase !== '' ? $phrase : ($name !== '' ? 'регион: '.$name : null);
    }

    private static function plainText(string $value): string
    {
        return trim(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    public static function displayName(string $value): string
    {
        $value = self::plainText($value);

        return preg_replace_callback(self::SIMPLE_QUOTES, static function (array $parts): string {
            [, $before, $open, $quoted, $close, $after] = $parts;
            $paired = ['"' => '"', '„' => '“', '“' => '”'];

            return $paired[$open] === $close ? $before.'«'.$quoted.'»'.$after : $parts[0];
        }, $value) ?? $value;
    }

    private const SIMPLE_QUOTES = '/^([^"«»„“”]*)(["„“])([^"«»„“”]+)(["“”])([^"«»„“”]*)$/u';

    public static function nameWarning(?string $value): ?string
    {
        $name = self::plainText($value ?? '');
        $warnings = [];

        if (preg_match('/\p{L}-\h+\p{L}|\p{L}\h+-\p{L}/u', $name)) {
            $warnings[] = 'Проверьте пробелы рядом с дефисом в названии.';
        }

        if (preg_match('/["«»„“”]/u', $name)
            && ! preg_match('/^[^"«»„“”]*«[^"«»„“”]+»[^"«»„“”]*$/u', $name)
            && self::displayName($name) === $name) {
            $warnings[] = 'Проверьте вложенные или непарные кавычки в названии.';
        }

        return $warnings !== [] ? implode(' ', $warnings) : null;
    }

    private static function seoLabel(string $name): string
    {
        $name = self::plainText($name);

        $singularCategories = [
            'Гарантирующие поставщики' => 'гарантирующий поставщик',
            'Ресурсо-снабжающие организации' => 'ресурсо-снабжающая организация',
            'Подрядчики' => 'подрядчик',
        ];

        return $singularCategories[$name] ?? preg_replace_callback(
            '/^(?:Газоснабжение|Наружная|Наружный|Теплоснабжение|Электроснабжение|Гарантирующий|Ресурсо-снабжающая|Ресурсоснабжающая|Подрядчик|Проектирование|Проектировщик(?:и)?|Строительство|Строитель(?:и)?|Монтаж|Монтажные|Инженерные|Изыскания|Обслуживание|Газ|Вода|Канализация|Отопление|Электричество)(?=\s|$)/u',
            static fn (array $match): string => mb_strtolower($match[0]),
            $name,
        );
    }
}
