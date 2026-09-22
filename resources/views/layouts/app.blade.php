<!doctype html>
@php
    $metaTitle = \App\Support\PublicSeo::title(trim((string) $__env->yieldContent('title', 'МНОГОРЕСУРСОВ')));
    $metaDescription = \App\Support\PublicSeo::description(trim((string) $__env->yieldContent('meta-description', '')));
    $canonicalUrl = \App\Support\PublicSeo::canonicalUrl();
    $ogImage = trim((string) $__env->yieldContent('og-image', '')) ?: asset('assets/images/logo-mr.jpg');
    $ogImageWidth = trim((string) $__env->yieldContent('og-image-width', '1200'));
    $ogImageHeight = trim((string) $__env->yieldContent('og-image-height', '630'));
    $ogImageType = trim((string) $__env->yieldContent('og-image-type', 'image/jpeg'));
    $ogImageAlt = trim((string) $__env->yieldContent('og-image-alt', '')) ?: $metaDescription;
    $trackingSettings = \App\Support\SeoSettings::all()['tracking'] ?? [];
    $yandexMetricaCode = trim((string) ($trackingSettings['yandex_metrica'] ?? ''));
@endphp
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="{{ $metaDescription }}" />
    <meta name="robots" content="{{ \App\Support\PublicSeo::robots() }}" />
    <title>{{ $metaTitle }}</title>
    @if ($canonicalUrl)
        <link rel="canonical" href="{{ $canonicalUrl }}" />
    @endif
    <meta property="og:title" content="{{ $metaTitle }}" />
    <meta property="og:description" content="{{ $metaDescription }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ \App\Support\PublicSeo::ogUrl() }}" />
    <meta property="og:image" content="{{ $ogImage }}" />
    <meta property="og:image:width" content="{{ $ogImageWidth }}" />
    <meta property="og:image:height" content="{{ $ogImageHeight }}" />
    <meta property="og:image:type" content="{{ $ogImageType }}" />
    <meta property="og:image:alt" content="{{ $ogImageAlt }}" />
    <meta property="og:site_name" content="Многоресурсов" />
    <meta property="og:locale" content="ru_RU" />
    @if ($yandexMetricaCode !== '')
        {!! $yandexMetricaCode !!}
    @endif
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body @yield('body-attrs')>
@yield('content')
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@stack('scripts')
</body>
</html>
