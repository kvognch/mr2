<?php

namespace App\Filament\Resources\RatingResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\RatingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRatings extends ListRecords
{
    protected static string $resource = RatingResource::class;

    protected static ?string $title = 'Рейтинги';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
