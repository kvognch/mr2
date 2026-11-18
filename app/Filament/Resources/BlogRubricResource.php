<?php

namespace App\Filament\Resources;

use App\Models\BlogRubric;

class BlogRubricResource extends EditorialRubricResource
{
    protected static ?string $model = BlogRubric::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Блог';

    protected static ?string $navigationLabel = 'Рубрики';

    protected static ?string $modelLabel = 'рубрика';

    protected static ?string $pluralModelLabel = 'Рубрики';

    protected static ?string $breadcrumb = 'Рубрики';

    public static function getPages(): array
    {
        return ['index' => BlogRubricResource\Pages\ListRecords::route('/'), 'create' => BlogRubricResource\Pages\CreateRecord::route('/create'), 'edit' => BlogRubricResource\Pages\EditRecord::route('/{record}/edit')];
    }
}
