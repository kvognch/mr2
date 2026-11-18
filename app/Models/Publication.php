<?php

namespace App\Models;

use App\Support\EditorialHtml;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Publication extends Model
{
    protected $table = 'publications';

    protected $guarded = ['id', 'kind'];

    protected $casts = ['use_rich_editor' => 'boolean', 'published_at' => 'datetime', 'content_updated_at' => 'datetime', 'source_checked_at' => 'date', 'is_federal' => 'boolean', 'cta' => 'array'];

    protected static function booted(): void
    {
        static::saving(function (self $p) {
            if (blank($p->slug)) {
                $p->slug = Str::slug($p->title);
            }
            $p->body = EditorialHtml::clean((string) $p->body);
            if ($p->source_url && ! preg_match('~^https?://~i', $p->source_url)) {
                throw ValidationException::withMessages(['source_url' => 'Используйте URL с http или https.']);
            }
            if ($p->rubric_id && ! EditorialRubric::whereKey($p->rubric_id)->where('kind', $p->kind)->exists()) {
                throw ValidationException::withMessages(['rubric_id' => 'Рубрика должна относиться к этому разделу.']);
            }
            if ($p->status === 'published' && (blank($p->title) || blank($p->slug) || ! $p->rubric_id || blank(strip_tags($p->body)) || ! $p->published_at)) {
                throw ValidationException::withMessages(['status' => 'Для публикации заполните название, slug, рубрику, содержание и дату публикации.']);
            }
        });
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function rubric()
    {
        return $this->belongsTo(EditorialRubric::class);
    }

    public function directions()
    {
        return $this->belongsToMany(ResourceType::class, 'publication_resource_type', 'publication_id', 'resource_type_id');
    }

    public function regions()
    {
        return $this->belongsToMany(GeoUnit::class, 'publication_geo_unit', 'publication_id', 'geo_unit_id');
    }

    public function organizations()
    {
        return $this->belongsToMany(Contractor::class, 'publication_contractor', 'publication_id', 'contractor_id');
    }

    public function relatedArticles()
    {
        return $this->belongsToMany(BlogArticle::class, 'publication_related', 'news_id', 'article_id')->withPivot('sort_order')->orderByPivot('sort_order');
    }

    public function getPublicUrlAttribute(): string
    {
        return route($this->kind.'.show', ['slug' => $this->slug]);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    public function getMapUrlAttribute(): string
    {
        $c = $this->cta ?? [];
        $p = [];
        foreach (['direction_id' => 'res', 'category_id' => 'cat', 'region_id' => 'r'] as $key => $param) {
            if (! empty($c[$key])) {
                $p[$param] = (int) $c[$key];
            }
        }

        return route('search.index', $p);
    }

    public function getSafeBodyAttribute(): string
    {
        return EditorialHtml::clean((string) $this->body);
    }
}
