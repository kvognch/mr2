<?php

namespace App\Filament\Resources\NewsRubricResource\Pages;

use App\Filament\Resources\NewsRubricResource;
use Filament\Actions\CreateAction;

class ListRecords extends \Filament\Resources\Pages\ListRecords
{
    protected static string $resource = NewsRubricResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Добавить рубрику')];
    }
}
