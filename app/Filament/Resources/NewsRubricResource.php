<?php

namespace App\Filament\Resources;

use App\Models\NewsRubric;

class NewsRubricResource extends EditorialRubricResource
{
    protected static ?string $model = NewsRubric::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Новости';

    protected static ?string $navigationLabel = 'Рубрики';

    protected static ?string $modelLabel = 'рубрика';

    protected static ?string $pluralModelLabel = 'Рубрики';

    protected static ?string $breadcrumb = 'Рубрики';

    public static function getPages(): array
    {
        return ['index' => NewsRubricResource\Pages\ListRecords::route('/'), 'create' => NewsRubricResource\Pages\CreateRecord::route('/create'), 'edit' => NewsRubricResource\Pages\EditRecord::route('/{record}/edit')];
    }
}
