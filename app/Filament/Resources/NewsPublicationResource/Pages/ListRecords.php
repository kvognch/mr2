<?php

namespace App\Filament\Resources\NewsPublicationResource\Pages;

use App\Filament\Resources\NewsPublicationResource;
use Filament\Actions\CreateAction;

class ListRecords extends \Filament\Resources\Pages\ListRecords
{
    protected static string $resource = NewsPublicationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Добавить новость')];
    }
}
