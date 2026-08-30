<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Components\Html;
use App\Filament\Resources\ContractorResource\Pages\ListContractors;
use App\Filament\Resources\ContractorResource\Pages\ListContractorCategoryContractors;
use App\Filament\Resources\ContractorResource\Pages\CreateContractor;
use App\Filament\Resources\ContractorResource\Pages\EditContractor;
use App\Filament\Resources\ContractorResource\Pages\ListGuaranteeingSuppliers;
use App\Filament\Resources\ContractorResource\Pages\ListResourceSupplyingOrganizations;
use App\Filament\Forms\Components\TerritoryTreeSelect;
use App\Filament\Resources\ContractorResource\Pages;
use App\Models\Contractor;
use App\Models\ContractorCategory;
use App\Models\ContractorTariff;
use App\Models\GeoUnit;
use App\Models\User;
use Filament\Navigation\NavigationItem;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use BackedEnum;
use UnitEnum;

class ContractorResource extends Resource
{
    protected static ?string $model = Contractor::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string | \UnitEnum | null $navigationGroup = 'Организации';

    protected static ?string $navigationLabel = 'Подрядчики';

    protected static ?string $modelLabel = 'подрядчик';

    protected static ?string $pluralModelLabel = 'подрядчики';

    protected static ?string $breadcrumb = 'Организации';

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
                            TextInput::make('social_max')
                                ->label('Max')
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
                        ->live()
                        ->preload()
                        ->searchable(),
                    TextInput::make('response_time')
                        ->label('Сроки ответа')
                        ->visible(fn (Get $get): bool => static::shouldShowContractorWorkFields($get('categories')))
                        ->maxLength(255),
                    TextInput::make('work_volume')
                        ->label('Объем выполняемых работ, ₽')
                        ->visible(fn (Get $get): bool => static::shouldShowContractorWorkFields($get('categories')))
                        ->maxLength(255),
                    TextInput::make('application_url')
                        ->label('Ссылка на форму заявки')
                        ->url()
                        ->visible(fn (Get $get): bool => static::hasTariffCategorySelected($get('categories')))
                        ->maxLength(255),
                    FileUpload::make('connection_tariff_upload')
                        ->label('Действующий тариф (подключение)')
                        ->disk('public')
                        ->directory('contractor-tariffs')
                        ->downloadable()
                        ->openable()
                        ->preserveFilenames()
                        ->rules(['mimes:pdf,doc,docx,xls,xlsx,csv,txt,rtf,odt,ods,ppt,pptx'])
                        ->helperText('При загрузке нового файла предыдущий действующий тариф попадёт в историю тарифов.')
                        ->visible(fn (Get $get): bool => static::hasResourceSupplyingCategorySelected($get('categories')))
                        ->columnSpanFull(),
                    Html::make(fn (?Contractor $record): HtmlString => new HtmlString(static::renderTariffsAdminHtml(
                        $record,
                        ContractorTariff::TYPE_CONNECTION,
                        'Действующий тариф (подключение)',
                    )))
                        ->visible(fn (Get $get): bool => static::hasResourceSupplyingCategorySelected($get('categories')))
                        ->columnSpanFull(),
                    FileUpload::make('sales_tariff_upload')
                        ->label('Действующий тариф (сбыт)')
                        ->disk('public')
                        ->directory('contractor-tariffs')
                        ->downloadable()
                        ->openable()
                        ->preserveFilenames()
                        ->rules(['mimes:pdf,doc,docx,xls,xlsx,csv,txt,rtf,odt,ods,ppt,pptx'])
                        ->helperText('При загрузке нового файла предыдущий действующий тариф попадёт в историю тарифов.')
                        ->visible(fn (Get $get): bool => static::hasGuaranteeingSupplierCategorySelected($get('categories')))
                        ->columnSpanFull(),
                    Html::make(fn (?Contractor $record): HtmlString => new HtmlString(static::renderTariffsAdminHtml(
                        $record,
                        ContractorTariff::TYPE_SALES,
                        'Действующий тариф (сбыт)',
                    )))
                        ->visible(fn (Get $get): bool => static::hasGuaranteeingSupplierCategorySelected($get('categories')))
                        ->columnSpanFull(),
                    TerritoryTreeSelect::make('territory_ids')
                        ->label('Территория работы')
                        ->tree(fn (): array => static::getTerritoryTree())
                        ->descendants(fn (): array => static::getTerritoryDescendants())
                        ->manageSchemes(fn (): bool => (bool) (auth()->user()?->isSuperadmin() || auth()->user()?->isManager()))
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
                TextColumn::make('edit')
                    ->label('Изменить')
                    ->state('Изменить')
                    ->url(fn (Contractor $record): string => static::getUrl('edit', ['record' => $record]))
                    ->color('primary'),
                TextColumn::make('categories.name')
                    ->label('Категории')
                    ->width('240px')
                    ->wrap()
                    ->extraCellAttributes(['style' => 'max-width: 240px; white-space: normal;'])
                    ->badge()
                    ->listWithLineBreaks()
                    ->limitList(5)
                    ->expandableLimitedList(),
                TextColumn::make('territories.name')
                    ->label('Территория работы')
                    ->width('240px')
                    ->wrap()
                    ->extraCellAttributes(['style' => 'max-width: 240px; white-space: normal;'])
                    ->badge()
                    ->listWithLineBreaks()
                    ->limitList(5)
                    ->expandableLimitedList(),
                TextColumn::make('smrResourceTypes.name')
                    ->label('СМР')
                    ->width('240px')
                    ->wrap()
                    ->extraCellAttributes(['style' => 'max-width: 240px; white-space: normal;'])
                    ->badge()
                    ->listWithLineBreaks()
                    ->limitList(5)
                    ->expandableLimitedList(),
                TextColumn::make('pirResourceTypes.name')
                    ->label('ПИР/ПСД')
                    ->width('240px')
                    ->wrap()
                    ->extraCellAttributes(['style' => 'max-width: 240px; white-space: normal;'])
                    ->badge()
                    ->listWithLineBreaks()
                    ->limitList(5)
                    ->expandableLimitedList(),
                TextColumn::make('business_segments')
                    ->label('Сегмент бизнеса')
                    ->badge()
                    ->state(fn (Contractor $record): string => collect($record->business_segments ?? [])
                        ->map(fn (string $segment): string => match ($segment) {
                            'b2b' => 'B2B',
                            'b2c' => 'B2C',
                            default => mb_strtoupper($segment),
                        })
                        ->join(', ')),
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
                SelectFilter::make('categories')
                    ->label('Категория')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->preload(),
                SelectFilter::make('territories')
                    ->label('Территория работы')
                    ->relationship(
                        'territories',
                        'name',
                        fn (Builder $query) => $query
                            ->select(['geo_units.id', 'geo_units.name'])
                            ->orderBy('geo_units.name')
                    )
                    ->multiple()
                    ->searchable()
                    ->preload(),
                SelectFilter::make('smrResourceTypes')
                    ->label('СМР')
                    ->relationship('smrResourceTypes', 'name', fn (Builder $query) => $query->orderBy('name'))
                    ->multiple()
                    ->searchable()
                    ->preload(),
                SelectFilter::make('pirResourceTypes')
                    ->label('ПИР/ПСД')
                    ->relationship('pirResourceTypes', 'name', fn (Builder $query) => $query->orderBy('name'))
                    ->multiple()
                    ->searchable()
                    ->preload(),
                SelectFilter::make('business_segments')
                    ->label('Сегмент бизнеса')
                    ->options([
                        'b2b' => 'B2B - для бизнеса',
                        'b2c' => 'B2C - для клиента',
                    ])
                    ->multiple()
                    ->query(function (Builder $query, array $data): Builder {
                        $segments = collect($data['values'] ?? [])
                            ->filter()
                            ->values();

                        if ($segments->isEmpty()) {
                            return $query;
                        }

                        return $query->where(function (Builder $query) use ($segments): void {
                            foreach ($segments as $segment) {
                                $query->orWhereJsonContains('business_segments', $segment);
                            }
                        });
                    }),
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

