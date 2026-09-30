@extends('layouts.app')

@section('title', $pageTitle)
@section('meta-description', $pageDescription)
@section('body-attrs')
x-data="{ mobileMenuOpen: false, requestModalOpen: false, ratingInfoModalOpen: false, contractorReviewModalOpen: false, authModalOpen: false, authModalMode: 'login' }" x-effect="window.setBodyScrollLock(mobileMenuOpen || requestModalOpen || ratingInfoModalOpen || contractorReviewModalOpen || authModalOpen || $store.reviewModalOpen)"
@endsection

@section('content')
    <svg class="absolute w-0 h-0 overflow-hidden" aria-hidden="true">
        <symbol id="icon-select-chevron" viewBox="0 0 17 10">
            <path d="M15.7945 1L8.39726 9L1 1" stroke="#8695AA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none" />
        </symbol>
    </svg>

    @include('shared.partials.header', ['settings' => $settings])

    <main class="bg-brand-gray-light-2 pt-10 pb-20 sm:pt-12 lg:pb-30">
        <section>
            <div class="container-base space-y-8 lg:space-y-10">
                <div class="space-y-5 sm:space-y-6">
                    <nav aria-label="Хлебные крошки" class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm/5 text-brand-gray-dark">
                        <a href="/" class="hover:text-brand-blue">Главная</a>
                        <span aria-hidden="true">/</span>
                        @foreach ($breadcrumbs as $breadcrumb)
                            @if ($breadcrumb['url'])
                                <a href="{{ $breadcrumb['url'] }}" class="hover:text-brand-blue">{{ $breadcrumb['label'] }}</a>
                            @else
                                <span aria-current="page" class="text-brand-dark">{{ $breadcrumb['label'] }}</span>
                            @endif
                            @if (! $loop->last)
                                <span aria-hidden="true">/</span>
                            @endif
                        @endforeach
                    </nav>

                    <div class="max-w-4xl space-y-3">
                        <h1 class="text-3xl/9 sm:text-4xl/11 xl:text-[38px]/11.5">{{ $pageHeading }}</h1>
                        <p class="text-base/6 text-brand-gray-dark sm:text-lg/7">
                            {{ filled($introText) ? $introText : ($isCatalog ? 'Выберите тип организации или просмотрите весь каталог.' : 'Выберите инженерное направление и найдите работающие в нём организации.') }}
                        </p>
                    </div>
                </div>

                @if ($topicCards->isNotEmpty())
                    <nav class="space-y-6" aria-label="{{ $isCatalog ? 'Категории организаций' : 'Инженерные направления' }}">
                        @foreach ($topicCards as $level)
                            <div class="space-y-3">
                                @if ($level['label'])
                                    <h2 class="text-sm/5 font-semibold text-brand-gray-dark">{{ $level['label'] }}</h2>
                                @endif
                                <div class="grid gap-3 sm:grid-cols-2 sm:gap-4 xl:grid-cols-3">
                                    @foreach ($level['cards'] as $card)
                                        @php
                                            $topic = $card['topic'];
                                            $topicUrl = $isCatalog ? route('catalog.category', ['slug' => $topic->slug]) : route('directions.show', ['slug' => $topic->slug]);
                                            $isSelectedTopic = $card['is_selected'];
                                            $isAncestorTopic = $card['is_ancestor'];
                                        @endphp
                                        <a href="{{ $topicUrl }}" @class([
                                            'group flex min-h-28 items-center gap-4 rounded-2xl border p-5 smooth sm:p-6',
                                            'border-brand-blue bg-brand-gray-light text-brand-dark shadow-sm' => $isSelectedTopic,
                                            'border-brand-blue/40 bg-white text-brand-dark hover:bg-brand-gray-light' => $isAncestorTopic && ! $isSelectedTopic,
                                            'border-brand-gray bg-white text-brand-dark hover:border-brand-blue hover:bg-brand-gray-light hover:shadow-md' => ! $isAncestorTopic && ! $isSelectedTopic,
                                        ]) @if ($isSelectedTopic) aria-current="page" @endif>
                                            @if (! $isCatalog && $topic->resolveIconUrl())
                                                <span class="flex size-12 shrink-0 items-center justify-center rounded-xl {{ $isSelectedTopic || $isAncestorTopic ? 'bg-brand-blue/10' : 'bg-brand-gray-light' }}">
                                                    <img src="{{ $topic->resolveIconUrl() }}" alt="" class="size-7 object-contain" loading="lazy">
                                                </span>
                                            @elseif (! $isCatalog)
                                                <span class="flex size-12 shrink-0 items-center justify-center rounded-xl {{ $isSelectedTopic || $isAncestorTopic ? 'bg-brand-blue/10 text-brand-blue' : 'bg-brand-gray-light text-brand-blue' }}" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16M12 4v16" /></svg>
                                                </span>
                                            @endif
                                            <span class="min-w-0 flex-1">
                                                <strong class="block text-lg/6 font-semibold">{{ $topic->name }}</strong>
                                                <span class="mt-1 block text-sm/5 text-brand-gray-dark">{{ $card['count'] }} {{ $card['count_label'] }}</span>
                                            </span>
                                            <svg viewBox="0 0 24 24" class="size-5 shrink-0 {{ $isSelectedTopic || $isAncestorTopic ? 'text-brand-blue' : 'text-brand-gray-dark group-hover:text-brand-blue' }}" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" /></svg>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </nav>
                @endif

                <div class="grid items-start gap-5 lg:grid-cols-[minmax(15rem,0.32fr)_minmax(0,1fr)] lg:gap-7">
                    <aside class="rounded-2xl bg-white p-5 shadow-sm sm:p-6" aria-label="Фильтры каталога">
                        <form method="get" action="{{ $directoryUrl }}" class="space-y-5" data-directory-search-form>
                            <div class="space-y-1">
                                <h2 class="text-xl/7 font-semibold">{{ $isCatalog ? 'Уточнить каталог' : 'Уточнить направление' }}</h2>
                                <p class="text-sm/5 text-brand-gray-dark">Сузьте список по территории и специализации.</p>
                            </div>

                            @include('directory.partials.territory-filter', [
                                'territoryOptions' => $territoryOptions,
                                'selectedTerritoryPath' => $selectedTerritoryPath,
                            ])

                            @if ($isCatalog)
                                @if ($category === null)
                                    @include('directory.partials.filter-dropdown', [
                                        'name' => 'organization_type',
                                        'ariaLabel' => 'Тип организации',
                                        'placeholder' => 'Выберите тип',
                                        'selectedValue' => $selectedCategoryId,
                                        'choices' => $categoryDropdownOptions,
                                        'menuStyle' => 'filter',
                                        'autoSubmit' => false,
                                    ])
                                @endif
                                @include('directory.partials.filter-dropdown', [
                                    'name' => 'direction_id',
                                    'ariaLabel' => 'Направление',
                                    'placeholder' => 'Выберите направление',
                                    'selectedValue' => $selectedDirectionId,
                                    'choices' => $directionDropdownOptions,
                                    'menuStyle' => 'filter',
                                    'autoSubmit' => false,
                                ])
                            @else
                                @include('directory.partials.filter-dropdown', [
                                    'name' => 'organization_type',
                                    'ariaLabel' => 'Тип организации',
                                    'placeholder' => 'Выберите тип',
                                    'selectedValue' => $selectedCategoryId,
                                    'choices' => $categoryDropdownOptions,
                                    'menuStyle' => 'filter',
                                    'autoSubmit' => false,
                                ])
                                @if ($resourceType === null)
                                    @include('directory.partials.filter-dropdown', [
                                        'name' => 'direction_id',
                                        'ariaLabel' => 'Направление',
                                        'placeholder' => 'Выберите направление',
                                        'selectedValue' => $selectedDirectionId,
                                        'choices' => $directionDropdownOptions,
                                        'menuStyle' => 'filter',
                                        'autoSubmit' => false,
                                    ])
                                @endif
                            @endif

                            @include('directory.partials.filter-dropdown', [
                                'name' => 'rating_id',
                                'ariaLabel' => 'Рейтинг',
                                'placeholder' => 'Любой рейтинг',
                                'selectedValue' => $selectedRatingId,
                                'choices' => $ratingDropdownOptions,
                                'menuStyle' => 'filter',
                                'autoSubmit' => false,
                            ])

                            @include('directory.partials.filter-dropdown', [
                                'name' => 'sort',
                                'ariaLabel' => 'Сортировка',
                                'placeholder' => 'Сортировка',
                                'selectedValue' => $sortOrder,
                                'choices' => $sortDropdownOptions,
                                'menuStyle' => 'filter',
                                'autoSubmit' => false,
                            ])

                            <div class="space-y-3">
                                <button type="submit" class="button_1 w-full">Показать организации</button>
                                @if (request()->query() !== [])
                                    <a href="{{ $directoryUrl }}" class="block text-center text-sm/5 text-brand-blue hover:underline">Сбросить фильтры</a>
                                @endif
                            </div>
                        </form>
                    </aside>

                    <div class="min-w-0 space-y-4" data-directory-results aria-live="polite">
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-white px-5 py-4 shadow-sm sm:px-6">
                            <h2 class="text-xl/7 font-semibold sm:text-2xl/8">
                                @if ($isCatalog)
                                    {{ $category?->name ?? $selectedCategory?->name ?? 'Все организации' }}
                                @else
                                    @if ($resourceType)
                                        {{ 'Организации: '.$resourceType->name }}
                                    @elseif ($selectedDirection)
                                        {{ 'Организации: '.$selectedDirection->name }}
                                    @else
                                        Все направления
                                    @endif
                                @endif
                            </h2>
                            <span class="text-sm/5 text-brand-gray-dark">{{ $organizations->total() }} найдено</span>
                        </div>

                        @forelse ($organizations as $organization)
                            @php($organizationResources = $organization->smrResourceTypes->merge($organization->pirResourceTypes)->unique('id'))
                            <article class="rounded-2xl bg-white p-5 shadow-sm sm:p-6">
                                <div class="flex flex-wrap items-start justify-between gap-4">
                                    <div class="min-w-0 space-y-2">
                                        <h3 class="text-xl/7 font-semibold">
                                            <a href="{{ route('organizations.show', ['slug' => $organization->slug]) }}" class="hover:text-brand-blue">{{ $organization->short_name }}</a>
                                        </h3>
                                        @if ($organizationResources->isNotEmpty())
                                            <p class="text-sm/5 text-brand-gray-dark">{{ $organizationResources->pluck('name')->implode(' · ') }}</p>
                                        @endif
                                    </div>
                                    @if ($organization->rating)
                                        <span class="shrink-0 rounded-lg bg-brand-gray-light px-3 py-1.5 text-sm/5 font-semibold text-brand-blue">{{ $organization->rating->name }}</span>
                                    @endif
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach ($organization->categories as $organizationCategory)
                                        <span class="rounded-full bg-brand-gray-light px-3 py-1 text-xs/4.5 text-brand-dark">{{ $organizationCategory->name }}</span>
                                    @endforeach
                                </div>

                                @if ($organization->territories->isNotEmpty())
                                    <p class="mt-4 text-sm/5 text-brand-gray-dark">{{ $organization->territories->pluck('name')->unique()->implode(', ') }}</p>
                                @endif

                                <a href="{{ route('organizations.show', ['slug' => $organization->slug]) }}" class="mt-4 inline-flex items-center gap-2 text-sm/5 font-semibold text-brand-blue hover:underline">
                                    Открыть карточку
                                    <span aria-hidden="true">→</span>
                                </a>
                            </article>
                        @empty
                            <div class="rounded-2xl bg-white px-6 py-12 text-center">
                                <p class="text-lg/7 font-semibold">Организации не найдены</p>
                                <p class="mt-2 text-sm/5 text-brand-gray-dark">Измените параметры фильтра и попробуйте ещё раз.</p>
                            </div>
                        @endforelse

                        @if ($organizations->hasPages())
                            <nav class="flex flex-wrap items-center justify-center gap-2 rounded-2xl bg-white px-4 py-3 shadow-sm" aria-label="Страницы каталога">
                                @if (! $organizations->onFirstPage())
                                    <a href="{{ $organizations->previousPageUrl() }}" rel="prev" class="rounded-lg border border-brand-gray px-3 py-2 text-sm/5 hover:border-brand-blue hover:text-brand-blue">Назад</a>
                                @endif
                                @for ($page = max(1, $organizations->currentPage() - 2); $page <= min($organizations->lastPage(), $organizations->currentPage() + 2); $page++)
                                    <a href="{{ $organizations->url($page) }}" @class([
                                        'rounded-lg border px-3 py-2 text-sm/5',
                                        'border-brand-blue bg-brand-blue text-white' => $page === $organizations->currentPage(),
                                        'border-brand-gray hover:border-brand-blue hover:text-brand-blue' => $page !== $organizations->currentPage(),
                                    ]) @if ($page === $organizations->currentPage()) aria-current="page" @endif>{{ $page }}</a>
                                @endfor
                                @if ($organizations->hasMorePages())
                                    <a href="{{ $organizations->nextPageUrl() }}" rel="next" class="rounded-lg border border-brand-gray px-3 py-2 text-sm/5 hover:border-brand-blue hover:text-brand-blue">Дальше</a>
                                @endif
                            </nav>
                        @endif
                    </div>
                </div>

                @if (filled($seoText))
                    <section class="rounded-2xl bg-white p-5 text-brand-dark sm:p-8 xl:p-10" aria-label="Дополнительная информация">
                        <div class="directory-seo-content">{!! $seoText !!}</div>
                    </section>
                @endif
            </div>
        </section>
    </main>

    @include('shared.partials.footer', ['settings' => $settings, 'footerBorder' => true])
    @include('shared.partials.request-modal', ['settings' => $settings])
    @include('shared.partials.auth-modal', ['settings' => $settings])
