<?php

namespace App\Filament\Resources\NewsRubricResource\Pages;

use App\Filament\Resources\NewsRubricResource;
use Filament\Actions\DeleteAction;

class EditRecord extends \Filament\Resources\Pages\EditRecord
{
    protected static ?string $title = 'Редактировать рубрику';

    protected static string $resource = NewsRubricResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
