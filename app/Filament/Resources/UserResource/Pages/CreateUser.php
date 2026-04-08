<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\UserResource;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Добавить пользователя';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! auth()->user()?->isSuperadmin()) {
            $data['role'] = UserRole::Client->value;
        }

        return $data;
    }
}
