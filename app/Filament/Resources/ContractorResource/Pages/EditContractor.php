<?php

namespace App\Filament\Resources\ContractorResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\ContractorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContractor extends EditRecord
{
    protected static string $resource = ContractorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['territory_ids'] = $this->record->territories()->pluck('geo_units.id')->map(fn ($id) => (int) $id)->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['territory_ids'], $data['tariff_upload']);

        return $data;
    }

    protected function afterSave(): void
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
