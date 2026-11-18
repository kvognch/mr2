@extends('layouts.app')
@section('body-attrs')
x-data="{ mobileMenuOpen: false, requestModalOpen: false, ratingInfoModalOpen: false, contractorReviewModalOpen: false, authModalOpen: false, authModalMode: 'login' }" x-effect="window.setBodyScrollLock(mobileMenuOpen || requestModalOpen || ratingInfoModalOpen || contractorReviewModalOpen || authModalOpen || $store.reviewModalOpen)"
@endsection

@section('content')
@include('shared.partials.header', ['settings'=>$settings])
<main class="editorial-page {{ request()->routeIs('blog.show', 'news.show') ? 'editorial-reading-page' : '' }} bg-brand-gray-light-2" data-editorial-kind="{{ $kind }}"><div class="editorial-shell container-base">
<nav aria-label="Хлебные крошки" class="editorial-breadcrumbs text-brand-gray-dark"><a href="/" class="hover:underline">Главная</a> / <a href="{{ route($kind.'.index') }}" class="hover:underline">{{ $kind==='blog'?'Блог':'Новости' }}</a>@if(request()->routeIs('blog.show', 'news.show')) / {{ $item->title }}@endif</nav>
@yield('editorial')
</div></main>
@include('shared.partials.footer', ['settings'=>$settings, 'footerBorder'=>true])
@include('shared.partials.request-modal', ['settings'=>$settings])
@include('shared.partials.auth-modal', ['settings'=>$settings])
@endsection
@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush
