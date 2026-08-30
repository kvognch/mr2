<?php

namespace App\Filament\Resources\ContractorResource\Pages;

use App\Filament\Resources\GeoUnitResource;
use App\Models\GeoUnit;
use App\Models\ContractorTariff;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\ContractorResource;
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

    protected function manageGeoUnitSchemesAction(): Action
    {
        return Action::make('manageGeoUnitSchemes')
            ->label('Схемы по видам ресурсов')
            ->modalHeading(fn (array $arguments): string => 'Схемы по видам ресурсов: ' . $this->getGeoUnitForSchemes($arguments)->name)
            ->modalWidth('4xl')
            ->modalSubmitActionLabel('Сохранить')
            ->schema(GeoUnitResource::resourceSchemesFormComponents())
            ->fillForm(fn (array $arguments): array => [
                'resource_schemes' => $this->getGeoUnitForSchemes($arguments)->resource_schemes ?? [],
            ])
            ->action(function (array $data, array $arguments): void {
                $geoUnit = $this->getGeoUnitForSchemes($arguments);
                $geoUnit->update([
                    'resource_schemes' => is_array($data['resource_schemes'] ?? null)
                        ? array_values($data['resource_schemes'])
                        : [],
                ]);
            })
            ->successNotificationTitle('Схемы по видам ресурсов сохранены');
    }

    protected function getGeoUnitForSchemes(array $arguments): GeoUnit
    {
        abort_unless($this->canManageGeoUnitSchemes(), 403);

        $geoUnitId = (int) ($arguments['geoUnitId'] ?? 0);
        abort_unless($geoUnitId > 0, 404);

        return GeoUnit::query()
            ->select(['id', 'name', 'resource_schemes'])
            ->findOrFail($geoUnitId);
    }

    protected function canManageGeoUnitSchemes(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isSuperadmin() || $user?->isManager());
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['territory_ids'] = $this->record->territories()->pluck('geo_units.id')->map(fn ($id) => (int) $id)->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (auth()->user()?->isClient()) {
            $data['status'] = 'pending';
        }

        unset($data['territory_ids'], $data['connection_tariff_upload'], $data['sales_tariff_upload']);

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
