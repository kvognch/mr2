<?php

namespace App\Filament\Resources\ResourceTypeResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\ResourceTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResourceTypes extends ListRecords
{
    protected static string $resource = ResourceTypeResource::class;

    protected static ?string $title = 'Виды ресурсов';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
