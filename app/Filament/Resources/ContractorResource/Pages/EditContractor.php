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
        unset($data['territory_ids']);

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
    }
}
