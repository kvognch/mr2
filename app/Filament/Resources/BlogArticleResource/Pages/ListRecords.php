<?php

namespace App\Filament\Resources\BlogArticleResource\Pages;

use App\Filament\Resources\BlogArticleResource;
use Filament\Actions\CreateAction;

class ListRecords extends \Filament\Resources\Pages\ListRecords
{
    protected static string $resource = BlogArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Добавить статью')];
    }
}
