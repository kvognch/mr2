<?php

namespace App\Filament\Resources\BlogArticleResource\Pages;

use App\Filament\Resources\BlogArticleResource;
use App\Filament\Resources\Concerns\HandlesEditorialContent;
use Filament\Actions\DeleteAction;

class EditRecord extends \Filament\Resources\Pages\EditRecord
{
    use HandlesEditorialContent;

    protected static ?string $title = 'Редактировать статью';

    protected static string $resource = BlogArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->modalDescription('Будет удалена 1 публикация. Подтвердите удаление.')];
    }
}
