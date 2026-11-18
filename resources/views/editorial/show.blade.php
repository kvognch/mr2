@extends('editorial.layout')
@section('title', $item->meta_title ?: $item->title.' — МногоРесурсов')
@section('meta-description', $item->meta_description ?: ($item->excerpt ?: $item->title))
@section('og-image', $item->image_url ?: asset('assets/images/logo-mr.jpg'))
@section('editorial')
<article class="editorial-document bg-white shadow-sm" data-editorial-kind="{{ $kind }}">
    @if ($kind === 'blog')
        <header data-editorial-block="article-cover">@include('editorial.hero', ['slide' => false])</header>
    @else
        <header class="editorial-news-header" data-editorial-block="news-header">
            @include('editorial.badges')
            <h1>{{ $item->title }}</h1>
            @if ($item->excerpt)<p class="editorial-lead">{{ $item->excerpt }}</p>@endif
            <div class="editorial-meta flex flex-wrap items-center gap-4">
                <time datetime="{{ $item->published_at->toAtomString() }}">Опубликовано {{ $item->published_at->format('d.m.Y') }}</time>
                @if ($item->source_url)<a href="{{ $item->source_url }}" target="_blank" rel="noopener noreferrer" class="rounded-xl bg-brand-gray-light px-4 py-3 text-brand-blue hover:underline">Открыть первоисточник</a>@endif
            </div>
        </header>
    @endif
    <div class="editorial-document-body">
        <div class="editorial-content info-page-content {{ $kind === 'blog' ? 'blog-content' : 'news-content' }}" data-editorial-block="body">{!! $item->safe_body !!}</div>
        @if ($kind === 'news' && ($item->source_name || $item->source_document))
            <div class="editorial-source editorial-meta text-brand-gray-dark">
                @if ($item->source_name)<p>Источник: {{ $item->source_name }}</p>@endif
                @if ($item->source_document)<p>{{ $item->source_document }}</p>@endif
                @if ($item->source_checked_at)<p>Проверено {{ $item->source_checked_at->format('d.m.Y') }}</p>@endif
            </div>
        @endif
        @if ($kind === 'news' && $related->isNotEmpty())
            <section class="editorial-related space-y-5" data-editorial-block="related">
                <div class="editorial-section-heading"><span class="editorial-eyebrow">Разобраться подробнее</span><h2>Материалы по теме</h2><p class="text-brand-gray-dark">Инструкции, которые помогут подготовиться к подключению.</p></div>
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach ($related as $article)
                        <article class="editorial-small-card flex flex-col gap-4 rounded-2xl bg-brand-gray-light">
                            <span class="text-sm text-brand-blue">{{ $article->rubric?->name }}</span>
                            <h3>{{ $article->title }}</h3><p>{{ $article->excerpt }}</p>
                            <a href="{{ $article->public_url }}" class="mt-auto text-brand-blue hover:underline">Читать статью →</a>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
        @include('editorial.cta')
    </div>
</article>
@endsection
