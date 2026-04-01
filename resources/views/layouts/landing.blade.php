@extends('layouts.app')

@section('title', $settings['meta']['home']['title'] ?? 'МНОГОРЕСУРСОВ')
@section('meta-description', $settings['meta']['home']['description'] ?? '')
@section('body-attrs')
x-data="{ mobileMenuOpen: false, requestModalOpen: false, videoModalOpen: false, authModalOpen: false, authModalMode: 'login' }" x-effect="document.body.style.overflow = (mobileMenuOpen || $store.reviewModalOpen || requestModalOpen || videoModalOpen || authModalOpen) ? 'hidden' : ''"
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
@endpush

@section('content')
    @yield('page-content')
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endpush
