<?php

namespace App\Filament\Resources\ContractorCategoryResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\ContractorCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContractorCategory extends EditRecord
{
    protected static string $resource = ContractorCategoryResource::class;

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
