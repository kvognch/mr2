<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'author_name',
        'author_role',
        'title',
        'body',
        'rating',
        'is_recommended',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_recommended' => 'boolean',
        'status' => ReviewStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', ReviewStatus::Approved->value);
    }

    public function scopeOrderedForModeration(Builder $query): Builder
    {
        return $query
            ->orderByRaw(
                "case status when ? then 0 when ? then 1 else 2 end",
                [ReviewStatus::Pending->value, ReviewStatus::Approved->value]
            );
    }
}
