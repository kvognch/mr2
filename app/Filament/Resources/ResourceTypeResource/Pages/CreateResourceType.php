<?php

namespace App\Filament\Resources\ResourceTypeResource\Pages;

use App\Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ResourceTypeResource;

class CreateResourceType extends CreateRecord
{
    protected static string $resource = ResourceTypeResource::class;

    protected static ?string $title = 'Добавить вид ресурса';
}
