<?php

namespace App\Filament\Resources\InformationPageResource\Pages;

use App\Filament\Resources\InformationPageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInformationPages extends ListRecords
{
    protected static string $resource = InformationPageResource::class;

    protected static ?string $title = 'Информация';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
