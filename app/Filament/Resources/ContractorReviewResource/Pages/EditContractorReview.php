<?php

namespace App\Filament\Resources\ContractorReviewResource\Pages;

use App\Enums\ReviewStatus;
use App\Filament\Resources\ContractorReviewResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContractorReview extends EditRecord
{
    protected static string $resource = ContractorReviewResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! auth()->user()?->isSuperadmin() && ! auth()->user()?->isManager()) {
            $data['status'] = ReviewStatus::Pending->value;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
