<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GeoUnitResource\Pages\CreateGeoUnit;
use App\Filament\Resources\GeoUnitResource\Pages\EditGeoUnit;
use App\Filament\Resources\GeoUnitResource\Pages\ListGeoUnits;
use App\Models\GeoUnit;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GeoUnitResource extends Resource
{
    protected static ?string $model = GeoUnit::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map';

    protected static string|\UnitEnum|null $navigationGroup = 'Геоданные';

    protected static ?string $navigationLabel = 'Геообъекты';

    protected static ?string $modelLabel = 'геообъект';

    protected static ?string $pluralModelLabel = 'геообъекты';

    protected static ?string $breadcrumb = 'Геообъекты';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 10;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->select([
            'geo_units.id',
            'geo_units.parent_id',
            'geo_units.name',
            'geo_units.is_active',
            'geo_units.resource_schemes',
            'geo_units.source',
            'geo_units.source_id',
            'geo_units.parent_source_id',
            'geo_units.normalized_name',
            'geo_units.admin_level',
            'geo_units.level',
            'geo_units.boundary',
        ])->withCount('children');
    }

    public static function resourceSchemesFormComponents(): array
    {
        return [
            Section::make('Схемы по видам ресурсов')
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
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Название')
                ->required()
                ->maxLength(255)
                ->visibleOn('create'),
            Select::make('parent_id')
                ->label('Родитель')
                ->relationship('parent', 'name')
                ->searchable()
                ->nullable()
                ->visibleOn('create'),
            Toggle::make('is_active')
                ->label('Активен на карте'),

            ...static::resourceSchemesFormComponents(),

            Section::make('Служебные поля')
                ->collapsed()
                ->schema([
                    TextInput::make('source')
                        ->label('Источник')
                        ->required()
                        ->maxLength(32)
                        ->disabled(fn (?GeoUnit $record): bool => $record !== null),
                    TextInput::make('source_id')
                        ->label('ID источника')
                        ->required()
                        ->maxLength(255)
                        ->disabled(fn (?GeoUnit $record): bool => $record !== null),
                    TextInput::make('parent_source_id')
                        ->label('ID родителя в источнике')
                        ->maxLength(255)
                        ->disabled(fn (?GeoUnit $record): bool => $record !== null),
                    TextInput::make('normalized_name')
                        ->label('Нормализованное название')
                        ->required()
                        ->maxLength(255)
                        ->disabled(fn (?GeoUnit $record): bool => $record !== null),
                    TextInput::make('admin_level')
                        ->label('Уровень OSM')
                        ->numeric()
                        ->disabled(fn (?GeoUnit $record): bool => $record !== null),
                    TextInput::make('level')
                        ->label('Тип уровня')
                        ->maxLength(32)
                        ->disabled(fn (?GeoUnit $record): bool => $record !== null),
                    TextInput::make('boundary')
                        ->label('Тип границы')
                        ->maxLength(64)
                        ->disabled(fn (?GeoUnit $record): bool => $record !== null),
                ])
                ->columns(1),

        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columnManager(false)
            ->emptyStateHeading('Не найдено геообъектов')
            ->emptyStateDescription('Добавьте геообъект, чтобы начать.')
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
                    ->updateStateUsing(function (GeoUnit $record, ?bool $state): bool {
                        $isActive = (bool) $state;

                        if ($record->admin_level !== null && $record->admin_level >= 5) {
                            $record->is_active = $isActive;
                            $record->save();
                        } else {
                            $record->setActiveWithDescendants($isActive);
                        }

                        return (bool) $record->is_active;
                    })
                    ->sortable(),
                ToggleColumn::make('descendants_active')
                    ->label('Наследники')
                    ->getStateUsing(fn (GeoUnit $record): bool => $record->hasActiveDescendants())
                    ->updateStateUsing(function (GeoUnit $record, ?bool $state): bool {
                        $record->setDescendantsActive((bool) $state);

                        return (bool) $state;
                    })
                    ->visible(fn ($livewire): bool => $livewire instanceof ListGeoUnits && $livewire->shouldShowDescendantStatusColumn())
                    ->disabled(fn (GeoUnit $record): bool => (int) ($record->children_count ?? 0) === 0),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkAction::make('activateSelected')
                    ->label('Активировать')
                    ->requiresConfirmation()
                    ->fetchSelectedRecords(false)
                    ->action(function (BulkAction $action): void {
                        GeoUnit::setActiveForIdsWithDescendants(
                            $action->getSelectedRecordsQuery()->pluck('id'),
                            true,
                        );
                    }),
                BulkAction::make('deactivateSelected')
                    ->label('Деактивировать')
                    ->requiresConfirmation()
                    ->fetchSelectedRecords(false)
                    ->action(function (BulkAction $action): void {
                        GeoUnit::setActiveForIdsWithDescendants(
                            $action->getSelectedRecordsQuery()->pluck('id'),
                            false,
                        );
                    }),
                BulkAction::make('activateSelectedDescendants')
                    ->label('Активировать наследников')
                    ->requiresConfirmation()
                    ->fetchSelectedRecords(false)
                    ->action(function (BulkAction $action): void {
                        GeoUnit::setActiveForDescendants(
                            $action->getSelectedRecordsQuery()->pluck('id'),
                            true,
                        );
                    }),
                BulkAction::make('deactivateSelectedDescendants')
                    ->label('Деактивировать наследников')
                    ->requiresConfirmation()
                    ->fetchSelectedRecords(false)
                    ->action(function (BulkAction $action): void {
                        GeoUnit::setActiveForDescendants(
                            $action->getSelectedRecordsQuery()->pluck('id'),
                            false,
                        );
                    }),
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
