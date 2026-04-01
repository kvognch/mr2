<?php

namespace App\Filament\Resources\ContractorCategoryResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\ContractorCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContractorCategory extends EditRecord
{
    protected static string $resource = ContractorCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
