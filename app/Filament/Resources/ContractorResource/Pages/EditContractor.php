<?php

namespace App\Filament\Resources\ContractorResource\Pages;

use App\Filament\Resources\ContractorResource;
use App\Filament\Resources\GeoUnitResource;
use App\Models\ContractorTariff;
use App\Models\GeoUnit;
use App\Support\ContractorSeo;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
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
            ->modalHeading(fn (array $arguments): string => 'Схемы по видам ресурсов: '.$this->getGeoUnitForSchemes($arguments)->name)
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
        return auth()->check();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['territory_ids'] = $this->record->territories()->pluck('geo_units.id')->map(fn ($id) => (int) $id)->all();

        if (ContractorResource::canManageSeo()) {
            $data = array_replace($data, ContractorSeo::formValues($this->record));
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (auth()->user()?->isClient()) {
            $data['status'] = 'pending';
        }

        if (ContractorResource::canManageSeo()) {
            $data = ContractorSeo::prepareForSave($data, $this->record);
        } else {
            unset($data['seo_h1'], $data['seo_title'], $data['seo_description'], $data['slug']);
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
