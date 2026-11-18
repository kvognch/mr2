<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EditorialRubric extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_visible' => 'boolean'];

    protected static function booted(): void
    {
        static::saving(function ($record) {
            if (blank($record->slug)) {
                $record->slug = Str::slug($record->name);
            }
        });
    }
}
