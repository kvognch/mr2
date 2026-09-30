<?php

namespace App\Filament\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ContractorCategoryResource\Pages\ListContractorCategories;
use App\Filament\Resources\ContractorCategoryResource\Pages\CreateContractorCategory;
use App\Filament\Resources\ContractorCategoryResource\Pages\EditContractorCategory;
use App\Filament\Resources\ContractorCategoryResource\Pages;
use App\Models\ContractorCategory;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;
use Illuminate\Support\Str;

class ContractorCategoryResource extends Resource
{
    protected static ?string $model = ContractorCategory::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';

    protected static string | \UnitEnum | null $navigationGroup = 'Организации';

    protected static ?string $navigationLabel = 'Категории организаций';

    protected static ?string $modelLabel = 'категория организации';

    protected static ?string $pluralModelLabel = 'категории организаций';

    protected static ?string $breadcrumb = 'Категории организаций';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Название')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                    if (blank($get('slug'))) {
                        $set('slug', Str::slug(Str::transliterate((string) $state)));
                    }
                })
                ->maxLength(255)
                ->columnSpanFull(),
            Textarea::make('intro_text')
                ->label('Вступительный текст')
                ->rows(3)
                ->maxLength(2000)
                ->columnSpanFull(),
            Toggle::make('use_rich_editor')
                ->label('Визуальный редактор')
                ->helperText('Выключите, чтобы править HTML-код вручную.')
                ->live()
                ->default(true)
                ->afterStateUpdated(function (Get $get, Set $set, ?bool $state): void {
                    if ($state) {
                        $set('seo_text', $get('seo_text_html') ?? '');

                        return;
                    }

                    $set('seo_text_html', $get('seo_text') ?? '');
                })
                ->columnSpanFull(),
            RichEditor::make('seo_text')
                ->label('Текст')
                ->visible(fn (Get $get): bool => (bool) $get('use_rich_editor'))
                ->toolbarButtons([
                    'bold', 'italic', 'underline', 'strike', 'h2', 'h3',
                    'bulletList', 'orderedList', 'blockquote', 'link', 'redo', 'undo',
                ])
                ->columnSpanFull(),
            Textarea::make('seo_text_html')
                ->label('HTML-код')
                ->rows(20)
                ->visible(fn (Get $get): bool => ! $get('use_rich_editor'))
                ->extraAttributes(['style' => 'font-family: monospace;'])
                ->columnSpanFull(),
            Select::make('parent_id')
                ->label('Родительская категория')
                ->options(fn (?ContractorCategory $record): array => static::parentOptions($record))
                ->searchable()
                ->preload()
                ->nullable()
                ->placeholder('Верхний уровень')
                ->columnSpanFull(),
            TextInput::make('h1')
                ->label('H1')
                ->maxLength(255)
                ->columnSpanFull(),
            TextInput::make('meta_title')
                ->label('Title')
                ->maxLength(255)
                ->columnSpanFull(),
            Textarea::make('meta_description')
                ->label('Description')
                ->rows(3)
                ->maxLength(1000)
                ->columnSpanFull(),
            TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->helperText('Используется в адресе страницы и должен быть уникальным.')
                ->columnSpanFull(),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columnManager(false)
            ->columns([
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('parent.name')
                    ->label('Родительская категория')
                    ->placeholder('Верхний уровень'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContractorCategories::route('/'),
            'create' => CreateContractorCategory::route('/create'),
            'edit' => EditContractorCategory::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isSuperadmin() || auth()->user()?->isManager();
    }

    /** @return array<int, string> */
    private static function parentOptions(?ContractorCategory $record): array
    {
        $excludedIds = $record?->exists ? $record->descendantIds() : [];
        $categories = ContractorCategory::query()
            ->when($excludedIds !== [], fn ($query) => $query->whereNotIn('id', $excludedIds))
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id'])
            ->keyBy('id');

        return $categories->mapWithKeys(function (ContractorCategory $category) use ($categories): array {
            $labels = [$category->name];
            $parentId = $category->parent_id;
            $visited = [];

            while ($parentId !== null && ! isset($visited[$parentId]) && $categories->has($parentId)) {
                $visited[$parentId] = true;
                $parent = $categories->get($parentId);
                array_unshift($labels, $parent->name);
                $parentId = $parent->parent_id;
            }

            return [$category->id => implode(' / ', $labels)];
        })->all();
    }
}
