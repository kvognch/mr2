<?php

namespace App\Filament\Resources\BlogRubricResource\Pages;

use App\Filament\Resources\BlogRubricResource;

class CreateRecord extends \App\Filament\Resources\Pages\CreateRecord
{
    protected static ?string $title = 'Добавить рубрику';

    protected static string $resource = BlogRubricResource::class;
}
