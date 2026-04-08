<?php

namespace App\Filament\Resources\ContractorResource\Pages;

use App\Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ContractorResource;

class CreateContractor extends CreateRecord
{
    protected static string $resource = ContractorResource::class;

    protected static ?string $title = 'Добавить подрядчика';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['owner_id'] ??= auth()->id();
        unset($data['territory_ids']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $territoryIds = collect($this->data['territory_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $this->record->territories()->sync($territoryIds);
    }
}
