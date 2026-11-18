<?php

namespace App\Models;

class BlogRubric extends EditorialRubric
{
    protected $table = 'editorial_rubrics';

    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope('kind', fn ($q) => $q->where('editorial_rubrics.kind', 'blog'));
        static::creating(fn ($record) => $record->setAttribute('kind', 'blog'));
    }
}
