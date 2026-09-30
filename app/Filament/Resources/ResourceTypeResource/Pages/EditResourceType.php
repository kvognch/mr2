<?php

namespace App\Filament\Resources\ResourceTypeResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\ResourceTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResourceType extends EditRecord
{
    protected static string $resource = ResourceTypeResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['use_rich_editor'] = (bool) ($data['use_rich_editor'] ?? true);
        $data['seo_text_html'] = is_string($data['seo_text'] ?? null) ? $data['seo_text'] : '';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! ($data['use_rich_editor'] ?? true)) {
            $data['seo_text'] = $data['seo_text_html'] ?? '';
        }

        unset($data['seo_text_html']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
