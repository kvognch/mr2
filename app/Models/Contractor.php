<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Contractor extends Model
{
    use HasFactory;

    protected $fillable = [
        'short_name',
        'slug',
        'full_name',
        'business_segments',
        'website',
        'social_telegram',
        'social_vk',
        'social_whatsapp',
        'phone',
        'email',
        'response_time',
        'work_volume',
        'smr_has_sro',
        'pir_has_sro',
        'ogrn',
        'inn',
        'kpp',
        'registration_date',
        'legal_address',
        'branch_contacts',
        'additional_info',
        'rating_id',
        'status',
        'owner_id',
    ];

    protected $casts = [
        'business_segments' => 'array',
        'smr_has_sro' => 'boolean',
        'pir_has_sro' => 'boolean',
        'registration_date' => 'date',
        'branch_contacts' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $contractor): void {
            if (! $contractor->isDirty('short_name') && ! empty($contractor->slug)) {
                return;
            }

            $contractor->slug = static::generateUniqueSlug((string) $contractor->short_name, $contractor->id);
        });
    }

    public static function generateUniqueSlug(string $name, ?int $exceptId = null): string
    {
        $base = Str::slug(Str::transliterate($name));
        if ($base === '') {
            $base = 'agent';
        }

        $slug = $base;
        $suffix = 1;

        while (static::query()
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ContractorCategory::class, 'contractor_contractor_category')->withTimestamps();
    }

    public function smrResourceTypes(): BelongsToMany
    {
        return $this->belongsToMany(ResourceType::class, 'contractor_smr_resource_type')->withTimestamps();
    }

    public function pirResourceTypes(): BelongsToMany
    {
        return $this->belongsToMany(ResourceType::class, 'contractor_pir_resource_type')->withTimestamps();
    }

    public function territories(): BelongsToMany
    {
        return $this->belongsToMany(GeoUnit::class, 'contractor_geo_unit')->withTimestamps();
    }

    public function rating(): BelongsTo
    {
        return $this->belongsTo(Rating::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ContractorReview::class);
    }

    public function scopeOrderedForModeration(Builder $query): Builder
    {
        return $query->orderByRaw(
            "case status when ? then 0 when ? then 1 else 2 end",
            ['pending', 'approved']
        );
    }
}
