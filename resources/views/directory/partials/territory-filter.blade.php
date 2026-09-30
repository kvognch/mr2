<div
    x-data="{
        territories: @js($territoryOptions),
        selectedPath: @js(array_values($selectedTerritoryPath)),
        labels: ['Регион', 'Район', 'Муниципальное образование', 'Населённый пункт'],
        placeholders: ['Выберите регион', 'Выберите район', 'Выберите МО', 'Выберите населённый пункт'],
        emptyLabels: ['Любой регион', 'Любой район', 'Любое муниципальное образование', 'Любой населённый пункт'],
        get levels() {
            let options = this.territories.filter((territory) => Number(territory.admin_level) === 4);
            const levels = [];

            for (let depth = 0; depth < 12 && options.length > 0; depth++) {
                levels.push({ depth, options });

                const selectedId = this.selectedPath[depth];
                if (!selectedId) break;

                const selected = options.find((territory) => Number(territory.id) === Number(selectedId));
                if (!selected) break;

                options = this.territories.filter((territory) => Number(territory.parent_id) === Number(selectedId));
            }

            return levels;
        },
        selectedName(level) {
            const selectedId = this.selectedPath[level.depth];
            const selected = level.options.find((territory) => Number(territory.id) === Number(selectedId));

            return selected?.name || this.placeholders[level.depth] || 'Выберите территорию';
        },
        selectTerritory(depth, id) {
            if (id !== null && Number(this.selectedPath[depth]) === Number(id)) return;

            const nextPath = this.selectedPath.slice(0, depth);
            if (id !== null) nextPath.push(Number(id));
            this.selectedPath = nextPath;
        },
        restoreTerritoryPath() {
            const params = new URLSearchParams(window.location.search);
            const path = [];

            for (let depth = 0; depth < 12; depth++) {
                const id = params.get(`territory_path[${depth}]`);
                if (!id) break;
                path.push(Number(id));
            }

            if (path.length === 0) {
                const legacyId = params.get('territory_id');
                let current = this.territories.find((territory) => Number(territory.id) === Number(legacyId));
                const visited = new Set();

                while (current && !visited.has(Number(current.id))) {
                    visited.add(Number(current.id));
                    path.push(Number(current.id));
                    if (Number(current.admin_level) === 4) break;
                    current = this.territories.find((territory) => Number(territory.id) === Number(current.parent_id));
                }

                if (path.length > 0 && Number(this.territories.find((territory) => Number(territory.id) === path[path.length - 1])?.admin_level) !== 4) {
                    path.length = 0;
                } else {
                    path.reverse();
                }
            }

            this.selectedPath = path;
        },
    }"
    @directory-search-restored.window="restoreTerritoryPath()"
    class="space-y-5"
>
    <template x-for="level in levels" :key="level.depth">
        <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
            <input type="hidden" :name="`territory_path[${level.depth}]`" :value="selectedPath[level.depth] ?? ''">
            <button
                type="button"
                class="h-10 xl:h-12.5 w-full text-sm/5.5 xl:text_6 flex-between gap-2.5 text-brand-gray-dark bg-brand-gray-light-2 outline-brand-dark rounded-xl py-2.5 px-5"
                :aria-label="labels[level.depth] || 'Территория'"
                @click="open = !open"
                :aria-expanded="open"
            >
                <span class="min-w-0 flex-1 text-left line-clamp-1" :class="selectedPath[level.depth] && 'text-brand-dark'" x-text="selectedName(level)"></span>
                <svg width="17" height="10" class="shrink-0 transition-transform duration-200" :class="open && 'rotate-180'" aria-hidden="true">
                    <use href="#icon-select-chevron" />
                </svg>
            </button>

            <ul
                x-show="open"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-1"
                class="w-full min-w-[200px] max-h-96 2xl:max-h-120 absolute top-full left-0 space-y-0.75 bg-white rounded-xl overflow-y-auto mt-4 z-20 shadow-lg"
            >
                <li>
                    <button
                        type="button"
                        class="w-full flex-between text-base text-left hover:bg-brand-blue hover:text-white smooth px-5 py-2.5"
                        @click="selectTerritory(level.depth, null); open = false"
                    >
                        <span x-text="emptyLabels[level.depth] || 'Любая территория'"></span>
                        <svg x-show="!selectedPath[level.depth]" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="shrink-0 size-5">
                            <path d="M9.00016 16.17L4.83016 12L3.41016 13.41L9.00016 19L21.0002 6.99997L19.5902 5.58997L9.00016 16.17Z" fill="currentColor" />
                        </svg>
                    </button>
                </li>
                <template x-for="territory in level.options" :key="territory.id">
                    <li>
                        <button
                            type="button"
                            class="w-full flex-between text-base text-left hover:bg-brand-blue hover:text-white smooth px-5 py-2.5"
                            @click="selectTerritory(level.depth, territory.id); open = false"
                        >
                            <span x-text="territory.name"></span>
                            <svg x-show="Number(selectedPath[level.depth]) === Number(territory.id)" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="shrink-0 size-5">
                                <path d="M9.00016 16.17L4.83016 12L3.41016 13.41L9.00016 19L21.0002 6.99997L19.5902 5.58997L9.00016 16.17Z" fill="currentColor" />
                            </svg>
                        </button>
                    </li>
                </template>
            </ul>
        </div>
    </template>
</div>
