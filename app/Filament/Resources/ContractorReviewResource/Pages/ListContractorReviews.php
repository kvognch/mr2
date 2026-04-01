<?php

namespace App\Filament\Resources\ContractorReviewResource\Pages;

use App\Filament\Resources\ContractorReviewResource;
use Filament\Resources\Pages\ListRecords;

class ListContractorReviews extends ListRecords
{
    protected static string $resource = ContractorReviewResource::class;

    protected static ?string $title = 'Отзывы о подрядчиках';
}
