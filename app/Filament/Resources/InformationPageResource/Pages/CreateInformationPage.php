<?php

namespace App\Filament\Resources\InformationPageResource\Pages;

use App\Filament\Resources\InformationPageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInformationPage extends CreateRecord
{
    protected static string $resource = InformationPageResource::class;

    protected static ?string $title = 'Создать страницу';

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
