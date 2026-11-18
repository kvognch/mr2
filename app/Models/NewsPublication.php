<?php

namespace App\Models;

class NewsPublication extends Publication
{
    protected $table = 'publications';

    protected $attributes = ['kind' => 'news'];

    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope('kind', fn ($q) => $q->where('publications.kind', 'news'));
        static::creating(fn ($record) => $record->setAttribute('kind', 'news'));
    }
}
