@extends('layouts.app')

@section('title', $settings['meta']['search']['title'] ?? 'Поиск организаций')
@section('meta-description', $settings['meta']['search']['description'] ?? 'Поиск подрядчиков и ресурсоснабжающих организаций')
@section('body-attrs')
x-data="searchPage()" x-effect="window.setBodyScrollLock(mobileMenuOpen || requestModalOpen || authModalOpen || territorySchemesModalOpen)"
@endsection

@section('content')
    @php($searchPlaceholderIcon = file_get_contents(base_path('search.svg')))

    <svg class="absolute w-0 h-0 overflow-hidden" aria-hidden="true">
        <symbol id="icon-select-chevron" viewBox="0 0 17 10">
            <path d="M15.7945 1L8.39726 9L1 1" stroke="#8695AA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none" />
        </symbol>
    </svg>

    @include('shared.partials.header', ['settings' => $settings])

    <main class="bg-brand-gray-light-2 pb-20 lg:pb-30">
        <section id="search" class="pt-15 pb-30">
            <div class="container-base space-y-10">
                <div class="space-y-6 sm:space-y-8 lg:space-y-10">
                    <h1 class="text-lg/6.5 md:text-xl/6.25 xl:text-2xl/7.5 3xl:text-3xl/10">Поиск организации</h1>

                    <div class="grid lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-5" @keydown.enter.window.prevent="applySearch()">
                        <label class="sm:col-span-2">
                            <input
                                type="text"
                                class="h-12.5 w-full text_6 placeholder:text-brand-gray-dark bg-white outline-brand-dark rounded-xl py-2.5 px-5"
                                placeholder="Название организации"
                                x-model.debounce.250ms="searchQuery"
                            />
                        </label>

                        <div class="relative" @click.outside="territoryOpen = false">
                            <button
                                type="button"
                                class="h-12.5 w-full text_6 flex-between gap-2.5 text-brand-gray-dark bg-white outline-brand-dark rounded-xl py-2.5 px-5"
                                @click="territoryOpen = !territoryOpen"
                                :aria-expanded="territoryOpen"
                            >
                                <span class="line-clamp-1" :class="selectedTerritoryName ? 'text-brand-dark' : ''" x-text="selectedTerritoryName || 'Выберите территорию'"></span>
                                <svg width="17" height="10" class="shrink-0 transition-transform duration-200" :class="territoryOpen && 'rotate-180'" aria-hidden="true">
                                    <use href="#icon-select-chevron" />
                                </svg>
                            </button>

                            <ul
                                x-show="territoryOpen"
                                x-cloak
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 -translate-y-1"
                                class="w-full max-h-96 2xl:max-h-120 absolute top-full left-0 space-y-0.75 text_8 bg-white rounded-xl overflow-y-auto mt-4 z-20 shadow-lg"
                            >
                                <li>
                                    <button
                                        type="button"
                                        class="w-full flex-between text-left hover:bg-brand-blue hover:text-white smooth px-5 py-2.5"
                                        @click="selectTerritory(null)"
                                    >
                                        <span>Любая территория</span>
                                        <svg
                                            x-show="selectedTerritoryId === null"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="shrink-0 size-5"
                                        >
                                            <path d="M9.00016 16.17L4.83016 12L3.41016 13.41L9.00016 19L21.0002 6.99997L19.5902 5.58997L9.00016 16.17Z" fill="currentColor" />
                                        </svg>
                                    </button>
                                </li>
                                <template x-for="item in visibleTerritories" :key="item.id">
                                    <li>
                                        <div
                                            class="w-full flex-between text-left smooth px-5 py-2.5"
                                            :class="isTerritoryDisabled(item.id) ? 'text-brand-gray cursor-not-allowed' : 'hover:bg-brand-blue hover:text-white'"
                                            :style="`padding-left: ${20 + item.depth * 20}px`"
                                        >
                                            <button
                                                type="button"
                                                class="flex-1 text-left"
                                                :disabled="isTerritoryDisabled(item.id)"
                                                @click="selectTerritory(item.id)"
                                            >
                                                <span x-text="item.name"></span>
                                            </button>
                                            <div class="flex-base gap-2 shrink-0">
                                                <button
                                                    type="button"
                                                    class="p-1"
                                                    x-show="item.hasChildren"
                                                    @click.stop="toggleTerritoryExpanded(item.id)"
                                                    :aria-label="expandedTerritoryIds[item.id] ? 'Свернуть' : 'Развернуть'"
                                                >
                                                    <svg width="12" height="12" viewBox="0 0 16 16" fill="none" class="transition-transform duration-200" :class="expandedTerritoryIds[item.id] && 'rotate-180'">
                                                        <path d="M14 4.5L8 10.5L2 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </button>
                                                <svg
                                                    x-show="selectedTerritoryId === item.id"
                                                    width="24"
                                                    height="24"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="shrink-0 size-5"
                                                >
                                                    <path d="M9.00016 16.17L4.83016 12L3.41016 13.41L9.00016 19L21.0002 6.99997L19.5902 5.58997L9.00016 16.17Z" fill="currentColor" />
                                                </svg>
                                            </div>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <div class="relative" @click.outside="resourceOpen = false">
                            <button
                                type="button"
                                class="h-12.5 w-full text_6 flex-between gap-2.5 text-brand-gray-dark bg-white outline-brand-dark rounded-xl py-2.5 px-5"
                                @click="resourceOpen = !resourceOpen"
                            >
                                <span class="line-clamp-1" :class="selectedResourceIds.length ? 'text-brand-dark' : ''" x-text="selectedResourceTitle || 'Выберите ресурс'"></span>
                                <svg width="17" height="10" class="shrink-0 transition-transform duration-200" :class="resourceOpen && 'rotate-180'" aria-hidden="true">
                                    <use href="#icon-select-chevron" />
                                </svg>
                            </button>
                            <ul x-show="resourceOpen" x-cloak class="w-full max-h-60 absolute top-full left-0 text_8 bg-white rounded-xl overflow-y-auto mt-2 z-20 shadow-lg border border-brand-gray-light-2">
                                <li>
                                    <button type="button" class="w-full text-left px-5 py-2.5 smooth hover:bg-brand-blue hover:text-white text-brand-dark" @click="selectedResourceIds = []; resourceOpen = false">Любые ресурсы</button>
                                </li>
                                <template x-for="resource in resourceTypes" :key="resource.id">
                                    <li>
                                        <button
                                            type="button"
                                            class="w-full text-left px-5 py-2.5 smooth"
                                            :class="isResourceDisabled(resource.id) ? 'text-brand-gray cursor-not-allowed' : 'hover:bg-brand-blue hover:text-white text-brand-dark'"
                                            :disabled="isResourceDisabled(resource.id)"
                                            @click="selectedResourceIds = [resource.id]; resourceOpen = false"
                                        >
                                            <span x-text="resource.name"></span>
                                        </button>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <div class="relative" @click.outside="categoryOpen = false">
                            <button
                                type="button"
                                class="h-12.5 w-full text_6 flex-between gap-2.5 text-brand-gray-dark bg-white outline-brand-dark rounded-xl py-2.5 px-5"
                                @click="categoryOpen = !categoryOpen"
                            >
                                <span class="line-clamp-1" :class="selectedCategoryIds.length ? 'text-brand-dark' : ''" x-text="selectedCategoryTitle || 'Выберите категорию'"></span>
                                <svg width="17" height="10" class="shrink-0 transition-transform duration-200" :class="categoryOpen && 'rotate-180'" aria-hidden="true">
                                    <use href="#icon-select-chevron" />
                                </svg>
                            </button>
                            <ul x-show="categoryOpen" x-cloak class="w-full max-h-60 absolute top-full left-0 text_8 bg-white rounded-xl overflow-y-auto mt-2 z-20 shadow-lg border border-brand-gray-light-2">
                                <li>
                                    <button type="button" class="w-full text-left px-5 py-2.5 smooth hover:bg-brand-blue hover:text-white text-brand-dark" @click="selectedCategoryIds = []; categoryOpen = false">Любые категории</button>
                                </li>
                                <template x-for="category in categories" :key="category.id">
                                    <li>
                                        <button
                                            type="button"
                                            class="w-full text-left px-5 py-2.5 smooth"
                                            :class="isCategoryDisabled(category.id) ? 'text-brand-gray cursor-not-allowed' : 'hover:bg-brand-blue hover:text-white text-brand-dark'"
                                            :disabled="isCategoryDisabled(category.id)"
                                            @click="selectedCategoryIds = [category.id]; categoryOpen = false"
                                        >
                                            <span x-text="category.name"></span>
                                        </button>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <div class="relative" @click.outside="workOpen = false">
                            <button
                                type="button"
                                class="h-12.5 w-full text_6 flex-between gap-2.5 text-brand-gray-dark bg-white outline-brand-dark rounded-xl py-2.5 px-5"
                                @click="workOpen = !workOpen"
                            >
                                <span class="line-clamp-1" :class="workType !== 'all' ? 'text-brand-dark' : ''" x-text="workType === 'all' ? 'Выберите работы' : workTypeLabel"></span>
                                <svg width="17" height="10" class="shrink-0 transition-transform duration-200" :class="workOpen && 'rotate-180'" aria-hidden="true">
                                    <use href="#icon-select-chevron" />
                                </svg>
                            </button>
                            <ul x-show="workOpen" x-cloak class="w-full max-h-60 absolute top-full left-0 text_8 bg-white rounded-xl overflow-y-auto mt-2 z-20 shadow-lg border border-brand-gray-light-2">
                                <li><button type="button" class="w-full text-left px-5 py-2.5 smooth hover:bg-brand-blue hover:text-white text-brand-dark" @click="setWorkType('all')">Любые работы</button></li>
                                <li><button type="button" class="w-full text-left px-5 py-2.5 smooth" :class="isWorkTypeDisabled('smr') ? 'text-brand-gray cursor-not-allowed' : 'hover:bg-brand-blue hover:text-white text-brand-dark'" :disabled="isWorkTypeDisabled('smr')" @click="setWorkType('smr')">СМР</button></li>
                                <li><button type="button" class="w-full text-left px-5 py-2.5 smooth" :class="isWorkTypeDisabled('pir') ? 'text-brand-gray cursor-not-allowed' : 'hover:bg-brand-blue hover:text-white text-brand-dark'" :disabled="isWorkTypeDisabled('pir')" @click="setWorkType('pir')">ПИР / ПСД</button></li>
                                <li><button type="button" class="w-full text-left px-5 py-2.5 smooth" :class="isWorkTypeDisabled('both') ? 'text-brand-gray cursor-not-allowed' : 'hover:bg-brand-blue hover:text-white text-brand-dark'" :disabled="isWorkTypeDisabled('both')" @click="setWorkType('both')">СМР и ПИР / ПСД</button></li>
                            </ul>
                        </div>

                        <div class="relative" @click.outside="segmentOpen = false">
                            <button
                                type="button"
                                class="h-12.5 w-full text_6 flex-between gap-2.5 text-brand-gray-dark bg-white outline-brand-dark rounded-xl py-2.5 px-5"
                                @click="segmentOpen = !segmentOpen"
                            >
                                <span class="line-clamp-1" :class="businessSegment !== '' ? 'text-brand-dark' : ''" x-text="businessSegment === '' ? 'Выберите сегмент' : segmentLabel"></span>
                                <svg width="17" height="10" class="shrink-0 transition-transform duration-200" :class="segmentOpen && 'rotate-180'" aria-hidden="true">
                                    <use href="#icon-select-chevron" />
                                </svg>
                            </button>
                            <ul x-show="segmentOpen" x-cloak class="w-full max-h-60 absolute top-full left-0 text_8 bg-white rounded-xl overflow-y-auto mt-2 z-20 shadow-lg border border-brand-gray-light-2">
                                <li><button type="button" class="w-full text-left px-5 py-2.5 smooth hover:bg-brand-blue hover:text-white text-brand-dark" @click="setBusinessSegment('')">Любые сегменты</button></li>
                                <li><button type="button" class="w-full text-left px-5 py-2.5 smooth" :class="isSegmentDisabled('b2b') ? 'text-brand-gray cursor-not-allowed' : 'hover:bg-brand-blue hover:text-white text-brand-dark'" :disabled="isSegmentDisabled('b2b')" @click="setBusinessSegment('b2b')">В2В - для бизнеса</button></li>
                                <li><button type="button" class="w-full text-left px-5 py-2.5 smooth" :class="isSegmentDisabled('b2c') ? 'text-brand-gray cursor-not-allowed' : 'hover:bg-brand-blue hover:text-white text-brand-dark'" :disabled="isSegmentDisabled('b2c')" @click="setBusinessSegment('b2c')">В2С - для клиента</button></li>
                                <li><button type="button" class="w-full text-left px-5 py-2.5 smooth" :class="isSegmentDisabled('both') ? 'text-brand-gray cursor-not-allowed' : 'hover:bg-brand-blue hover:text-white text-brand-dark'" :disabled="isSegmentDisabled('both')" @click="setBusinessSegment('both')">В2В / В2С - для всех</button></li>
                            </ul>
                        </div>

                        <button class="h-12.5 button_6" @click="applySearch()">Начать поиск</button>
                    </div>

                    <div class="grid lg:grid-cols-2 gap-5">
                        <div class="min-h-96 lg:min-h-150 3xl:min-h-250">
                            <div id="geo-map" class="size-full object-cover" style="min-height: 720px;"></div>
                        </div>

                        <div class="bg-white p-5 lg:p-6 3xl:p-10 min-h-96 lg:min-h-150 3xl:min-h-250 flex">
                            <div class="w-full flex flex-col">
                                <div class="flex-1 flex flex-col gap-5">
                                    <template x-if="hasAppliedSearch">
                                        <div class="space-y-4">
                                            <div class="flex-between flex-col items-start xs:items-center xs:flex-row gap-3">
                                                <p class="text_1" x-text="selectedTerritoryName || 'Любая территория'"></p>
                                                <span class="text_8 text-brand-gray"><span x-text="appliedCount"></span> организаций</span>
                                            </div>

                                            <div class="flex-base gap-5">
                                                <div class="relative" @click.outside="filtersOpen = false">
                                                    <button
                                                        type="button"
                                                        class="flex-base gap-2.5 hover:text-brand-gray-dark smooth"
                                                        @click="filtersOpen = !filtersOpen"
                                                        :aria-expanded="filtersOpen"
                                                    >
                                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M10 8H20M4 16H14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                                            <path d="M4 8C4 9.65685 5.34315 11 7 11C8.65685 11 10 9.65685 10 8C10 6.34315 8.65685 5 7 5C5.34315 5 4 6.34315 4 8Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                                            <path d="M14 16C14 17.6569 15.3431 19 17 19C18.6569 19 20 17.6569 20 16C20 14.3431 18.6569 13 17 13C15.3431 13 14 14.3431 14 16Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                                        </svg>
                                                        <span class="text_8">Фильтры</span>
                                                        <span x-show="activeFiltersCount > 0" x-cloak x-text="activeFiltersCount" class="bg-brand-blue text-white text-lg/4.5 rounded-full py-1.5 px-2.25 min-w-[1.5rem] flex-center"></span>
                                                    </button>

                                                    <div
                                                        x-show="filtersOpen"
                                                        x-cloak
                                                        x-transition:enter="transition ease-out duration-200"
                                                        x-transition:enter-start="opacity-0 -translate-y-1"
                                                        x-transition:enter-end="opacity-100 translate-y-0"
                                                        x-transition:leave="transition ease-in duration-150"
                                                        x-transition:leave-start="opacity-100 translate-y-0"
                                                        x-transition:leave-end="opacity-0 -translate-y-1"
                                                        class="absolute left-0 top-full mt-2 z-20 h-120 overflow-y-auto lg:h-auto w-72 2xs:w-80 xs:w-max max-w-125 space-y-8 bg-white border border-brand-gray/50 rounded-brand-base lg:rounded-b-brand-3xl shadow-2xl p-4 2xs:p-5 lg:p-6 3xl:p-10"
                                                    >
                                                        <div>
                                                            <div class="flex-end">
                                                                <button type="button" @click="filtersOpen = false" aria-label="Закрыть">
                                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 18.998L12 11.998M12 11.998L19 4.99805M12 11.998L5 4.99805M12 11.998L19 18.998" stroke="#193760" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                                                </button>
                                                            </div>
                                                            <div class="flex-base gap-5">
                                                                <h4>Фильтры</h4>
                                                                <button type="button" class="text_8 text-brand-gray hover:text-brand-gray-dark smooth disabled:opacity-50 disabled:cursor-not-allowed" :disabled="activeFiltersCount === 0" @click="resetFilters()">сбросить</button>
                                                            </div>
                                                        </div>

                                                        <div class="space-y-5">
                                                            <div class="space-y-3.75">
                                                                <p class="text_1">Вид ресурса</p>
                                                                <ul class="text_9 space-y-4">
                                                                    <template x-for="resource in resourceTypes" :key="`f-resource-${resource.id}`">
                                                                        <li>
                                                                            <label class="flex-between gap-3.25" :class="isResourceDisabled(resource.id) ? 'text-brand-gray-dark cursor-not-allowed' : 'cursor-pointer'">
                                                                                <div class="size-6 flex-center border" :class="isResourceDisabled(resource.id) ? 'border-brand-gray-dark' : 'border-brand-dark'">
                                                                                    <input type="checkbox" class="hidden peer" :disabled="isResourceDisabled(resource.id)" :checked="selectedResourceIds[0] === resource.id" @change="toggleSingleChoice('resource', resource.id)" />
                                                                                    <svg width="17" height="13" viewBox="0 0 17 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="opacity-0 peer-checked:opacity-100 smooth"><path d="M1.5 7.5L5.5 11.5L15.5 1.5" stroke="#399C41" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                                                                </div>
                                                                                <span class="flex-1" x-text="resource.name"></span>
                                                                            </label>
                                                                        </li>
                                                                    </template>
                                                                </ul>
                                                            </div>

                                                            <div class="space-y-3.75">
                                                                <p class="text_1">Категория</p>
                                                                <ul class="text_9 space-y-4">
                                                                    <template x-for="category in categories" :key="`f-category-${category.id}`">
                                                                        <li>
                                                                            <label class="flex-between gap-3.25" :class="isCategoryDisabled(category.id) ? 'text-brand-gray-dark cursor-not-allowed' : 'cursor-pointer'">
                                                                                <div class="size-6 flex-center border" :class="isCategoryDisabled(category.id) ? 'border-brand-gray-dark' : 'border-brand-dark'">
                                                                                    <input type="checkbox" class="hidden peer" :disabled="isCategoryDisabled(category.id)" :checked="selectedCategoryIds[0] === category.id" @change="toggleSingleChoice('category', category.id)" />
                                                                                    <svg width="17" height="13" viewBox="0 0 17 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="opacity-0 peer-checked:opacity-100 smooth"><path d="M1.5 7.5L5.5 11.5L15.5 1.5" stroke="#399C41" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                                                                </div>
                                                                                <span class="flex-1" x-text="category.name"></span>
                                                                            </label>
                                                                        </li>
                                                                    </template>
                                                                </ul>
                                                            </div>

                                                            <div class="space-y-3.75">
                                                                <p class="text_1">Сегмент бизнеса</p>
                                                                <ul class="text_9 space-y-4">
                                                                    <li><label class="flex-base gap-3.25" :class="isSegmentDisabled('b2b') ? 'cursor-not-allowed text-brand-gray-dark' : 'cursor-pointer'"><div class="size-6 flex-center border" :class="isSegmentDisabled('b2b') ? 'border-brand-gray-dark' : 'border-brand-dark'"><input type="checkbox" class="hidden peer" :disabled="isSegmentDisabled('b2b')" :checked="businessSegment === 'b2b'" @change="setBusinessSegment($event.target.checked ? 'b2b' : '')" /><svg width="17" height="13" viewBox="0 0 17 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="opacity-0 peer-checked:opacity-100 smooth"><path d="M1.5 7.5L5.5 11.5L15.5 1.5" stroke="#399C41" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" /></svg></div><span>В2В - для бизнеса</span></label></li>
                                                                    <li><label class="flex-base gap-3.25" :class="isSegmentDisabled('b2c') ? 'cursor-not-allowed text-brand-gray-dark' : 'cursor-pointer'"><div class="size-6 flex-center border" :class="isSegmentDisabled('b2c') ? 'border-brand-gray-dark' : 'border-brand-dark'"><input type="checkbox" class="hidden peer" :disabled="isSegmentDisabled('b2c')" :checked="businessSegment === 'b2c'" @change="setBusinessSegment($event.target.checked ? 'b2c' : '')" /><svg width="17" height="13" viewBox="0 0 17 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="opacity-0 peer-checked:opacity-100 smooth"><path d="M1.5 7.5L5.5 11.5L15.5 1.5" stroke="#399C41" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" /></svg></div><span>В2С - для клиента</span></label></li>
                                                                    <li><label class="flex-base gap-3.25" :class="isSegmentDisabled('both') ? 'cursor-not-allowed text-brand-gray-dark' : 'cursor-pointer'"><div class="size-6 flex-center border" :class="isSegmentDisabled('both') ? 'border-brand-gray-dark' : 'border-brand-dark'"><input type="checkbox" class="hidden peer" :disabled="isSegmentDisabled('both')" :checked="businessSegment === 'both'" @change="setBusinessSegment($event.target.checked ? 'both' : '')" /><svg width="17" height="13" viewBox="0 0 17 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="opacity-0 peer-checked:opacity-100 smooth"><path d="M1.5 7.5L5.5 11.5L15.5 1.5" stroke="#399C41" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" /></svg></div><span>В2В / В2С - для всех</span></label></li>
                                                                </ul>
                                                            </div>

                                                            <div class="space-y-3.75">
                                                                <p class="text_1">Выполняемые работы</p>
                                                                <ul class="text_9 space-y-4">
                                                                    <li><label class="flex-base gap-3.25" :class="isWorkTypeDisabled('smr') ? 'cursor-not-allowed text-brand-gray-dark' : 'cursor-pointer'"><div class="size-6 flex-center border" :class="isWorkTypeDisabled('smr') ? 'border-brand-gray-dark' : 'border-brand-dark'"><input type="checkbox" class="hidden peer" :disabled="isWorkTypeDisabled('smr')" :checked="workType === 'smr'" @change="setWorkType($event.target.checked ? 'smr' : 'all')" /><svg width="17" height="13" viewBox="0 0 17 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="opacity-0 peer-checked:opacity-100 smooth"><path d="M1.5 7.5L5.5 11.5L15.5 1.5" stroke="#399C41" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" /></svg></div><span>СМР</span></label></li>
                                                                    <li><label class="flex-base gap-3.25" :class="isWorkTypeDisabled('pir') ? 'cursor-not-allowed text-brand-gray-dark' : 'cursor-pointer'"><div class="size-6 flex-center border" :class="isWorkTypeDisabled('pir') ? 'border-brand-gray-dark' : 'border-brand-dark'"><input type="checkbox" class="hidden peer" :disabled="isWorkTypeDisabled('pir')" :checked="workType === 'pir'" @change="setWorkType($event.target.checked ? 'pir' : 'all')" /><svg width="17" height="13" viewBox="0 0 17 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="opacity-0 peer-checked:opacity-100 smooth"><path d="M1.5 7.5L5.5 11.5L15.5 1.5" stroke="#399C41" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" /></svg></div><span>ПИР / ПСД</span></label></li>
                                                                    <li><label class="flex-base gap-3.25" :class="isWorkTypeDisabled('both') ? 'cursor-not-allowed text-brand-gray-dark' : 'cursor-pointer'"><div class="size-6 flex-center border" :class="isWorkTypeDisabled('both') ? 'border-brand-gray-dark' : 'border-brand-dark'"><input type="checkbox" class="hidden peer" :disabled="isWorkTypeDisabled('both')" :checked="workType === 'both'" @change="setWorkType($event.target.checked ? 'both' : 'all')" /><svg width="17" height="13" viewBox="0 0 17 13" fill="none" xmlns="http://www.w3.org/2000/svg" class="opacity-0 peer-checked:opacity-100 smooth"><path d="M1.5 7.5L5.5 11.5L15.5 1.5" stroke="#399C41" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" /></svg></div><span>СМР и ПИР / ПСД</span></label></li>
                                                                </ul>
                                                            </div>
                                                        </div>

                                                        <button class="button_poppup_filter" @click="applySearch(); filtersOpen = false" x-text="`Показать ${futureCount} организаций`"></button>
                                                    </div>
                                                </div>

                                                <div class="relative" @click.outside="sortOpen = false">
                                                    <button
                                                        type="button"
                                                        class="flex-base gap-2.5 hover:text-brand-gray-dark smooth xs:px-5"
                                                        @click="sortOpen = !sortOpen"
                                                        :aria-expanded="sortOpen"
                                                    >
                                                        <span class="text_8" x-text="sortLabel"></span>
                                                        <svg width="16" height="8" viewBox="0 0 16 8" fill="none" xmlns="http://www.w3.org/2000/svg" class="shrink-0 transition-transform duration-200" :class="sortOpen && 'rotate-180'">
                                                            <path d="M14.3151 1.55957L7.65753 6.99957L1 1.55957" stroke="currentColor" stroke-width="2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                    </button>
                                                    <ul
                                                        x-show="sortOpen"
                                                        x-cloak
                                                        class="absolute top-full right-0 mt-2 min-w-52 text_8 bg-white rounded-xl shadow-lg overflow-hidden z-20"
                                                    >
                                                        <li><button type="button" class="w-full text-left px-5 py-2.5 text-brand-dark hover:bg-brand-blue hover:text-white smooth" @click="setSort('name_asc')">По названию</button></li>
                                                        <li><button type="button" class="w-full text-left px-5 py-2.5 text-brand-dark hover:bg-brand-blue hover:text-white smooth" @click="setSort('rating_best')">По рейтингу</button></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="!hasAppliedSearch">
                                        <div class="flex-1 flex flex-col items-center justify-center text-center gap-5">
                                            <div class="shrink-0" aria-hidden="true">
                                                {!! $searchPlaceholderIcon !!}
                                            </div>
                                            <p style="color: #C7CED7; font-size: 22px; line-height: 30px;">
                                                Наведите на карту<br>или начните поиск,<br>информация<br>появится
                                            </p>
                                        </div>
                                    </template>

                                    <template x-if="hasAppliedSearch">
                                        <div class="space-y-5">
                                            <ul class="text_8 divide-y divide-brand-gray">
                                                <template x-if="paginatedContractors.length === 0">
                                                    <li class="py-6 text-brand-gray-dark">По заданным фильтрам подрядчики не найдены.</li>
                                                </template>
                                                <template x-for="contractor in paginatedContractors" :key="contractor.id">
                                                    <li class="py-4 3xl:py-6">
                                                        <a :href="`/agents/${contractor.slug}`" target="_blank" rel="noopener noreferrer" class="flex-between items-start gap-3 hover:text-brand-blue smooth">
                                                            <span x-text="contractor.short_name"></span>
                                                            <span x-text="contractor.rating_name || '—'"></span>
                                                        </a>
                                                    </li>
                                                </template>
                                            </ul>

                                            <template x-if="totalPages > 1">
                                                <div class="flex-end gap-2.5 text_8 -mt-5">
                                                    <template x-for="p in pageButtons" :key="`p-${p}`">
                                                        <button class="hover:text-brand-gray smooth" :class="page === p ? 'text-brand-gray' : ''" @click="setPage(p)" x-text="p"></button>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>

                                <template x-if="selectedTerritoryId !== null">
                                    <button
                                        type="button"
                                        class="self-start text_8 text-brand-blue hover:underline underline-offset-1"
                                        @click="territorySchemesModalOpen = true"
                                    >
                                        Скачать схемы по видам ресурсов
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div
        x-show="territorySchemesModalOpen"
        x-cloak
        class="fixed inset-0 z-50 bg-black/50 flex-center px-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="territory-schemes-modal-title"
        @keydown.escape.window="territorySchemesModalOpen = false"
        @click="territorySchemesModalOpen = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div
            class="relative max-h-[90vh] overflow-y-auto w-full bg-white rounded-brand-base pt-12 pb-5 xs:pb-6 md:pb-8 px-5 xs:px-6 md:px-8"
            style="max-width: 640px;"
            @click.stop
            x-show="territorySchemesModalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            <button
                type="button"
                class="absolute top-5 right-5 xs:right-6 md:top-6 md:right-6 p-1 -mr-1 rounded-lg hover:bg-brand-gray-light smooth text-brand-dark"
                aria-label="Закрыть"
                @click="territorySchemesModalOpen = false"
            >
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.99951 18.998L11.9995 11.998M11.9995 11.998L18.9995 4.99805M11.9995 11.998L4.99951 4.99805M11.9995 11.998L18.9995 18.998" stroke="#193760" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </button>

            <div class="flex-center mb-4">
                <h4 id="territory-schemes-modal-title" class="text-center">
                    <span>Схемы по видам ресурсов:</span><br>
                    <span x-text="selectedTerritoryName || 'Выбранный регион'"></span>
                </h4>
            </div>

            <template x-if="selectedTerritorySchemes.length > 0">
                <ul class="divide-y divide-brand-gray-light-2 border border-brand-gray-light-2 rounded-brand-base overflow-hidden">
                    <template x-for="scheme in selectedTerritorySchemes" :key="scheme.url">
                        <li class="px-4 py-3 xs:px-5 xs:py-4">
                            <a
                                :href="scheme.url"
                                :download="scheme.title"
                                class="text_2 text-brand-blue hover:underline underline-offset-1"
                                x-text="scheme.title"
                            ></a>
                        </li>
                    </template>
                </ul>
            </template>

            <template x-if="selectedTerritorySchemes.length === 0">
                <p class="text_2 text-brand-gray-dark text-center">
                    Для выбранного региона схемы по видам ресурсов пока не загружены.
                </p>
            </template>
        </div>
    </div>

    @include('shared.partials.footer', ['settings' => $settings, 'footerBorder' => true])
    @include('shared.partials.request-modal', ['settings' => $settings])
    @include('shared.partials.auth-modal', ['settings' => $settings])
