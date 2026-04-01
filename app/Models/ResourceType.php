<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class ResourceType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'abbreviation', 'icon'];

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
