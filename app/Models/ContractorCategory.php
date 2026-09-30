<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ContractorCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
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
        static::saving(function (self $category): void {
            $category->slug = static::uniqueSlug(
                filled($category->slug) ? (string) $category->slug : (string) $category->name,
                $category->id,
            );

            static::ensureParentIsNotDescendant($category);
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

    public function contractors(): BelongsToMany
    {
        return $this->belongsToMany(Contractor::class, 'contractor_contractor_category')->withTimestamps();
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
            'Гарантирующий поставщик' => 'guaranteeing-supplier',
            'Ресурсо-снабжающая организация' => 'resource-supplying-organization',
            'Подрядчик' => 'contractors',
        ];
        $base = $knownSlugs[$value] ?? Str::slug(Str::transliterate($value));
        $base = $base !== '' ? $base : 'category';
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

    private static function ensureParentIsNotDescendant(self $category): void
    {
        $parentId = $category->parent_id !== null ? (int) $category->parent_id : null;
        $visited = [];

        while ($parentId !== null && ! isset($visited[$parentId])) {
            if ($category->exists && $parentId === (int) $category->getKey()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'parent_id' => 'Нельзя выбрать дочернюю категорию родительской.',
                ]);
            }

            $visited[$parentId] = true;
            $parentId = static::query()->whereKey($parentId)->value('parent_id');
            $parentId = $parentId !== null ? (int) $parentId : null;
        }
    }
}
