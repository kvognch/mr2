<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ContractorResource\Pages\ListContractors;
use App\Filament\Resources\ContractorResource\Pages\CreateContractor;
use App\Filament\Resources\ContractorResource\Pages\EditContractor;
use App\Filament\Forms\Components\TerritoryTreeSelect;
use App\Filament\Resources\ContractorResource\Pages;
use App\Models\Contractor;
use App\Models\GeoUnit;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

class ContractorResource extends Resource
{
    protected static ?string $model = Contractor::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string | \UnitEnum | null $navigationGroup = 'Подрядчики';

    protected static ?string $navigationLabel = 'Подрядчики';

    protected static ?string $modelLabel = 'подрядчик';

    protected static ?string $pluralModelLabel = 'подрядчики';

    protected static ?string $breadcrumb = 'Подрядчики';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 10;

    protected static ?array $territoryTreeCache = null;

    protected static ?array $territoryDescendantsCache = null;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('О компании')
                ->collapsible()
                ->collapsed()
                ->schema([
                    TextInput::make('short_name')
                        ->label('Краткое название')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('full_name')
                        ->label('Полное название')
                        ->maxLength(255),
                    Select::make('business_segments')
                        ->label('Сегмент бизнеса')
                        ->multiple()
                        ->options([
                            'b2b' => 'B2B - для бизнеса',
                            'b2c' => 'B2C - для клиента',
                        ]),
                    TextInput::make('website')
                        ->label('Сайт')
                        ->url()
                        ->maxLength(255),
                    Grid::make(3)
                        ->schema([
                            TextInput::make('social_telegram')
                                ->label('Telegram')
                                ->url()
                                ->maxLength(255),
                            TextInput::make('social_vk')
                                ->label('ВКонтакте')
                                ->url()
                                ->maxLength(255),
                            TextInput::make('social_whatsapp')
                                ->label('WhatsApp')
                                ->maxLength(255),
                        ])
                        ->columns(1)
                        ->columnSpanFull(),
                    TextInput::make('phone')
                        ->label('Телефон')
                        ->type('tel')
                        ->maxLength(64),
                    TextInput::make('email')
                        ->label('Электронная почта')
                        ->email()
                        ->maxLength(255),
                    Select::make('categories')
                        ->label('Категория')
                        ->relationship('categories', 'name')
                        ->multiple()
                        ->required()
                        ->preload()
                        ->searchable(),
                    TextInput::make('response_time')
                        ->label('Сроки ответа')
                        ->maxLength(255),
                    TextInput::make('work_volume')
                        ->label('Объем выполняемых работ, ₽')
                        ->maxLength(255),
                    TerritoryTreeSelect::make('territory_ids')
                        ->label('Территория работы')
                        ->tree(fn (): array => static::getTerritoryTree())
                        ->descendants(fn (): array => static::getTerritoryDescendants())
                        ->columnSpanFull(),
                ])
                ->columns(1),

