<?php

namespace App\Filament\Resources\InformationPageResource\Pages;

use App\Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\InformationPageResource;

class CreateInformationPage extends CreateRecord
{
    protected static string $resource = InformationPageResource::class;

    protected static ?string $title = 'Добавить информационную страницу';

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['use_rich_editor'] = true;
        $data['body_html'] = $data['body'] ?? '';

        return $data;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! ($data['use_rich_editor'] ?? true)) {
            $data['body'] = $data['body_html'] ?? '';
        }

        unset($data['body_html']);
        unset($data['use_rich_editor']);

        return $data;
    }
}
