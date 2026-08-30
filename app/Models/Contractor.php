<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
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
        'application_url',
        'social_telegram',
        'social_vk',
        'social_whatsapp',
        'social_max',
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

    public function hasGuaranteeingSupplierCategory(): bool
    {
        return $this->hasCategory('Гарантирующий поставщик');
    }

    public function hasResourceSupplyingCategory(): bool
    {
        return $this->hasCategory('Ресурсо-снабжающая организация');
    }

    public function hasTariffCategory(): bool
    {
        return $this->hasGuaranteeingSupplierCategory() || $this->hasResourceSupplyingCategory();
    }

    protected function hasCategory(string $categoryName): bool
    {
        if ($this->relationLoaded('categories')) {
            return $this->categories
                ->pluck('name')
                ->contains($categoryName);
        }

        return $this->categories()
            ->where('name', $categoryName)
            ->exists();
    }

    public function hasContractorCategory(): bool
    {
        if ($this->relationLoaded('categories')) {
            return $this->categories
                ->pluck('name')
                ->contains('Подрядчик');
        }

        return $this->categories()
            ->where('name', 'Подрядчик')
            ->exists();
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

    public function tariffs(): HasMany
    {
        return $this->hasMany(ContractorTariff::class)->latest();
    }

    public function currentConnectionTariff(): HasMany
    {
        return $this->hasMany(ContractorTariff::class)
            ->where('tariff_type', ContractorTariff::TYPE_CONNECTION)
            ->where('is_current', true)
            ->latest();
    }

    public function connectionTariffHistory(): HasMany
    {
        return $this->hasMany(ContractorTariff::class)
            ->where('tariff_type', ContractorTariff::TYPE_CONNECTION)
            ->where('is_current', false)
            ->latest();
    }

    public function currentSalesTariff(): HasMany
    {
        return $this->hasMany(ContractorTariff::class)
            ->where('tariff_type', ContractorTariff::TYPE_SALES)
            ->where('is_current', true)
            ->latest();
    }

    public function salesTariffHistory(): HasMany
    {
        return $this->hasMany(ContractorTariff::class)
            ->where('tariff_type', ContractorTariff::TYPE_SALES)
            ->where('is_current', false)
            ->latest();
    }

    public function currentTariff(): HasMany
    {
        return $this->currentConnectionTariff();
    }

    public function tariffHistory(): HasMany
    {
        return $this->connectionTariffHistory();
    }

    public function addConnectionTariff(string $path): ContractorTariff
    {
        return $this->addTariff($path, ContractorTariff::TYPE_CONNECTION);
    }

    public function addSalesTariff(string $path): ContractorTariff
    {
        return $this->addTariff($path, ContractorTariff::TYPE_SALES);
    }

    public function addCurrentTariff(string $path): ContractorTariff
    {
        return $this->addConnectionTariff($path);
    }

    protected function addTariff(string $path, string $tariffType): ContractorTariff
    {
        $this->tariffs()
            ->where('tariff_type', $tariffType)
            ->where('is_current', true)
            ->update(['is_current' => false]);

        $disk = Storage::disk('public');

        return $this->tariffs()->create([
            'tariff_type' => $tariffType,
            'path' => $path,
            'original_name' => basename($path),
            'mime_type' => $disk->exists($path) ? $disk->mimeType($path) : null,
            'size' => $disk->exists($path) ? $disk->size($path) : null,
            'is_current' => true,
        ]);
    }

    public function scopeOrderedForModeration(Builder $query): Builder
    {
        return $query->orderByRaw(
            "case status when ? then 0 when ? then 1 else 2 end",
            ['pending', 'approved']
        );
    }
}
