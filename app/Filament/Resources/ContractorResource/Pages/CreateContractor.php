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
        unset($data['territory_ids'], $data['tariff_upload']);

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
        $this->saveUploadedTariff();
    }

    protected function saveUploadedTariff(): void
    {
        $path = $this->data['tariff_upload'] ?? null;

        if (is_array($path)) {
            $path = reset($path) ?: null;
        }

        if (is_string($path) && $path !== '') {
            $this->record->addCurrentTariff($path);
            $this->data['tariff_upload'] = null;
        }
    }
}
