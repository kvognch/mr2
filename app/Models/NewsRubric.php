<?php

namespace App\Models;

class NewsRubric extends EditorialRubric
{
    protected $table = 'editorial_rubrics';

    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope('kind', fn ($q) => $q->where('editorial_rubrics.kind', 'news'));
        static::creating(fn ($record) => $record->setAttribute('kind', 'news'));
    }
}
