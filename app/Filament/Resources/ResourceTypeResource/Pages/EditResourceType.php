<?php

namespace App\Filament\Resources\ResourceTypeResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\ResourceTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResourceType extends EditRecord
{
    protected static string $resource = ResourceTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
