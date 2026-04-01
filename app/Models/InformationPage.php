<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InformationPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'body',
        'is_active',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $page): void {
            if (blank($page->slug)) {
                $page->slug = static::generateUniqueSlug((string) $page->title, $page->id);
            }
        });
    }

    public static function generateUniqueSlug(string $title, ?int $exceptId = null): string
    {
        $base = Str::slug(Str::transliterate($title));

        if ($base === '') {
            $base = 'info-page';
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
}
