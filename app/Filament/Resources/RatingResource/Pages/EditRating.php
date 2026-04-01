<?php

namespace App\Filament\Resources\RatingResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\RatingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRating extends EditRecord
{
    protected static string $resource = RatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
