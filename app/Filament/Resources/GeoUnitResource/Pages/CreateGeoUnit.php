<?php

namespace App\Filament\Resources\GeoUnitResource\Pages;

use App\Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\GeoUnitResource;

class CreateGeoUnit extends CreateRecord
{
    protected static string $resource = GeoUnitResource::class;

    protected static ?string $title = 'Добавить геообъект';
}
