<?php

namespace App\Filament\Resources\GeoUnitResource\Pages;

use App\Filament\Resources\GeoUnitResource;
use App\Models\GeoUnit;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditGeoUnit extends EditRecord
{
    protected static string $resource = GeoUnitResource::class;

    public function getTitle(): string
    {
        return (string) ($this->getRecord()->getAttribute('name') ?: 'Геообъект');
    }

    /**
     * Keep the status of a manually edited unit and its descendants in sync.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        if ($record instanceof GeoUnit && array_key_exists('is_active', $data)) {
            $record->setActiveWithDescendants((bool) ($data['is_active'] ?? false));
        }

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
