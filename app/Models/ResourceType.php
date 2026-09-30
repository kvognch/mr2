<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResourceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'abbreviation',
        'icon',
        'parent_id',
        'intro_text',
        'seo_text',
        'use_rich_editor',
        'h1',
        'meta_title',
        'meta_description',
        'slug',
    ];

    protected $casts = [
        'use_rich_editor' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $resourceType): void {
            $resourceType->slug = static::uniqueSlug(
                filled($resourceType->slug) ? (string) $resourceType->slug : (string) $resourceType->name,
                $resourceType->id,
            );

            static::ensureParentIsNotDescendant($resourceType);
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /** @return array<int> */
    public function descendantIds(bool $includeSelf = true): array
    {
        $ids = $includeSelf ? [(int) $this->getKey()] : [];
        $pendingIds = [(int) $this->getKey()];
        $knownIds = array_fill_keys($pendingIds, true);

        while ($pendingIds !== []) {
            $childIds = static::query()
                ->whereIn('parent_id', $pendingIds)
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->reject(fn (int $id): bool => isset($knownIds[$id]))
                ->values()
                ->all();

            foreach ($childIds as $childId) {
                $knownIds[$childId] = true;
            }

            $ids = [...$ids, ...$childIds];
            $pendingIds = $childIds;
        }

        return array_values(array_unique($ids));
    }

    public static function uniqueSlug(string $value, ?int $exceptId = null): string
    {
        $knownSlugs = [
            'Газоснабжение' => 'gas-supply',
            'Наружная канализация' => 'external-sewerage',
            'Наружный водопровод' => 'external-water-supply',
            'Теплоснабжение' => 'heat-supply',
            'Электроснабжение' => 'electricity',
        ];
        $base = $knownSlugs[$value] ?? Str::slug(Str::transliterate($value));
        $base = $base !== '' ? $base : 'direction';
        $slug = $base;
        $suffix = 1;

        while (static::query()
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private static function ensureParentIsNotDescendant(self $resourceType): void
    {
        $parentId = $resourceType->parent_id !== null ? (int) $resourceType->parent_id : null;
        $visited = [];

        while ($parentId !== null && ! isset($visited[$parentId])) {
            if ($resourceType->exists && $parentId === (int) $resourceType->getKey()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'parent_id' => 'Нельзя выбрать дочерний вид родительским.',
                ]);
            }

            $visited[$parentId] = true;
            $parentId = static::query()->whereKey($parentId)->value('parent_id');
            $parentId = $parentId !== null ? (int) $parentId : null;
        }
    }

    public function getIconUrlAttribute(): ?string
    {
        return $this->resolveIconUrl();
    }

    public function resolveIconUrl(bool $small = false): ?string
    {
        if (filled($this->icon)) {
            return str_starts_with($this->icon, 'assets/')
                ? asset($this->icon)
                : Storage::disk('public')->url($this->icon);
        }

        $fallbackPath = static::fallbackIconPath($this->abbreviation, $small);

        return $fallbackPath ? asset($fallbackPath) : null;
    }

    public static function fallbackIconPath(?string $abbreviation, bool $small = false): ?string
    {
        return match (mb_strtoupper(trim((string) $abbreviation))) {
            'ГС' => $small ? 'assets/svgs/gas-pipe-sm.svg' : 'assets/svgs/gas-pipe.svg',
            'НВ' => $small ? 'assets/svgs/water-sm.svg' : 'assets/svgs/water.svg',
            'НК' => $small ? 'assets/svgs/pipe-thin-sm.svg' : 'assets/svgs/pipe-thin.svg',
            'ТС' => $small ? 'assets/svgs/heating-square-sm.svg' : 'assets/svgs/heating-square.svg',
            'ЭС' => $small ? 'assets/svgs/electricity-sm.svg' : 'assets/svgs/electricity.svg',
            default => null,
        };
    }

    public function contractorsSmr(): BelongsToMany
    {
        return $this->belongsToMany(Contractor::class, 'contractor_smr_resource_type')->withTimestamps();
    }

    public function contractorsPir(): BelongsToMany
    {
        return $this->belongsToMany(Contractor::class, 'contractor_pir_resource_type')->withTimestamps();
    }
}
