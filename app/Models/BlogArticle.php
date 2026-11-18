<?php

namespace App\Models;

class BlogArticle extends Publication
{
    protected $table = 'publications';

    protected $attributes = ['kind' => 'blog'];

    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope('kind', fn ($q) => $q->where('publications.kind', 'blog'));
        static::creating(fn ($record) => $record->setAttribute('kind', 'blog'));
    }
}