@endsection

@push('styles')
    <style>
        .directory-seo-content :is(h2, h3, h4) { margin: 1.5rem 0 .75rem; }
        .directory-seo-content :is(p, ul, ol, blockquote) { margin: 0 0 1rem; }
        .directory-seo-content ul { list-style: disc; padding-left: 1.5rem; }
        .directory-seo-content ol { list-style: decimal; padding-left: 1.5rem; }
        .directory-seo-content a { color: #1450a3; text-decoration: underline; }
    </style>
@endpush

@push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        (() => {
            const form = document.querySelector('[data-directory-search-form]');
            const results = document.querySelector('[data-directory-results]');

            if (!form || !results) return;

            let activeRequest = null;

            const loadResults = async (url, updateHistory = true) => {
                activeRequest?.abort();
                const request = new AbortController();
                activeRequest = request;
                results.setAttribute('aria-busy', 'true');

                try {
                    const response = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                        signal: request.signal,
                    });

                    if (!response.ok) throw new Error(`Directory request failed: ${response.status}`);

                    const documentFragment = new DOMParser().parseFromString(await response.text(), 'text/html');
                    const nextResults = documentFragment.querySelector('[data-directory-results]');

                    if (!nextResults) throw new Error('Directory results were not found in the response');

                    results.innerHTML = nextResults.innerHTML;

                    if (updateHistory) {
                        const nextUrl = new URL(url, window.location.href);
                        window.history.pushState({}, '', `${nextUrl.pathname}${nextUrl.search}${nextUrl.hash}`);
                    }
                } catch (error) {
                    if (error.name === 'AbortError') return;

                    console.error(error);
                    window.location.assign(url);
                } finally {
                    if (activeRequest === request) {
                        activeRequest = null;
                        results.removeAttribute('aria-busy');
                    }
                }
            };

            form.addEventListener('submit', (event) => {
                event.preventDefault();

                const nextUrl = new URL(form.action, window.location.href);
                const query = new URLSearchParams();

                for (const [name, value] of new FormData(form).entries()) {
                    if (value !== '') query.append(name, value.toString());
                }

                nextUrl.search = query.toString();
                loadResults(nextUrl.href);
            });

            results.addEventListener('click', (event) => {
                const link = event.target.closest('a[href]');
                if (!link || !results.contains(link)) return;

                const nextUrl = new URL(link.href, window.location.href);
                if (nextUrl.origin !== window.location.origin || nextUrl.pathname !== window.location.pathname || !nextUrl.searchParams.has('page')) return;

                event.preventDefault();
                loadResults(nextUrl.href);
            });

            window.addEventListener('popstate', () => {
                window.dispatchEvent(new CustomEvent('directory-search-restored'));
                loadResults(window.location.href, false);
            });
        })();
    </script>
@endpush
