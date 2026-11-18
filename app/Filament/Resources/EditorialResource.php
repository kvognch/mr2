<?php

namespace App\Filament\Resources;

use App\Models\BlogArticle;
use App\Models\ContractorCategory;
use App\Models\GeoUnit;
use App\Models\NewsPublication;
use App\Models\ResourceType;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

abstract class EditorialResource extends InformationPageResource
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    public static function form(Schema $schema): Schema
    {
        $news = static::getModel() === NewsPublication::class;

        return $schema->columns(1)->components([
            TextInput::make('title')->label('Название')->required()->maxLength(255),
            TextInput::make('slug')->label('Slug')->required()->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->maxLength(255)->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->where('kind', $news ? 'news' : 'blog')),
            Textarea::make('excerpt')->label('Текст превью')->rows(3),
            FileUpload::make('image')->label('Изображение-превью')->image()->disk('public')->directory('editorial')->maxSize(5120),
            Select::make('rubric_id')->label('Рубрика')->relationship('rubric', 'name', modifyQueryUsing: fn ($query) => $query->where('kind', $news ? 'news' : 'blog'))->required()->searchable()->preload(),
            Select::make('directions')->label('Направления')->relationship('directions', 'name')->multiple()->searchable()->preload(),
            Toggle::make('is_federal')->label('Вся Россия')->visible($news),
            Select::make('regions')->label('Регионы')->relationship('regions', 'name', modifyQueryUsing: fn ($query) => $query->select('geo_units.id', 'geo_units.name')->where('admin_level', 4))->multiple()->searchable()->preload(),
            Select::make('organizations')->label('Организации')->relationship('organizations', 'short_name', modifyQueryUsing: fn ($query) => $query->select('contractors.id', 'contractors.short_name'))->multiple()->searchable()->visible($news),
            Select::make('status')->label('Статус')->options(['draft' => 'Черновик', 'published' => 'Опубликовано', 'withdrawn' => 'Снято с публикации'])->default('draft')->required(),
            DateTimePicker::make('published_at')->label('Дата публикации'),
            DateTimePicker::make('content_updated_at')->label('Дата содержательного обновления'),
            Toggle::make('use_rich_editor')->label('Визуальный редактор')->default(true)->live()->afterStateUpdated(function (Get $get, Set $set, $state) {
                if ($state) {
                    $set('body', $get('body_html'));
                } else {
                    $value = $get('body');
                    $set('body_html', is_array($value) ? RichContentRenderer::make($value)->toHtml() : $value);
                }
            }),
            RichEditor::make('body')->label('Содержание')->visible(fn (Get $get) => $get('use_rich_editor'))->columnSpanFull(),
            Textarea::make('body_html')->label('HTML-код')->rows(24)->visible(fn (Get $get) => ! $get('use_rich_editor'))->columnSpanFull(),
            TextInput::make('source_name')->label('Название источника')->visible($news)->maxLength(255),
            TextInput::make('source_url')->label('URL первоисточника')->url()->rules(['nullable', 'regex:~^https?://~i'])->visible($news),
            Textarea::make('source_document')->label('Документ')->visible($news),
            DatePicker::make('source_checked_at')->label('Дата проверки источника')->visible($news),
            Textarea::make('audience')->label('Кого касается')->visible($news),
            Repeater::make('related_rows')->label('Материалы по теме')->schema([Select::make('article_id')->label('Статья')->options(fn () => BlogArticle::published()->pluck('title', 'id'))->required()->searchable()->distinct()])->maxItems(3)->defaultItems(0)->reorderable()->visible($news),
            Toggle::make('cta.enabled')->label('Показывать блок перехода на карту')->default(false),
            TextInput::make('cta.title')->label('Заголовок блока')->maxLength(255),
            Textarea::make('cta.text')->label('Пояснение'),
            TextInput::make('cta.button')->label('Текст кнопки')->maxLength(255),
            Select::make('cta.direction_id')->label('Направление на карте')->options(fn () => ResourceType::pluck('name', 'id'))->searchable(),
            Select::make('cta.category_id')->label('Категория на карте')->options(fn () => ContractorCategory::pluck('name', 'id'))->searchable(),
            Select::make('cta.region_id')->label('Регион на карте')->options(fn () => GeoUnit::where('admin_level', 4)->pluck('name', 'id'))->searchable(),
            TextInput::make('meta_title')->label('Title')->maxLength(255), Textarea::make('meta_description')->label('Description')->maxLength(1000),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('title')->label('Название')->searchable()->sortable(), TextColumn::make('rubric.name')->label('Рубрика'), TextColumn::make('status')->label('Статус')->formatStateUsing(fn ($state) => ['draft' => 'Черновик', 'published' => 'Опубликовано', 'withdrawn' => 'Снято с публикации'][$state] ?? $state), TextColumn::make('published_at')->label('Публикация')->dateTime()->sortable()])->defaultSort('published_at', 'desc')->recordActions([EditAction::make()])->toolbarActions([BulkAction::make('withdraw')->label('Снять с публикации')->requiresConfirmation()->action(fn (Collection $records) => $records->each->update(['status' => 'withdrawn']))->deselectRecordsAfterCompletion(), DeleteBulkAction::make()->modalDescription(fn (Collection $records) => 'Будет удалено публикаций: '.$records->count().'. Подтвердите удаление.')]);
    }
}
