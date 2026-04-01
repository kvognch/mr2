<?php

namespace App\Filament\Resources\ServiceReviewResource\Pages;

use App\Enums\ReviewStatus;
use App\Filament\Resources\ServiceReviewResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditServiceReview extends EditRecord
{
    protected static string $resource = ServiceReviewResource::class;

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
