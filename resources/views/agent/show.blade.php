@extends('layouts.app')

@php
    $contractorMetaTitle = str_replace('%name%', $contractor->short_name, $settings['meta']['contractor']['title'] ?? '%name%');
    $contractorMetaDescription = str_replace('%name%', $contractor->short_name, $settings['meta']['contractor']['description'] ?? '');
@endphp

@section('title', $contractorMetaTitle)
@section('meta-description', $contractorMetaDescription)
@section('body-attrs')
x-data="{ mobileMenuOpen: false, requestModalOpen: false, ratingInfoModalOpen: false, contractorTerritoryMapModalOpen: false, contractorReviewModalOpen: false, authModalOpen: false, authModalMode: 'login' }" x-effect="window.setBodyScrollLock(mobileMenuOpen || requestModalOpen || ratingInfoModalOpen || contractorTerritoryMapModalOpen || contractorReviewModalOpen || authModalOpen || $store.reviewModalOpen)"
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
@endpush

@section('content')
    @include('shared.partials.header', ['settings' => $settings])
    @include('agent.partials.main')
    @include('shared.partials.footer', ['settings' => $settings, 'footerBorder' => true])
    @include('shared.partials.request-modal', ['settings' => $settings])
    @include('shared.partials.auth-modal', ['settings' => $settings])
    @include('agent.partials.review-modal', ['settings' => $settings])
@endsection

@push('scripts')
<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU{{ $yandexMapsApiKey ? '&apikey=' . urlencode($yandexMapsApiKey) : '' }}" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
    window.contractorTerritoriesMapPayload = {
        territoryIds: @js($contractorTerritoryIds),
    };

    (function initContractorTerritoriesMapModal() {
        const state = {
            map: null,
            loaded: false,
            features: [],
        };

        const payload = window.contractorTerritoriesMapPayload ?? {};
        const territoryIds = Array.isArray(payload.territoryIds) ? payload.territoryIds.map((id) => Number(id)).filter((id) => Number.isInteger(id) && id > 0) : [];

        const setEmptyState = (message) => {
            const mapEl = document.getElementById('contractor-territories-map');
            const emptyEl = document.getElementById('contractor-territories-map-empty');

            if (mapEl) {
                mapEl.classList.toggle('hidden', Boolean(message));
            }

            if (emptyEl) {
                emptyEl.textContent = message || '';
                emptyEl.classList.toggle('hidden', !message);
            }
        };

        const drawFeatures = (items) => {
            if (!state.map) {
                return;
            }

            state.map.geoObjects.removeAll();

            if (!items.length) {
                setEmptyState('Для выбранного подрядчика не найдены геометрии привязанных районов.');
                state.map.container.fitToViewport();
                return;
            }

            setEmptyState('');

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

                const attachEvents = (obj) => {
                    obj.events.add('mouseenter', () => obj.options.set('fillOpacity', 0.4));
                    obj.events.add('mouseleave', () => obj.options.set('fillOpacity', 0.2));
                    collection.add(obj);
                };

                if (geometry.type === 'Polygon') {
                    attachEvents(new ymaps.Polygon(
                        geometry.coordinates,
                        { hintContent: item.name },
                        objectOptions
                    ));
                } else if (geometry.type === 'MultiPolygon') {
                    for (const polygonCoordinates of geometry.coordinates) {
                        attachEvents(new ymaps.Polygon(
                            polygonCoordinates,
                            { hintContent: item.name },
                            objectOptions
                        ));
                    }
                }

                if (item.bbox && item.bbox.min_lat !== null) {
                    bounds.push([
                        [Number(item.bbox.min_lat), Number(item.bbox.min_lon)],
                        [Number(item.bbox.max_lat), Number(item.bbox.max_lon)],
                    ]);
                }
            }

            state.map.geoObjects.add(collection);

            if (!collection.getLength()) {
                setEmptyState('Для выбранного подрядчика не найдены геометрии привязанных районов.');
                state.map.container.fitToViewport();
                return;
            }

            if (bounds.length) {
                const merged = [
                    [Math.min(...bounds.map((b) => b[0][0])), Math.min(...bounds.map((b) => b[0][1]))],
                    [Math.max(...bounds.map((b) => b[1][0])), Math.max(...bounds.map((b) => b[1][1]))],
                ];
                state.map.setBounds(merged, { checkZoomRange: true, zoomMargin: 20 });
            } else {
                state.map.container.fitToViewport();
            }
        };

        const ensureMap = async () => {
            const container = document.getElementById('contractor-territories-map');
            if (!container) {
                return;
            }

            if (!territoryIds.length) {
                setEmptyState('У подрядчика не указаны районы работы.');
                return;
            }

            if (typeof ymaps === 'undefined') {
                setEmptyState('Не удалось загрузить карту.');
                return;
            }

            if (!state.map) {
                state.map = new ymaps.Map('contractor-territories-map', {
                    center: [61.5240, 105.3188],
                    zoom: 3,
                    controls: ['zoomControl', 'typeSelector', 'fullscreenControl']
                });

                state.map.container.fitToViewport();
                window.addEventListener('resize', () => {
                    if (state.map) {
                        state.map.container.fitToViewport();
                    }
                }, { passive: true });
            }

            if (!state.loaded) {
                try {
                    const response = await fetch('/api/geo/map-features');
                    const payload = await response.json();
                    const items = Array.isArray(payload?.data) ? payload.data : [];
                    state.features = items.filter((item) => territoryIds.includes(Number(item.id)));
                    state.loaded = true;
                } catch (error) {
                    console.error('Failed to load contractor map features', error);
                    setEmptyState('Не удалось загрузить геометрию районов.');
                    return;
                }
            }

            drawFeatures(state.features);
        };

        window.addEventListener('contractor-territories-map-modal-opened', () => {
            if (typeof ymaps === 'undefined') {
                setEmptyState('Не удалось загрузить карту.');
                return;
            }

            ymaps.ready(() => {
                window.requestAnimationFrame(() => {
                    ensureMap();
                });
            });
        });
    })();
</script>
@endpush
