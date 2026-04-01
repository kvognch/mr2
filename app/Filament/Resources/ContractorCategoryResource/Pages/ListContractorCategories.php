<?php

namespace App\Filament\Resources\ContractorCategoryResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\ContractorCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContractorCategories extends ListRecords
{
    protected static string $resource = ContractorCategoryResource::class;

    protected static ?string $title = 'Категории подрядчиков';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
