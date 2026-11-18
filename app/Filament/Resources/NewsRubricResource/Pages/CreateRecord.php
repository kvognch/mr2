<?php

namespace App\Filament\Resources\NewsRubricResource\Pages;

use App\Filament\Resources\NewsRubricResource;

class CreateRecord extends \App\Filament\Resources\Pages\CreateRecord
{
    protected static ?string $title = 'Добавить рубрику';

    protected static string $resource = NewsRubricResource::class;
}
