<?php

namespace App\Filament\Resources\RatingResource\Pages;

use App\Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\RatingResource;

class CreateRating extends CreateRecord
{
    protected static string $resource = RatingResource::class;

    protected static ?string $title = 'Добавить рейтинг';
}
