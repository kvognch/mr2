<?php

namespace App\Filament\Resources\NewsPublicationResource\Pages;

use App\Filament\Resources\Concerns\HandlesEditorialContent;
use App\Filament\Resources\NewsPublicationResource;

class CreateRecord extends \App\Filament\Resources\Pages\CreateRecord
{
    use HandlesEditorialContent;

    protected static ?string $title = 'Добавить новость';

    protected static string $resource = NewsPublicationResource::class;
}
