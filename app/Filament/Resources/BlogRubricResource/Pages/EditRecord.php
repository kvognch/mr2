<?php

namespace App\Filament\Resources\BlogRubricResource\Pages;

use App\Filament\Resources\BlogRubricResource;
use Filament\Actions\DeleteAction;

class EditRecord extends \Filament\Resources\Pages\EditRecord
{
    protected static ?string $title = 'Редактировать рубрику';

    protected static string $resource = BlogRubricResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
