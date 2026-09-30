<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use App\Models\ContractorCategory;
use App\Models\GeoUnit;
use App\Models\Rating;
use App\Models\ResourceType;
use App\Support\HomepageSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DirectoryController extends Controller
{
    public function catalog(Request $request): View
    {
        return $this->renderDirectory($request, 'catalog');
    }

    public function catalogCategory(Request $request, string $slug): View
    {
        $category = ContractorCategory::query()->where('slug', $slug)->firstOrFail();

        return $this->renderDirectory($request, 'catalog', $category);
    }

    public function directions(Request $request): View
    {
        return $this->renderDirectory($request, 'directions');
    }

    public function direction(Request $request, string $slug): View
    {
        $resourceType = ResourceType::query()->where('slug', $slug)->firstOrFail();

        return $this->renderDirectory($request, 'directions', null, $resourceType);
    }

    private function renderDirectory(
        Request $request,
        string $section,
        ?ContractorCategory $category = null,
        ?ResourceType $resourceType = null,
    ): View {
        $isCatalog = $section === 'catalog';
        $allCategories = ContractorCategory::query()->orderBy('name')->get();
        $allResourceTypes = ResourceType::query()->orderBy('name')->get();
        $rootCategories = $this->sortCategories($allCategories->whereNull('parent_id')->values());
        $rootResourceTypes = $this->sortResourceTypes($allResourceTypes->whereNull('parent_id')->values());

        $selectedCategoryId = ! $isCatalog || $category === null
            ? $this->validSelection($request->query('organization_type'), $allCategories)
            : null;
        $selectedDirectionId = $isCatalog || $resourceType === null
            ? $this->validSelection($request->query('direction_id'), $allResourceTypes)
            : null;
        $topicItems = $isCatalog ? $allCategories : $allResourceTypes;
        $rootTopics = $isCatalog ? $rootCategories : $rootResourceTypes;
        $selectedTopicId = $isCatalog
            ? ($category?->id ?? $selectedCategoryId)
            : ($resourceType?->id ?? $selectedDirectionId);
        $activeTopic = $selectedTopicId !== null
            ? $topicItems->firstWhere('id', (int) $selectedTopicId)
            : null;
        $topicTrail = [];
        $visitedTopicIds = [];
        $currentTopic = $activeTopic;

        while ($currentTopic && ! isset($visitedTopicIds[(int) $currentTopic->id])) {
            $visitedTopicIds[(int) $currentTopic->id] = true;
            $topicTrail[] = $currentTopic;
            $currentTopic = $currentTopic->parent_id !== null
                ? $topicItems->firstWhere('id', (int) $currentTopic->parent_id)
                : null;
        }

        $topicTrail = array_reverse($topicTrail);
        $topicLevels = [['label' => null, 'topics' => $rootTopics]];
        foreach ($topicTrail as $trailTopic) {
            $children = $topicItems->where('parent_id', $trailTopic->id)->values();

            if ($children->isNotEmpty()) {
                $topicLevels[] = [
                    'label' => 'Подкатегории «'.$trailTopic->name.'»',
                    'topics' => $children,
                ];
            }
        }

        $activeTopicIds = array_map(fn ($topic): int => (int) $topic->id, $topicTrail);
        $territories = $this->activeTerritories();
        [$selectedTerritoryId, $selectedTerritoryPath] = $this->territorySelection($request, $territories);
        $territoryOptions = $territories->map(fn (GeoUnit $territory): array => [
            'id' => (int) $territory->id,
            'parent_id' => $territory->parent_id === null ? null : (int) $territory->parent_id,
            'name' => $territory->name,
            'admin_level' => (int) $territory->admin_level,
        ])->values()->all();
        $ratings = Rating::query()->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
        $selectedRatingId = $this->validSelection($request->query('rating_id'), $ratings);
        $sortOrder = $request->query('sort') === 'rating' ? 'rating' : 'name';

        $categoryIds = $category?->descendantIds() ?? [];
        $resourceIds = $resourceType?->descendantIds() ?? [];
        $selectedCategory = $selectedCategoryId !== null
            ? $allCategories->firstWhere('id', $selectedCategoryId)
            : null;
        $selectedDirection = $selectedDirectionId !== null
            ? $allResourceTypes->firstWhere('id', $selectedDirectionId)
            : null;

        if ($selectedCategory !== null) {
            $categoryIds = array_values(array_unique([...$categoryIds, ...$selectedCategory->descendantIds()]));
        }

        if ($selectedDirection !== null) {
            $resourceIds = array_values(array_unique([...$resourceIds, ...$selectedDirection->descendantIds()]));
        }

        $territoryIds = $selectedTerritoryId === null
            ? []
            : array_values(array_unique([
                ...$this->descendantIds($selectedTerritoryId, $territories),
                ...$selectedTerritoryPath,
            ]));

        $organizations = $this->organizationsQuery($categoryIds, $resourceIds, $territoryIds, $selectedRatingId, $sortOrder)
            ->paginate(12)
            ->withQueryString();

        $topicCards = collect($topicLevels)->map(function (array $level) use ($isCatalog, $activeTopicIds, $selectedTopicId): array {
            $cards = $level['topics']->map(function (ContractorCategory|ResourceType $topic) use ($isCatalog, $activeTopicIds, $selectedTopicId): array {
                $topicIds = $topic->descendantIds();
                $count = $isCatalog
                    ? $this->organizationsQuery($topicIds, [], [], null, 'name')->count()
                    : $this->organizationsQuery([], $topicIds, [], null, 'name')->count();

                return [
                    'topic' => $topic,
                    'count' => $count,
                    'count_label' => $this->organizationCountLabel($count),
                    'is_selected' => $selectedTopicId !== null && (int) $selectedTopicId === (int) $topic->id,
                    'is_ancestor' => in_array((int) $topic->id, $activeTopicIds, true)
                        && (int) $topic->id !== (int) $selectedTopicId,
                ];
            });

            return ['label' => $level['label'], 'cards' => $cards];
        })->filter(fn (array $level): bool => $level['cards']->isNotEmpty())->values();

        $categoryDropdownOptions = $this->dropdownOptions($this->hierarchicalOptions($allCategories), 'Все типы');
        $directionDropdownOptions = $this->dropdownOptions($this->hierarchicalOptions($allResourceTypes), 'Все направления');
        $ratingDropdownOptions = $ratings
            ->map(fn (Rating $rating): array => ['value' => (string) $rating->id, 'label' => $rating->name])
            ->prepend(['value' => '', 'label' => 'Любой рейтинг'])
            ->values()
            ->all();
        $sortDropdownOptions = [
            ['value' => 'name', 'label' => 'По названию'],
            ['value' => 'rating', 'label' => 'По рейтингу'],
        ];

        $directoryUrl = $this->directoryUrl($isCatalog, $category, $resourceType);
        $pageHeading = $isCatalog ? 'Каталог организаций' : 'Направления';
        $pageTitle = $isCatalog
            ? ($category?->meta_title ?: $category?->name ?: 'Каталог организаций')
            : ($resourceType?->meta_title ?: $resourceType?->name ?: 'Направления');
        $introText = $category?->intro_text ?: $resourceType?->intro_text;
        $seoText = $category?->seo_text ?: $resourceType?->seo_text;
        $defaultDescription = $category
            ? 'Организации категории «'.$category->name.'»: контакты, направления работы и территории обслуживания.'
            : ($resourceType
                ? 'Организации по направлению «'.$resourceType->name.'»: контакты, специализация и территории работы.'
                : ($isCatalog
                    ? 'Каталог гарантирующих поставщиков, ресурсоснабжающих организаций и подрядчиков.'
                    : 'Направления инженерных сетей и организации, работающие с каждым видом ресурса.'));
        $pageDescription = $category?->meta_description
            ?: $resourceType?->meta_description
            ?: trim(strip_tags((string) $introText))
            ?: $defaultDescription;

        return view('directory.index', [
            'settings' => HomepageSettings::all(),
            'section' => $section,
            'isCatalog' => $isCatalog,
            'category' => $category,
            'selectedCategory' => $selectedCategory,
            'selectedDirection' => $selectedDirection,
            'resourceType' => $resourceType,
            'rootCategories' => $rootCategories,
            'rootResourceTypes' => $rootResourceTypes,
            'topicCards' => $topicCards,
            'organizations' => $organizations,
            'territoryOptions' => $territoryOptions,
            'selectedTerritoryPath' => $selectedTerritoryPath,
            'categoryDropdownOptions' => $categoryDropdownOptions,
            'directionDropdownOptions' => $directionDropdownOptions,
            'ratingDropdownOptions' => $ratingDropdownOptions,
            'sortDropdownOptions' => $sortDropdownOptions,
            'ratings' => $ratings,
            'selectedCategoryId' => $selectedCategoryId,
            'selectedDirectionId' => $selectedDirectionId,
            'selectedTerritoryId' => $selectedTerritoryId,
            'selectedRatingId' => $selectedRatingId,
            'sortOrder' => $sortOrder,
            'directoryUrl' => $directoryUrl,
            'breadcrumbs' => $this->breadcrumbs($isCatalog, $category, $resourceType, $allCategories, $allResourceTypes),
            'pageHeading' => $pageHeading,
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'introText' => $introText,
            'seoText' => $seoText,
        ]);
    }

    /** @param array<int> $categoryIds @param array<int> $resourceIds @param array<int> $territoryIds */
    private function organizationsQuery(
        array $categoryIds,
        array $resourceIds,
        array $territoryIds,
        ?int $ratingId,
        string $sortOrder,
    ): Builder {
        $query = Contractor::query()
            ->where('contractors.status', 'approved')
            ->with([
                'categories:id,name,parent_id',
                'rating:id,name,sort_order',
                'territories:id,name,parent_id',
                'smrResourceTypes:id,name,abbreviation,icon',
                'pirResourceTypes:id,name,abbreviation,icon',
            ])
            ->when($categoryIds !== [], fn (Builder $query) => $query->whereHas(
                'categories',
                fn (Builder $relation) => $relation->whereIn('contractor_categories.id', $categoryIds),
            ))
            ->when($resourceIds !== [], fn (Builder $query) => $query->where(function (Builder $query) use ($resourceIds): void {
                $query->whereHas('smrResourceTypes', fn (Builder $relation) => $relation->whereIn('resource_types.id', $resourceIds))
                    ->orWhereHas('pirResourceTypes', fn (Builder $relation) => $relation->whereIn('resource_types.id', $resourceIds));
            }))
            ->when($territoryIds !== [], fn (Builder $query) => $query->whereHas(
                'territories',
                fn (Builder $relation) => $relation->whereIn('geo_units.id', $territoryIds),
            ))
            ->when($ratingId !== null, fn (Builder $query) => $query->where('contractors.rating_id', $ratingId));

        if ($sortOrder === 'rating') {
            $query->leftJoin('ratings as directory_ratings', 'contractors.rating_id', '=', 'directory_ratings.id')
                ->select('contractors.*')
                ->orderByRaw('directory_ratings.sort_order IS NULL')
                ->orderBy('directory_ratings.sort_order')
                ->orderBy('contractors.short_name');
        } else {
            $query->orderBy('contractors.short_name');
        }

        return $query;
    }

    /** @return Collection<int, GeoUnit> */
    private function activeTerritories(): Collection
    {
        $activeUnits = GeoUnit::query()
            ->active()
            ->where('admin_level', '>=', 4)
            ->get(['id', 'parent_id', 'name', 'admin_level']);

        if ($activeUnits->isEmpty()) {
            return collect();
        }

        $requiredIds = [];
        foreach ($activeUnits as $unit) {
            $requiredIds[(int) $unit->id] = true;
        }

        $pendingParentIds = $activeUnits
            ->pluck('parent_id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        while ($pendingParentIds !== []) {
            $parents = GeoUnit::query()
                ->whereIn('id', $pendingParentIds)
                ->where('admin_level', '>=', 4)
                ->get(['id', 'parent_id']);
            $nextParentIds = [];

            foreach ($parents as $parent) {
                $requiredIds[(int) $parent->id] = true;

                if ($parent->parent_id !== null && ! isset($requiredIds[(int) $parent->parent_id])) {
                    $nextParentIds[] = (int) $parent->parent_id;
                }
            }

            $pendingParentIds = array_values(array_unique($nextParentIds));
        }

        return GeoUnit::query()
            ->whereIn('id', array_keys($requiredIds))
            ->where('admin_level', '>=', 4)
            ->orderBy('admin_level')
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name', 'admin_level']);
    }

    /**
     * @param Collection<int, GeoUnit> $territories
     * @return array{0: ?int, 1: array<int>}
     */
    private function territorySelection(Request $request, Collection $territories): array
    {
        $territoryById = $territories->keyBy('id');
        $rootOptions = $territories->where('admin_level', 4)->values();
        $path = [];
        $requestedPath = $request->query('territory_path');

        if (is_array($requestedPath)) {
            $options = $rootOptions;

            for ($depth = 0; $depth < 12; $depth++) {
                $requestedId = $requestedPath[$depth] ?? null;
                if ($requestedId === null || $requestedId === '') {
                    break;
                }

                $selectedId = $this->validSelection($requestedId, $options);
                if ($selectedId === null) {
                    break;
                }

                $path[] = $selectedId;
                $options = $territories->where('parent_id', $selectedId)->values();
            }
        } else {
            // Support links created before the cascading territory filter was introduced.
            $legacyId = $this->validSelection($request->query('territory_id'), $territories);
            if ($legacyId !== null) {
                $reversePath = [];
                $current = $territoryById->get($legacyId);
                $visitedIds = [];

                while ($current && ! isset($visitedIds[(int) $current->id])) {
                    $visitedIds[(int) $current->id] = true;
                    $reversePath[] = (int) $current->id;

                    if ((int) $current->admin_level === 4) {
                        break;
                    }

                    $current = $current->parent_id !== null
                        ? $territoryById->get((int) $current->parent_id)
                        : null;
                }

                if ($current && (int) $current->admin_level === 4) {
                    $path = array_reverse($reversePath);
                }
            }
        }

        $selectedId = $path === [] ? null : $path[array_key_last($path)];

        return [$selectedId, $path];
    }

    /** @param Collection<int, ContractorCategory|ResourceType> $items @return array<int, string> */
    private function hierarchicalOptions(Collection $items): array
    {
        $byId = $items->keyBy('id');

        return $items->mapWithKeys(function (ContractorCategory|ResourceType $item) use ($byId): array {
            $labels = [$item->name];
            $parentId = $item->parent_id;
            $visited = [];

            while ($parentId !== null && ! isset($visited[$parentId]) && $byId->has($parentId)) {
                $visited[$parentId] = true;
                $parent = $byId->get($parentId);
                array_unshift($labels, $parent->name);
                $parentId = $parent->parent_id;
            }

            return [$item->id => implode(' / ', $labels)];
        })->all();
    }

    /** @param array<int, string> $options @return array<int, array{value: string, label: string}> */
    private function dropdownOptions(array $options, string $emptyLabel): array
    {
        $choices = [['value' => '', 'label' => $emptyLabel]];

        foreach ($options as $value => $label) {
            $choices[] = ['value' => (string) $value, 'label' => $label];
        }

        return $choices;
    }

    /** @param Collection<int, mixed> $items */
    private function validSelection(mixed $value, Collection $items): ?int
    {
        if (! is_scalar($value) || filter_var($value, FILTER_VALIDATE_INT) === false) {
            return null;
        }

        $id = (int) $value;

        return $id > 0 && $items->contains(fn ($item): bool => (int) $item->id === $id) ? $id : null;
    }

    /** @param Collection<int, GeoUnit> $territories @return array<int> */
    private function descendantIds(int $id, Collection $territories): array
    {
        $childrenByParent = [];

        foreach ($territories as $territory) {
            if ($territory->parent_id !== null) {
                $childrenByParent[(int) $territory->parent_id][] = (int) $territory->id;
            }
        }

        $ids = [$id];
        $pendingIds = [$id];

        while ($pendingIds !== []) {
            $children = [];

            foreach ($pendingIds as $parentId) {
                $children = [...$children, ...($childrenByParent[$parentId] ?? [])];
            }

            $children = array_values(array_diff(array_unique($children), $ids));
            $ids = [...$ids, ...$children];
            $pendingIds = $children;
        }

        return $ids;
    }

    /** @param Collection<int, ResourceType> $resourceTypes @return Collection<int, ResourceType> */
    private function sortResourceTypes(Collection $resourceTypes): Collection
    {
        $order = ['ГС' => 0, 'НК' => 1, 'НВ' => 2, 'ТС' => 3, 'ЭС' => 4];

        return $resourceTypes
            ->sortBy(fn (ResourceType $resourceType): int => $order[mb_strtoupper(trim((string) $resourceType->abbreviation))] ?? (100 + (int) $resourceType->id))
            ->values();
    }

    private function directoryUrl(bool $isCatalog, ?ContractorCategory $category, ?ResourceType $resourceType): string
    {
        if ($isCatalog) {
            return $category
                ? route('catalog.category', ['slug' => $category->slug])
                : route('catalog.index');
        }

        return $resourceType
            ? route('directions.show', ['slug' => $resourceType->slug])
            : route('directions.index');
    }

    /** @param Collection<int, ContractorCategory> $categories @param Collection<int, ResourceType> $resourceTypes @return array<int, array{label: string, url: ?string}> */
    private function breadcrumbs(
        bool $isCatalog,
        ?ContractorCategory $category,
        ?ResourceType $resourceType,
        Collection $categories,
        Collection $resourceTypes,
    ): array {
        $sectionLabel = $isCatalog ? 'Каталог организаций' : 'Направления';
        $baseUrl = $isCatalog ? route('catalog.index') : route('directions.index');
        $breadcrumbs = [['label' => $sectionLabel, 'url' => $baseUrl]];
        $item = $category ?? $resourceType;
        $items = $isCatalog ? $categories : $resourceTypes;

        if (! $item) {
            $breadcrumbs[0]['url'] = null;

            return $breadcrumbs;
        }

        $trail = [];
        $visited = [];
        $current = $item;

        while ($current) {
            $trail[] = $current;

            if ($current->parent_id === null || isset($visited[$current->parent_id])) {
                break;
            }

            $visited[$current->parent_id] = true;
            $current = $items->firstWhere('id', (int) $current->parent_id);
        }

        $trail = array_reverse($trail);

        foreach ($trail as $index => $trailItem) {
            $isCurrent = $index === array_key_last($trail);
            $url = $isCurrent
                ? null
                : ($isCatalog
                    ? route('catalog.category', ['slug' => $trailItem->slug])
                    : route('directions.show', ['slug' => $trailItem->slug]));
            $breadcrumbs[] = ['label' => $trailItem->name, 'url' => $url];
        }

        return $breadcrumbs;
    }

    /** @param Collection<int, ContractorCategory> $categories @return Collection<int, ContractorCategory> */
    private function sortCategories(Collection $categories): Collection
    {
        $order = [
            'Подрядчик' => 0,
            'Гарантирующий поставщик' => 1,
            'Ресурсо-снабжающая организация' => 2,
        ];

        return $categories
            ->sortBy(fn (ContractorCategory $category): int => $order[$category->name] ?? (100 + (int) $category->id))
            ->values();
    }

    private function organizationCountLabel(int $count): string
    {
        $lastTwoDigits = $count % 100;
        $lastDigit = $count % 10;

        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 14) {
            return 'организаций';
        }

        return match (true) {
            $lastDigit === 1 => 'организация',
            $lastDigit >= 2 && $lastDigit <= 4 => 'организации',
            default => 'организаций',
        };
    }
}
