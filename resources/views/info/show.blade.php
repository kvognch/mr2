@extends('layouts.app')

@section('title', $page->meta_title ?: $page->title)
@section('meta-description', $page->meta_description ?: '')
@section('body-attrs')
x-data="{ mobileMenuOpen: false, requestModalOpen: false, ratingInfoModalOpen: false, contractorReviewModalOpen: false, authModalOpen: false, authModalMode: 'login' }" x-effect="window.setBodyScrollLock(mobileMenuOpen || requestModalOpen || ratingInfoModalOpen || contractorReviewModalOpen || authModalOpen || $store.reviewModalOpen)"
@endsection

@section('content')
    @include('shared.partials.header', ['settings' => $settings])

    <main class="bg-brand-gray-light-2 pb-20 lg:pb-30">
        <section class="pt-15 pb-30">
            <div class="container-base space-y-10">
                <div class="space-y-6 sm:space-y-8 lg:space-y-10">
                    <h1>{{ $page->title }}</h1>

                    <div class="space-y-5 bg-white text-brand-dark rounded-2xl sm:rounded-brand-3xl p-4 sm:p-6 xl:p-10 min-w-0">
                        <div class="info-page-content text_2">
                            {!! $page->body !!}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    @include('shared.partials.footer', ['settings' => $settings, 'footerBorder' => true])
    @include('shared.partials.request-modal', ['settings' => $settings])
    @include('shared.partials.auth-modal', ['settings' => $settings])
@endsection

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush
