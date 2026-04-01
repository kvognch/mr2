<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
