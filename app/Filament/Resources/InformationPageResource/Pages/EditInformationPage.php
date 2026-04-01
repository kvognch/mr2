<?php

namespace App\Filament\Resources\InformationPageResource\Pages;

use App\Filament\Resources\InformationPageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInformationPage extends EditRecord
{
    protected static string $resource = InformationPageResource::class;

    protected static ?string $title = 'Редактировать страницу';

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['use_rich_editor'] = true;
        $data['body_html'] = $data['body'] ?? '';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! ($data['use_rich_editor'] ?? true)) {
            $data['body'] = $data['body_html'] ?? '';
        }

        unset($data['body_html']);
        unset($data['use_rich_editor']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
