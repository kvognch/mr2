@extends('layouts.landing')

@section('page-content')
    @include('shared.partials.header', ['settings' => $settings])

    <main>
        @include('home.partials.hero', ['settings' => $settings])
        @include('home.partials.search', ['settings' => $settings])
        @include('home.partials.be-pro', ['settings' => $settings])
        @if ($settings['plans']['enabled'] ?? true)
            @include('home.partials.plans', ['settings' => $settings])
        @endif
        @include('home.partials.join', ['settings' => $settings])
        @include('home.partials.reviews', ['settings' => $settings])
        @include('home.partials.need-help', ['settings' => $settings])
    </main>

    @include('shared.partials.footer', ['settings' => $settings])
    @include('shared.partials.request-modal', ['settings' => $settings])
    @include('shared.partials.auth-modal', ['settings' => $settings])
    @include('shared.partials.video-modal', ['settings' => $settings])
@endsection