            Section::make('Выполняемые работы по видам ресурсов')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Fieldset::make('СМР')
                        ->columns(1)
                        ->schema([
                            Select::make('smrResourceTypes')
                                ->label('СМР (Строительно-монтажные работы)')
                                ->relationship('smrResourceTypes', 'name')
                                ->multiple()
                                ->preload()
                                ->searchable(),
                            Toggle::make('smr_has_sro')
                                ->label('Наличие СРО'),
                        ]),
                    Fieldset::make('ПИР/ПСД')
                        ->columns(1)
                        ->schema([
                            Select::make('pirResourceTypes')
                                ->label('ПИР/ПСД (Проектно-изыскательские работы / Проектно-сметная документация)')
                                ->relationship('pirResourceTypes', 'name')
                                ->multiple()
                                ->preload()
                                ->searchable(),
                            Toggle::make('pir_has_sro')
                                ->label('Наличие СРО'),
                        ]),
                ])
                ->columns(1),

            Section::make('Реквизиты')
                ->collapsible()
                ->collapsed()
                ->schema([
                    TextInput::make('ogrn')->label('ОГРН')->maxLength(32),
                    TextInput::make('inn')->label('ИНН')->maxLength(32),
                    TextInput::make('kpp')->label('КПП')->maxLength(32),
                    DatePicker::make('registration_date')->label('Дата регистрации'),
                    TextInput::make('legal_address')
                        ->label('Юридический адрес')
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Repeater::make('branch_contacts')
                        ->label('Адреса и телефоны филиалов')
                        ->itemLabel(fn (): string => 'Филиал')
                        ->addActionLabel('Добавить')
                        ->schema([
                            TextInput::make('value')
                                ->hiddenLabel()
                                ->required(),
                        ])
                        ->defaultItems(0)
                        ->reorderable(false)
                        ->columnSpanFull(),
                ])
                ->columns(1),

            Section::make('Примечания')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Textarea::make('additional_info')
                        ->label('Дополнительная информация')
                        ->rows(5)
                        ->columnSpanFull(),
                ]),

            Section::make('Публикация и ответственность')
                ->visible(fn () => ! auth()->user()?->isClient())
                ->collapsible()
                ->collapsed()
                ->schema([
                    Select::make('rating_id')
                        ->label('Рейтинг')
                        ->relationship('rating', 'name', fn ($query) => $query->orderBy('sort_order'))
                        ->searchable()
                        ->preload(),
                    Select::make('status')
                        ->label('Статус')
                        ->required()
                        ->options([
                            'pending' => 'На рассмотрении',
                            'approved' => 'Одобрен',
                            'rejected' => 'Отклонён',
                        ])
                        ->default('pending'),
                    Select::make('owner_id')
                        ->label('Владелец')
                        ->relationship('owner', 'name')
                        ->searchable()
                        ->preload()
                        ->default(fn () => auth()->id()),
                ])
                ->columns(1),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columnManager(false)
            ->columns([
                TextColumn::make('short_name')
                    ->label('Краткое название')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('categories.name')
                    ->label('Категории')
                    ->badge()
                    ->separator(', ')
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'approved' => 'Одобрен',
                        'rejected' => 'Отклонён',
                        default => 'На рассмотрении',
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'На рассмотрении',
                        'approved' => 'Одобрен',
                        'rejected' => 'Отклонён',
                    ]),
                SelectFilter::make('owner_id')
                    ->label('Владелец')
                    ->relationship('owner', 'name')
                    ->visible(fn () => ! auth()->user()?->isClient()),
                SelectFilter::make('categories')
                    ->label('Категория')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('short_name');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->orderedForModeration();
        $user = auth()->user();

        if ($user?->isClient()) {
            $query->where('owner_id', $user->id);
        }

        return $query;
    }

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()
            ->where('status', 'pending')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string | array | null
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContractors::route('/'),
            'create' => CreateContractor::route('/create'),
            'edit' => EditContractor::route('/{record}/edit'),
        ];
    }

    public static function getTerritoryTree(): array
    {
        static::warmupTerritoryCache();

        return static::$territoryTreeCache ?? [];
    }

    public static function getTerritoryDescendants(): array
    {
        static::warmupTerritoryCache();

        return static::$territoryDescendantsCache ?? [];
    }

    protected static function warmupTerritoryCache(): void
    {
        if (static::$territoryTreeCache !== null && static::$territoryDescendantsCache !== null) {
            return;
        }

        $units = GeoUnit::query()
            ->select(['id', 'name', 'parent_id', 'admin_level'])
            ->whereBetween('admin_level', [4, 8])
            ->orderBy('name')
            ->get();

        $childrenByParent = [];

        foreach ($units as $unit) {
            $parentId = $unit->parent_id ?? 0;
            $childrenByParent[$parentId][] = [
                'id' => (int) $unit->id,
                'name' => $unit->name,
                'parent_id' => $unit->parent_id,
                'admin_level' => (int) $unit->admin_level,
            ];
        }

        $roots = $units
            ->where('admin_level', 4)
            ->sortBy('name')
            ->values()
            ->all();

        $descendants = [];
        $tree = [];

        foreach ($roots as $root) {
            $tree[] = static::buildNodeTree((int) $root->id, $root->name, $childrenByParent, $descendants);
        }

        static::$territoryTreeCache = $tree;
        static::$territoryDescendantsCache = $descendants;
    }

    protected static function buildNodeTree(int $id, string $name, array $childrenByParent, array &$descendants): array
    {
        $childrenNodes = [];
        $childIds = [];

        foreach ($childrenByParent[$id] ?? [] as $child) {
            $childNode = static::buildNodeTree((int) $child['id'], (string) $child['name'], $childrenByParent, $descendants);
            $childrenNodes[] = $childNode;
            $childIds[] = (int) $child['id'];
            $childIds = [...$childIds, ...($descendants[(int) $child['id']] ?? [])];
        }

        $descendants[$id] = array_values(array_unique($childIds));

        return [
            'id' => $id,
            'name' => $name,
            'children' => $childrenNodes,
        ];
    }
}
