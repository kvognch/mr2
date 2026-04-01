<?php

namespace App\Models;

use App\Enums\ServiceRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'comment',
        'status',
        'admin_note',
        'source_url',
    ];

    protected $casts = [
        'status' => ServiceRequestStatus::class,
    ];

    public function scopeOrderedForModeration(Builder $query): Builder
    {
        return $query
            ->orderByRaw(
                "case status when ? then 0 when ? then 1 else 2 end",
                [ServiceRequestStatus::Pending->value, ServiceRequestStatus::Processed->value]
            )
            ->orderByDesc('created_at');
    }
}
