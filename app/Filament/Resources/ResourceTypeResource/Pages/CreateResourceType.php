<?php

namespace App\Filament\Resources\ResourceTypeResource\Pages;

use App\Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ResourceTypeResource;

class CreateResourceType extends CreateRecord
{
    protected static string $resource = ResourceTypeResource::class;

    protected static ?string $title = 'Добавить вид ресурса';

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['use_rich_editor'] = true;
        $data['seo_text_html'] = is_string($data['seo_text'] ?? null) ? $data['seo_text'] : '';

        return $data;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! ($data['use_rich_editor'] ?? true)) {
            $data['seo_text'] = $data['seo_text_html'] ?? '';
        }

        unset($data['seo_text_html']);

        return $data;
    }
}
