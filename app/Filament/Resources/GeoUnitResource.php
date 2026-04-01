<?php

namespace App\Filament\Resources;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkAction;
use App\Filament\Resources\GeoUnitResource\Pages\ListGeoUnits;
use App\Filament\Resources\GeoUnitResource\Pages\CreateGeoUnit;
use App\Filament\Resources\GeoUnitResource\Pages\EditGeoUnit;
use App\Filament\Resources\GeoUnitResource\Pages;
use App\Models\GeoUnit;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class GeoUnitResource extends Resource
{
    protected static ?string $model = GeoUnit::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map';

    protected static string | \UnitEnum | null $navigationGroup = 'Геоданные';

    protected static ?string $navigationLabel = 'Геообъекты';

    protected static ?string $breadcrumb = 'Геообъекты';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Название')->required()->maxLength(255),
            Select::make('parent_id')
                ->label('Родитель')
                ->relationship('parent', 'name')
                ->searchable()
                ->nullable(),
            Toggle::make('is_active')->label('Активен на карте'),

            Section::make('Схемы по видам ресурсов')
                ->collapsed()
                ->schema([
                    Repeater::make('resource_schemes')
                        ->label('Файлы')
                        ->addActionLabel('Добавить схему')
                        ->defaultItems(0)
                        ->reorderable(false)
                        ->itemLabel(fn (array $state): ?string => filled($state['title'] ?? null) ? (string) $state['title'] : 'Схема')
                        ->schema([
                            TextInput::make('title')
                                ->label('Название')
                                ->required()
                                ->maxLength(255),
                            FileUpload::make('file')
                                ->label('Файл')
                                ->disk('public')
                                ->directory('geo-unit-schemes')
                                ->downloadable()
                                ->openable()
                                ->preserveFilenames()
                                ->helperText('Поддерживаются PDF и офисные документы')
                                ->required(),
                        ])
                        ->columns(1)
                        ->columnSpanFull(),
                ])
                ->columns(1),

            Section::make('Служебные поля')
                ->collapsed()
                ->schema([
                    TextInput::make('source')->label('Источник')->required()->maxLength(32),
                    TextInput::make('source_id')->label('ID источника')->required()->maxLength(255),
                    TextInput::make('parent_source_id')->label('ID родителя в источнике')->maxLength(255),
                    TextInput::make('normalized_name')->label('Нормализованное название')->required()->maxLength(255),
                    TextInput::make('admin_level')->label('Уровень OSM')->numeric(),
                    TextInput::make('level')->label('Тип уровня')->maxLength(32),
                    TextInput::make('boundary')->label('Тип границы')->maxLength(64),
                ])
                ->columns(1),

            Section::make('Геометрия OSM (JSON)')
                ->collapsed()
                ->schema([
                    Textarea::make('geometry_osm')
                        ->label('OSM геометрия')
                        ->rows(8)
                        ->formatStateUsing(fn ($state): string => is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : (string) ($state ?? ''))
                        ->dehydrateStateUsing(function ($state) {
                            $decoded = json_decode((string) $state, true);
                            return is_array($decoded) ? $decoded : null;
                        }),
                ])
                ->columns(1),

            Section::make('Геометрия Яндекс (JSON)')
                ->collapsed()
                ->schema([
                    Textarea::make('geometry_yandex')
                        ->label('Яндекс геометрия')
                        ->rows(8)
                        ->formatStateUsing(fn ($state): string => is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : (string) ($state ?? ''))
                        ->dehydrateStateUsing(function ($state) {
                            $decoded = json_decode((string) $state, true);
                            return is_array($decoded) ? $decoded : null;
                        }),
                ])
                ->columns(1),

            Section::make('Дополнительно')
                ->collapsed()
                ->schema([
                    Textarea::make('properties')
                        ->label('Свойства (JSON)')
                        ->rows(6)
                        ->formatStateUsing(fn ($state): string => is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) : (string) ($state ?? ''))
                        ->dehydrateStateUsing(function ($state) {
                            $decoded = json_decode((string) $state, true);
                            return is_array($decoded) ? $decoded : null;
                        }),
                    Textarea::make('meta')
                        ->label('Метаданные (JSON)')
                        ->rows(4)
                        ->formatStateUsing(fn ($state): string => is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) : (string) ($state ?? ''))
                        ->dehydrateStateUsing(function ($state) {
                            $decoded = json_decode((string) $state, true);
                            return is_array($decoded) ? $decoded : null;
                        }),
                ])
                ->columns(1),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columnManager(false)
            ->columns([
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->url(fn (GeoUnit $record): string => self::getUrl('index', ['parent_id' => $record->id])),
                TextColumn::make('parent.name')
                    ->label('Родитель')
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Статус')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkAction::make('activateSelected')
                    ->label('Активировать')
                    ->requiresConfirmation()
                    ->action(fn ($records) => $records->each(fn (GeoUnit $item) => $item->update(['is_active' => true]))),
                BulkAction::make('deactivateSelected')
                    ->label('Деактивировать')
                    ->requiresConfirmation()
                    ->action(fn ($records) => $records->each(fn (GeoUnit $item) => $item->update(['is_active' => false]))),
            ])
            ->paginationPageOptions([50, 100, 200])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGeoUnits::route('/'),
            'create' => CreateGeoUnit::route('/create'),
            'edit' => EditGeoUnit::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isSuperadmin() || auth()->user()?->isManager();
    }
}
