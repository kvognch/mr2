<?php

namespace App\Filament\Resources\ContractorCategoryResource\Pages;

use App\Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ContractorCategoryResource;

class CreateContractorCategory extends CreateRecord
{
    protected static string $resource = ContractorCategoryResource::class;

    protected static ?string $title = 'Добавить категорию подрядчика';
}