@endsection

@push('scripts')
    <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU{{ $yandexMapsApiKey ? '&apikey=' . urlencode($yandexMapsApiKey) : '' }}" type="text/javascript"></script>
    <script>
        window.searchPagePayload = {
            contractors: @js($contractors),
            categories: @js($categories),
            resourceTypes: @js($resourceTypes),
            territoryTree: @js($territoryTree),
            territoryDescendants: @js($territoryDescendants),
        };

        document.addEventListener('alpine:init', () => {
            Alpine.store('scroll', { y: 0 });
            const setScrollY = () => (Alpine.store('scroll').y = window.scrollY ?? window.pageYOffset ?? 0);
            setScrollY();
            window.addEventListener('scroll', setScrollY, { passive: true });

            Alpine.data('searchPage', () => ({
                mobileMenuOpen: false,
                requestModalOpen: false,
                authModalOpen: false,
                authModalMode: 'login',
                territorySchemesModalOpen: false,
                territoryOpen: false,
                categoryOpen: false,
                resourceOpen: false,
                workOpen: false,
                segmentOpen: false,
                filtersOpen: false,
                sortOpen: false,
                expandedTerritoryIds: {},
                searchQuery: '',
                selectedTerritoryId: null,
                selectedCategoryIds: [],
                selectedResourceIds: [],
                workType: 'all',
                businessSegment: '',
                sortBy: 'rating_best',
                appliedSearchQuery: '',
                appliedSelectedTerritoryId: null,
                appliedSelectedCategoryIds: [],
                appliedSelectedResourceIds: [],
                appliedWorkType: 'all',
                appliedBusinessSegment: '',
                appliedSortBy: 'rating_best',
                page: 1,
                perPage: 10,
                contractors: window.searchPagePayload.contractors ?? [],
                categories: window.searchPagePayload.categories ?? [],
                resourceTypes: window.searchPagePayload.resourceTypes ?? [],
                territoryTree: window.searchPagePayload.territoryTree ?? [],
                territoryDescendants: window.searchPagePayload.territoryDescendants ?? {},
                mapInstance: null,

                init() {
                    for (const item of this.territoryTree) {
                        if (item && item.id) {
                            this.expandedTerritoryIds[item.id] = true;
                        }
                    }
                    this.onMapTerritorySelected = (event) => {
                        const territoryId = Number(event?.detail?.territoryId);
                        if (!Number.isInteger(territoryId) || territoryId <= 0) return;
                        if (!this.findTerritoryById(territoryId)) return;
                        this.selectedTerritoryId = territoryId;
                        this.applySearch();
                    };
                    window.addEventListener('geo-map-territory-selected', this.onMapTerritorySelected);
                    this.initFromQueryParams();
                },
                destroy() {
                    if (this.onMapTerritorySelected) {
                        window.removeEventListener('geo-map-territory-selected', this.onMapTerritorySelected);
                    }
                },

                normalizeText(value) {
                    return String(value ?? '').trim().toLocaleLowerCase('ru-RU');
                },

                hasIntersection(source, values) {
                    if (!values.length) return true;
                    return source.some((id) => values.includes(id));
                },
                parseIdsParam(value) {
                    if (!value) return [];
                    return String(value)
                        .split(',')
                        .map((v) => Number(v.trim()))
                        .filter((v) => Number.isInteger(v) && v > 0);
                },
                buildQueryParams(pageOverride = null) {
                    const params = new URLSearchParams();
                    if (this.appliedSearchQuery.trim() !== '') params.set('q', this.appliedSearchQuery.trim());
                    if (this.appliedSelectedTerritoryId !== null) params.set('t', String(this.appliedSelectedTerritoryId));
                    if (this.appliedSelectedCategoryIds.length > 0) params.set('cat', this.appliedSelectedCategoryIds.join(','));
                    if (this.appliedSelectedResourceIds.length > 0) params.set('res', this.appliedSelectedResourceIds.join(','));
                    if (this.appliedWorkType !== 'all') params.set('work', this.appliedWorkType);
                    if (this.appliedBusinessSegment !== '') params.set('seg', this.appliedBusinessSegment);
                    if (this.appliedSortBy !== 'rating_best') params.set('sort', this.appliedSortBy);

                    const pageValue = pageOverride !== null ? Number(pageOverride) : this.page;
                    if (Number.isInteger(pageValue) && pageValue > 1) params.set('page', String(pageValue));
                    return params;
                },
                syncQueryParams(pageOverride = null) {
                    const params = this.buildQueryParams(pageOverride);
                    const query = params.toString();
                    const nextUrl = query ? `${window.location.pathname}?${query}` : window.location.pathname;
                    window.history.replaceState({}, '', nextUrl);
                },
                initFromQueryParams() {
                    const params = new URLSearchParams(window.location.search);
                    const q = params.get('q') ?? '';
                    const territory = params.get('t');
                    const cats = this.parseIdsParam(params.get('cat'));
                    const resources = this.parseIdsParam(params.get('res'));
                    const work = ['all', 'both', 'smr', 'pir'].includes(params.get('work') ?? '') ? params.get('work') : 'all';
                    const rawSeg = params.get('seg') ?? '';
                    const seg = ['b2b', 'b2c', 'both'].includes(rawSeg) ? rawSeg : '';
                    const sort = ['name_asc', 'rating_best'].includes(params.get('sort') ?? '') ? params.get('sort') : 'rating_best';
                    const page = Math.max(1, Number(params.get('page') || 1));

                    this.searchQuery = q;
                    this.selectedTerritoryId = territory ? Number(territory) : null;
                    this.selectedCategoryIds = cats;
                    this.selectedResourceIds = resources;
                    this.workType = work;
                    this.businessSegment = seg;
                    this.sortBy = sort;

                    this.appliedSearchQuery = this.searchQuery;
                    this.appliedSelectedTerritoryId = this.selectedTerritoryId;
                    this.appliedSelectedCategoryIds = [...this.selectedCategoryIds];
                    this.appliedSelectedResourceIds = [...this.selectedResourceIds];
                    this.appliedWorkType = this.workType;
                    this.appliedBusinessSegment = this.businessSegment;
                    this.appliedSortBy = this.sortBy;
                    this.page = page;
                },
                searchScopedContractors() {
                    return this.filterContractors({
                        searchQuery: this.searchQuery,
                        selectedTerritoryId: null,
                        selectedCategoryIds: [],
                        selectedResourceIds: [],
                        workType: 'all',
                        businessSegment: '',
                    });
                },

                filterContractors(overrides = {}) {
                    const state = {
                        searchQuery: this.searchQuery,
                        selectedTerritoryId: this.selectedTerritoryId,
                        selectedCategoryIds: this.selectedCategoryIds,
                        selectedResourceIds: this.selectedResourceIds,
                        workType: this.workType,
                        businessSegment: this.businessSegment,
                        ...overrides,
                    };

                    const query = this.normalizeText(state.searchQuery);
                    const categoryIds = (state.selectedCategoryIds ?? []).map((v) => Number(v));
                    const resourceIds = (state.selectedResourceIds ?? []).map((v) => Number(v));
                    const territoryId = state.selectedTerritoryId !== null ? Number(state.selectedTerritoryId) : null;
                    const territoryScope = territoryId === null
                        ? []
                        : [territoryId, ...((this.territoryDescendants[territoryId] ?? []).map((id) => Number(id)))];

                    return this.contractors.filter((contractor) => {
                        if (query !== '' && !this.normalizeText(contractor.short_name).includes(query)) return false;

                        const contractorTerritories = (contractor.territory_ids ?? []).map((v) => Number(v));
                        if (territoryScope.length > 0 && !this.hasIntersection(contractorTerritories, territoryScope)) return false;

                        const contractorCategories = (contractor.category_ids ?? []).map((v) => Number(v));
                        if (!this.hasIntersection(contractorCategories, categoryIds)) return false;

                        const smrIds = (contractor.smr_resource_ids ?? []).map((v) => Number(v));
                        const pirIds = (contractor.pir_resource_ids ?? []).map((v) => Number(v));
                        const allResourceIds = [...new Set([...smrIds, ...pirIds])];

                        if (state.workType === 'both') {
                            if (resourceIds.length === 0) {
                                if (smrIds.length === 0 || pirIds.length === 0) return false;
                            } else {
                                if (!this.hasIntersection(smrIds, resourceIds) || !this.hasIntersection(pirIds, resourceIds)) return false;
                            }
                        } else if (state.workType === 'smr') {
                            if (resourceIds.length === 0 && smrIds.length === 0) return false;
                            if (resourceIds.length > 0 && !this.hasIntersection(smrIds, resourceIds)) return false;
                        } else if (state.workType === 'pir') {
                            if (resourceIds.length === 0 && pirIds.length === 0) return false;
                            if (resourceIds.length > 0 && !this.hasIntersection(pirIds, resourceIds)) return false;
                        } else if (resourceIds.length > 0 && !this.hasIntersection(allResourceIds, resourceIds)) {
                            return false;
                        }

                        const segments = (contractor.business_segments ?? []).map((segment) => String(segment));
                        if (state.businessSegment === 'b2b' && !segments.includes('b2b')) return false;
                        if (state.businessSegment === 'b2c' && !segments.includes('b2c')) return false;
                        if (state.businessSegment === 'both' && (!segments.includes('b2b') || !segments.includes('b2c'))) return false;

                        return true;
                    });
                },

                sortContractors(items) {
                    const list = [...items];
                    const sortBy = this.appliedSortBy;
                    if (sortBy === 'rating_best') return list.sort((a, b) => Number(a.rating_sort_order ?? 9999) - Number(b.rating_sort_order ?? 9999));
                    return list.sort((a, b) => String(a.short_name ?? '').localeCompare(String(b.short_name ?? ''), 'ru'));
                },

                get filteredContractors() {
                    return this.sortContractors(this.filterContractors({
                        searchQuery: this.appliedSearchQuery,
                        selectedTerritoryId: this.appliedSelectedTerritoryId,
                        selectedCategoryIds: this.appliedSelectedCategoryIds,
                        selectedResourceIds: this.appliedSelectedResourceIds,
                        workType: this.appliedWorkType,
                        businessSegment: this.appliedBusinessSegment,
                    }));
                },
                get appliedCount() {
                    return this.filteredContractors.length;
                },
                get hasAppliedSearch() {
                    return this.appliedSearchQuery.trim() !== ''
                        || this.appliedSelectedTerritoryId !== null
                        || this.appliedSelectedCategoryIds.length > 0
                        || this.appliedSelectedResourceIds.length > 0
                        || this.appliedWorkType !== 'all'
                        || this.appliedBusinessSegment !== '';
                },
                get totalPages() {
                    return Math.max(1, Math.ceil(this.appliedCount / this.perPage));
                },
                get paginatedContractors() {
                    const page = Math.min(this.page, this.totalPages);
                    return this.filteredContractors.slice((page - 1) * this.perPage, page * this.perPage);
                },
                get futureCount() {
                    return this.filterContractors().length;
                },
                get pageButtons() {
                    const total = this.totalPages;
                    const start = Math.max(1, this.page - 4);
                    const end = Math.min(total, start + 9);
                    const adjustedStart = Math.max(1, end - 9);
                    return Array.from({ length: (end - adjustedStart) + 1 }, (_, i) => adjustedStart + i);
                },
                setPage(value) {
                    this.page = Math.max(1, Math.min(this.totalPages, Number(value) || 1));
                    this.syncQueryParams();
                },

                get activeFiltersCount() {
                    return this.selectedCategoryIds.length
                        + this.selectedResourceIds.length
                        + (this.workType !== 'all' ? 1 : 0)
                        + (this.businessSegment !== '' ? 1 : 0);
                },

                get selectedCategoryTitle() {
                    if (this.selectedCategoryIds.length === 0) return '';
                    if (this.selectedCategoryIds.length > 1) return `Выбрано: ${this.selectedCategoryIds.length}`;
                    const id = Number(this.selectedCategoryIds[0]);
                    return this.categories.find((item) => item.id === id)?.name ?? '';
                },
                get selectedResourceTitle() {
                    if (this.selectedResourceIds.length === 0) return '';
                    if (this.selectedResourceIds.length > 1) return `Выбрано: ${this.selectedResourceIds.length}`;
                    const id = Number(this.selectedResourceIds[0]);
                    return this.resourceTypes.find((item) => item.id === id)?.name ?? '';
                },
                get workTypeLabel() {
                    if (this.workType === 'both') return 'СМР и ПИР / ПСД';
                    if (this.workType === 'smr') return 'СМР';
                    if (this.workType === 'pir') return 'ПИР / ПСД';
                    return 'Любые работы';
                },
                get segmentLabel() {
                    if (this.businessSegment === 'b2b') return 'В2В - для бизнеса';
                    if (this.businessSegment === 'b2c') return 'В2С - для клиента';
                    if (this.businessSegment === 'both') return 'В2В / В2С - для всех';
                    return 'Любые сегменты';
                },
                get sortLabel() {
                    if (this.sortBy === 'rating_best') return 'По рейтингу';
                    return 'По названию';
                },

                resetFilters() {
                    this.searchQuery = '';
                    this.selectedTerritoryId = null;
                    this.selectedCategoryIds = [];
                    this.selectedResourceIds = [];
                    this.workType = 'all';
                    this.businessSegment = '';
                    this.sortBy = 'rating_best';
                    this.applySearch();
                },
                applySearch() {
                    this.appliedSearchQuery = this.searchQuery;
                    this.appliedSelectedTerritoryId = this.selectedTerritoryId;
                    this.appliedSelectedCategoryIds = [...this.selectedCategoryIds];
                    this.appliedSelectedResourceIds = [...this.selectedResourceIds];
                    this.appliedWorkType = this.workType;
                    this.appliedBusinessSegment = this.businessSegment;
                    this.appliedSortBy = this.sortBy;
                    this.page = 1;
                    this.syncQueryParams();
                },
                setWorkType(value) {
                    this.workType = value;
                    this.workOpen = false;
                },
                setSort(value) {
                    this.sortBy = value;
                    this.appliedSortBy = value;
                    this.sortOpen = false;
                    this.syncQueryParams();
                },
                setBusinessSegment(value) {
                    this.businessSegment = value;
                    this.segmentOpen = false;
                },
                toggleSingleChoice(type, id) {
                    const numericId = Number(id);
                    if (type === 'resource') {
                        this.selectedResourceIds = this.selectedResourceIds[0] === numericId ? [] : [numericId];
                        return;
                    }
                    if (type === 'category') {
                        this.selectedCategoryIds = this.selectedCategoryIds[0] === numericId ? [] : [numericId];
                    }
                },
                selectTerritory(id) {
                    this.selectedTerritoryId = id === null ? null : Number(id);
                    this.territoryOpen = false;
                },
                toggleTerritoryExpanded(id) {
                    this.expandedTerritoryIds[id] = !this.expandedTerritoryIds[id];
                },
                isCategoryDisabled(id) {
                    return false;
                },
                isResourceDisabled(id) {
                    return false;
                },
                isWorkTypeDisabled(value) {
                    return false;
                },
                isSegmentDisabled(value) {
                    return false;
                },
                isTerritoryDisabled(id) {
                    return false;
                },
                get selectedTerritoryName() {
                    if (this.selectedTerritoryId === null) return '';
                    return this.findTerritoryById(this.selectedTerritoryId)?.name ?? '';
                },
                get selectedTerritorySchemes() {
                    if (this.selectedTerritoryId === null) return [];
                    return this.findTerritoryById(this.selectedTerritoryId)?.resource_schemes ?? [];
                },
                findTerritoryById(id) {
                    const numericId = Number(id);
                    if (!Number.isInteger(numericId) || numericId <= 0) return null;
                    const walk = (items) => {
                        for (const item of items) {
                            if (Number(item.id) === numericId) return item;
                            if (item.children && item.children.length) {
                                const found = walk(item.children);
                                if (found) return found;
                            }
                        }
                        return null;
                    };
                    return walk(this.territoryTree);
                },
                get visibleTerritories() {
                    const result = [];
                    const walk = (items, depth) => {
                        for (const item of items) {
                            result.push({
                                id: Number(item.id),
                                name: item.name,
                                depth,
                                hasChildren: Array.isArray(item.children) && item.children.length > 0,
                            });
                            if (Array.isArray(item.children) && item.children.length > 0 && this.expandedTerritoryIds[item.id]) {
                                walk(item.children, depth + 1);
                            }
                        }
                    };
                    walk(this.territoryTree, 0);
                    return result;
                },

                initMap() {},
            }));
        });
    </script>
    <script>
        (function initSearchGeoMap() {
            const containerId = 'geo-map';
            const container = document.getElementById(containerId);
            if (!container || typeof ymaps === 'undefined') {
                return;
            }

            ymaps.ready(async function () {
                const map = new ymaps.Map(containerId, {
                    center: [61.5240, 105.3188],
                    zoom: 3,
                    controls: ['zoomControl', 'typeSelector', 'fullscreenControl']
                });

                map.container.fitToViewport();
                window.addEventListener('resize', () => map.container.fitToViewport(), { passive: true });

                try {
                    const response = await fetch('/api/geo/map-features');
                    const payload = await response.json();
                    const items = payload?.data ?? [];

                    if (!items.length) {
                        return;
                    }

                    const collection = new ymaps.GeoObjectCollection();
                    const bounds = [];

                    for (const item of items) {
                        const geometry = item.geometry;
                        if (!geometry || !geometry.type || !geometry.coordinates) {
                            continue;
                        }

                        const objectOptions = {
                            fillColor: '#1450a3',
                            fillOpacity: 0.2,
                            strokeColor: '#1450a3',
                            strokeWidth: 2,
                            interactivityModel: 'default#geoObject',
                            cursor: 'pointer',
                        };

                        if (geometry.type === 'Polygon') {
                            const obj = new ymaps.Polygon(
                                geometry.coordinates,
                                { hintContent: item.name },
                                objectOptions
                            );
                            obj.events.add('mouseenter', () => obj.options.set('fillOpacity', 0.4));
                            obj.events.add('mouseleave', () => obj.options.set('fillOpacity', 0.2));
                            obj.events.add('click', () => {
                                window.dispatchEvent(new CustomEvent('geo-map-territory-selected', {
                                    detail: { territoryId: Number(item.id) },
                                }));
                            });
                            collection.add(obj);
                        } else if (geometry.type === 'MultiPolygon') {
                            for (const polygonCoordinates of geometry.coordinates) {
                                const obj = new ymaps.Polygon(
                                    polygonCoordinates,
                                    { hintContent: item.name },
                                    objectOptions
                                );
                                obj.events.add('mouseenter', () => obj.options.set('fillOpacity', 0.4));
                                obj.events.add('mouseleave', () => obj.options.set('fillOpacity', 0.2));
                                obj.events.add('click', () => {
                                    window.dispatchEvent(new CustomEvent('geo-map-territory-selected', {
                                        detail: { territoryId: Number(item.id) },
                                    }));
                                });
                                collection.add(obj);
                            }
                        } else {
                            continue;
                        }

                        if (item.bbox && item.bbox.min_lat !== null) {
                            bounds.push([
                                [Number(item.bbox.min_lat), Number(item.bbox.min_lon)],
                                [Number(item.bbox.max_lat), Number(item.bbox.max_lon)],
                            ]);
                        }
                    }

                    map.geoObjects.add(collection);

                    if (bounds.length) {
                        const merged = [
                            [Math.min(...bounds.map(b => b[0][0])), Math.min(...bounds.map(b => b[0][1]))],
                            [Math.max(...bounds.map(b => b[1][0])), Math.max(...bounds.map(b => b[1][1]))],
                        ];
                        map.setBounds(merged, { checkZoomRange: true, zoomMargin: 20 });
                    } else {
                        map.container.fitToViewport();
                    }
                } catch (error) {
                    console.error('Failed to load map features', error);
                }
            });
        })();
    </script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush
