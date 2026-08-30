<?php

namespace App\Filament\Resources\ContractorResource\Pages;

use App\Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ContractorResource;
use App\Models\ContractorTariff;

class CreateContractor extends CreateRecord
{
    protected static string $resource = ContractorResource::class;

    protected static ?string $title = 'Добавить подрядчика';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['owner_id'] ??= auth()->id();
        unset($data['territory_ids'], $data['connection_tariff_upload'], $data['sales_tariff_upload']);

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
        $this->saveUploadedTariff('connection_tariff_upload', ContractorTariff::TYPE_CONNECTION);
        $this->saveUploadedTariff('sales_tariff_upload', ContractorTariff::TYPE_SALES);
    }

    protected function saveUploadedTariff(string $field, string $tariffType): void
    {
        $path = $this->data[$field] ?? null;

        if (is_array($path)) {
            $path = reset($path) ?: null;
        }

        if (is_string($path) && $path !== '') {
            if ($tariffType === ContractorTariff::TYPE_SALES) {
                $this->record->addSalesTariff($path);
            } else {
                $this->record->addConnectionTariff($path);
            }

            $this->data[$field] = null;
        }
    }
}
