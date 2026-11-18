<?php

namespace App\Filament\Resources\NewsPublicationResource\Pages;

use App\Filament\Resources\Concerns\HandlesEditorialContent;
use App\Filament\Resources\NewsPublicationResource;
use Filament\Actions\DeleteAction;

class EditRecord extends \Filament\Resources\Pages\EditRecord
{
    use HandlesEditorialContent;

    protected static ?string $title = 'Редактировать новость';

    protected static string $resource = NewsPublicationResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->modalDescription('Будет удалена 1 публикация. Подтвердите удаление.')];
    }
}