    protected static function hasTariffCategorySelected(mixed $categoryIds): bool
    {
        return static::hasCategorySelected($categoryIds, 'Гарантирующий поставщик')
            || static::hasCategorySelected($categoryIds, 'Ресурсо-снабжающая организация');
    }

    protected static function hasGuaranteeingSupplierCategorySelected(mixed $categoryIds): bool
    {
        return static::hasCategorySelected($categoryIds, 'Гарантирующий поставщик');
    }

    protected static function hasResourceSupplyingCategorySelected(mixed $categoryIds): bool
    {
        return static::hasCategorySelected($categoryIds, 'Ресурсо-снабжающая организация');
    }

    protected static function hasCategorySelected(mixed $categoryIds, string $categoryName): bool
    {
        $categoryIds = collect($categoryIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        if ($categoryIds->isEmpty()) {
            return false;
        }

        return ContractorCategory::query()
            ->whereIn('id', $categoryIds)
            ->where('name', $categoryName)
            ->exists();
    }

    protected static function shouldShowContractorWorkFields(mixed $categoryIds): bool
    {
        $categoryIds = collect($categoryIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        if ($categoryIds->isEmpty()) {
            return true;
        }

        return ContractorCategory::query()
            ->whereIn('id', $categoryIds)
            ->where('name', 'Подрядчик')
            ->exists()
            || ! static::hasTariffCategorySelected($categoryIds);
    }

    protected static function renderTariffsAdminHtml(?Contractor $contractor, string $tariffType, string $tariffLabel): string
    {
        if (! $contractor?->exists) {
            return '<div class="text-sm text-gray-500">Тарифы появятся после сохранения подрядчика.</div>';
        }

        $currentRelation = $tariffType === ContractorTariff::TYPE_SALES
            ? 'currentSalesTariff'
            : 'currentConnectionTariff';
        $historyRelation = $tariffType === ContractorTariff::TYPE_SALES
            ? 'salesTariffHistory'
            : 'connectionTariffHistory';
        $historyLabel = $tariffType === ContractorTariff::TYPE_SALES
            ? 'История тарифов (сбыт)'
            : 'История тарифов (подключение)';

        $contractor->loadMissing([$currentRelation, $historyRelation]);

        $currentTariff = $contractor->{$currentRelation}->first();
        $history = $contractor->{$historyRelation};

        $html = '<div class="space-y-3 rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm" data-tariffs-admin>';
        $html .= '<div class="font-medium text-gray-950">Загруженные тарифы</div>';
        $html .= sprintf('<div><div class="mb-1 text-gray-600">%s</div><div data-current-tariff>', e($tariffLabel));

        if ($currentTariff) {
            $html .= sprintf(
                '<div class="flex flex-wrap items-center gap-2" data-tariff-row><a class="min-w-0 break-all text-primary-600 underline underline-offset-2" href="%s" target="_blank" rel="noopener noreferrer">%s</a>%s</div>',
                e($currentTariff->url),
                e($currentTariff->original_name),
                static::renderTariffDeleteButton($currentTariff->id, true)
            );
        } else {
            $html .= '<div class="text-gray-500">Не загружен</div>';
        }

        $html .= '</div></div>';

        if ($history->isNotEmpty()) {
            $html .= sprintf('<div data-tariff-history><div class="mb-1 text-gray-600">%s</div><div class="space-y-1" data-tariff-history-list>', e($historyLabel));

            foreach ($history as $tariff) {
                $html .= sprintf(
                    '<div class="flex flex-wrap items-center gap-2" data-tariff-row><a class="min-w-0 break-all text-primary-600 underline underline-offset-2" href="%s" target="_blank" rel="noopener noreferrer">%s</a>%s</div>',
                    e($tariff->url),
                    e($tariff->original_name),
                    static::renderTariffDeleteButton($tariff->id)
                );
            }

            $html .= '</div></div>';
        }

        $html .= '</div>';

        return $html;
    }

    protected static function renderTariffDeleteButton(int $tariffId, bool $isCurrent = false): string
    {
        return sprintf(
            '<button type="button" class="text-xs font-medium text-danger-600 hover:underline underline-offset-2" onclick="event.preventDefault(); event.stopPropagation(); var button = this; if (button.dataset.deleting === \'1\') return false; button.dataset.deleting = \'1\'; if (! confirm(\'Удалить этот тариф?\')) { button.dataset.deleting = \'\'; return false; } fetch(\'%s\', { method: \'DELETE\', headers: { \'X-CSRF-TOKEN\': \'%s\', \'Accept\': \'application/json\' }, credentials: \'same-origin\' }).then(function (response) { if (! response.ok) throw new Error(); var root = button.closest(\'[data-tariffs-admin]\'); var row = button.closest(\'[data-tariff-row]\'); if (%s) { var current = root && root.querySelector(\'[data-current-tariff]\'); if (current) { current.replaceChildren(); var empty = document.createElement(\'div\'); empty.className = \'text-gray-500\'; empty.textContent = \'Не загружен\'; current.appendChild(empty); } } else { if (row) row.remove(); var history = root && root.querySelector(\'[data-tariff-history]\'); var historyList = root && root.querySelector(\'[data-tariff-history-list]\'); if (history && historyList && ! historyList.querySelector(\'[data-tariff-row]\')) history.remove(); } }).catch(function () { button.dataset.deleting = \'\'; alert(\'Не удалось удалить тариф.\'); }); return false;">Удалить</button>',
            e(route('contractor-tariffs.destroy', ['contractorTariff' => $tariffId])),
            e(csrf_token()),
            $isCurrent ? 'true' : 'false'
        );
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

    protected static function getPendingCountForCategory(string $categoryName): ?string
    {
        $count = static::getEloquentQuery()
            ->where('status', 'pending')
            ->whereHas('categories', fn (Builder $query) => $query->where('name', $categoryName))
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make('Гарантирующие поставщики')
                ->group(static::getNavigationGroup())
                ->icon(static::getNavigationIcon())
                ->isActiveWhen(fn (): bool => request()->routeIs(static::getRouteBaseName() . '.guaranteeing-suppliers'))
                ->sort(10)
                ->badge(fn (): ?string => static::getPendingCountForCategory('Гарантирующий поставщик'), color: 'warning')
                ->url(static::getUrl('guaranteeing-suppliers')),
            NavigationItem::make('Подрядчики')
                ->group(static::getNavigationGroup())
                ->icon(static::getNavigationIcon())
                ->isActiveWhen(fn (): bool => request()->routeIs(static::getRouteBaseName() . '.category-contractors'))
                ->sort(12)
                ->badge(fn (): ?string => static::getPendingCountForCategory('Подрядчик'), color: 'warning')
                ->url(static::getUrl('category-contractors')),
            NavigationItem::make('РСО')
                ->group(static::getNavigationGroup())
                ->icon(static::getNavigationIcon())
                ->isActiveWhen(fn (): bool => request()->routeIs(static::getRouteBaseName() . '.resource-supplying-organizations'))
                ->sort(11)
                ->badge(fn (): ?string => static::getPendingCountForCategory('Ресурсо-снабжающая организация'), color: 'warning')
                ->url(static::getUrl('resource-supplying-organizations')),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContractors::route('/'),
            'guaranteeing-suppliers' => ListGuaranteeingSuppliers::route('/guaranteeing-suppliers'),
            'category-contractors' => ListContractorCategoryContractors::route('/contractors'),
            'resource-supplying-organizations' => ListResourceSupplyingOrganizations::route('/resource-supplying-organizations'),
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
