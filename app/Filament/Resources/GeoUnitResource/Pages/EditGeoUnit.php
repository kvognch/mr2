<?php

namespace App\Filament\Resources\GeoUnitResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\GeoUnitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGeoUnit extends EditRecord
{
    protected static string $resource = GeoUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
