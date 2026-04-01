<?php

namespace App\Filament\Resources\ServiceReviewResource\Pages;

use App\Filament\Resources\ServiceReviewResource;
use Filament\Resources\Pages\ListRecords;

class ListServiceReviews extends ListRecords
{
    protected static string $resource = ServiceReviewResource::class;

    protected static ?string $title = 'Отзывы о сервисе';
}
