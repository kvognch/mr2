<?php

namespace App\Filament\Resources\BlogArticleResource\Pages;

use App\Filament\Resources\BlogArticleResource;
use App\Filament\Resources\Concerns\HandlesEditorialContent;

class CreateRecord extends \App\Filament\Resources\Pages\CreateRecord
{
    use HandlesEditorialContent;

    protected static ?string $title = 'Добавить статью';

    protected static string $resource = BlogArticleResource::class;
}
