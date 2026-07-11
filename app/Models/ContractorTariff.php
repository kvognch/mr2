<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ContractorTariff extends Model
{
    use HasFactory;

    protected $fillable = [
        'contractor_id',
        'path',
        'original_name',
        'mime_type',
        'size',
        'is_current',
    ];

    protected $casts = [
        'is_current' => 'boolean',
    ];

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
