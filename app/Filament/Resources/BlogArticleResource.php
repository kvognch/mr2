<?php

namespace App\Filament\Resources;

use App\Models\BlogArticle;

class BlogArticleResource extends EditorialResource
{
    protected static ?string $model = BlogArticle::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Блог';

    protected static ?string $navigationLabel = 'Статьи';

    protected static ?string $modelLabel = 'статья';

    protected static ?string $pluralModelLabel = 'Статьи';

    protected static ?string $breadcrumb = 'Статьи';

    public static function getPages(): array
    {
        return ['index' => BlogArticleResource\Pages\ListRecords::route('/'), 'create' => BlogArticleResource\Pages\CreateRecord::route('/create'), 'edit' => BlogArticleResource\Pages\EditRecord::route('/{record}/edit')];
    }
}
