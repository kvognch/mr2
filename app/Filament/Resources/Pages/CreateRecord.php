<?php

namespace App\Filament\Resources\Pages;

use Filament\Actions\Action;

abstract class CreateRecord extends \Filament\Resources\Pages\CreateRecord
{
    public function getBreadcrumb(): string
    {
        return 'Добавить';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Добавить');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Добавить ещё');
    }
}
