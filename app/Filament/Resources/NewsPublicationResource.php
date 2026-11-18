<?php

namespace App\Filament\Resources;

use App\Models\NewsPublication;

class NewsPublicationResource extends EditorialResource
{
    protected static ?string $model = NewsPublication::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Новости';

    protected static ?string $navigationLabel = 'Публикации';

    protected static ?string $modelLabel = 'новость';

    protected static ?string $pluralModelLabel = 'Публикации';

    protected static ?string $breadcrumb = 'Публикации';

    public static function getPages(): array
    {
        return ['index' => NewsPublicationResource\Pages\ListRecords::route('/'), 'create' => NewsPublicationResource\Pages\CreateRecord::route('/create'), 'edit' => NewsPublicationResource\Pages\EditRecord::route('/{record}/edit')];
    }
}
