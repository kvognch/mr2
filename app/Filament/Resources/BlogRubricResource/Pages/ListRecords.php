<?php

namespace App\Filament\Resources\BlogRubricResource\Pages;

use App\Filament\Resources\BlogRubricResource;
use Filament\Actions\CreateAction;

class ListRecords extends \Filament\Resources\Pages\ListRecords
{
    protected static string $resource = BlogRubricResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Добавить рубрику')];
    }
}
