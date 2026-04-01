<?php

namespace App\Filament\Resources\ContractorResource\Pages;

use App\Filament\Resources\ContractorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContractor extends CreateRecord
{
    protected static string $resource = ContractorResource::class;

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
