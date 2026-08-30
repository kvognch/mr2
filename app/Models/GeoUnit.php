<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class GeoUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'source',
        'source_id',
        'parent_source_id',
        'name',
        'normalized_name',
        'admin_level',
        'level',
        'boundary',
        'geometry_osm',
        'geometry_yandex',
        'center_lat',
        'center_lon',
        'bbox_min_lat',
        'bbox_min_lon',
        'bbox_max_lat',
        'bbox_max_lon',
        'is_active',
        'resource_schemes',
        'properties',
        'meta',
    ];

    protected $casts = [
        'admin_level' => 'integer',
        'geometry_osm' => 'array',
        'geometry_yandex' => 'array',
        'is_active' => 'boolean',
        'resource_schemes' => 'array',
        'properties' => 'array',
        'meta' => 'array',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Update this unit and all of its descendants in one transaction.
     */
    public function setActiveWithDescendants(bool $isActive): void
    {
        self::setActiveForIdsWithDescendants([$this->getKey()], $isActive);

        $this->is_active = $isActive;
    }

    public function setDescendantsActive(bool $isActive): void
    {
        self::setActiveForDescendants([$this->getKey()], $isActive);
    }

    public function hasActiveDescendants(): bool
    {
        $descendantIds = self::getDescendantIds([(int) $this->getKey()]);

        if ($descendantIds === []) {
            return false;
        }

        return self::query()
            ->whereIn('id', $descendantIds)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * @param  iterable<int|string>  $ids
     */
    public static function setActiveForIdsWithDescendants(iterable $ids, bool $isActive): void
    {
        $rootIds = self::normalizeIds($ids);

        if ($rootIds === []) {
            return;
        }

        DB::transaction(function () use ($rootIds, $isActive): void {
            self::query()
                ->whereIn('id', [...$rootIds, ...self::getDescendantIds($rootIds)])
                ->update(['is_active' => $isActive]);
        });
    }

    /**
     * @param  iterable<int|string>  $ids
     */
    public static function setActiveForDescendants(iterable $ids, bool $isActive): void
    {
        $rootIds = self::normalizeIds($ids);

        if ($rootIds === []) {
            return;
        }

        DB::transaction(function () use ($rootIds, $isActive): void {
            $descendantIds = self::getDescendantIds($rootIds);

            if ($descendantIds === []) {
                return;
            }

            self::query()
                ->whereIn('id', $descendantIds)
                ->update(['is_active' => $isActive]);
        });
    }

    /**
     * @param  iterable<int|string>  $ids
     * @return array<int>
     */
    private static function normalizeIds(iterable $ids): array
    {
        return collect($ids)
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int>  $rootIds
     * @return array<int>
     */
    private static function getDescendantIds(array $rootIds): array
    {
        $knownIds = array_fill_keys($rootIds, true);
        $pendingIds = $rootIds;
        $descendantIds = [];

        while ($pendingIds !== []) {
            $childIds = self::query()
                ->whereIn('parent_id', $pendingIds)
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->reject(fn (int $id): bool => isset($knownIds[$id]))
                ->unique()
                ->values()
                ->all();

            if ($childIds === []) {
                break;
            }

            foreach ($childIds as $childId) {
                $knownIds[$childId] = true;
            }

            $descendantIds = [...$descendantIds, ...$childIds];
            $pendingIds = $childIds;
        }

        return $descendantIds;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
