<div class="editorial-hero relative overflow-hidden rounded-2xl bg-brand-dark text-white">
    @if ($item->image_url)
        <img src="{{ $item->image_url }}" alt="" class="absolute inset-0 size-full object-cover" />
        <div class="absolute inset-0 bg-black/60"></div>
    @endif
    <div class="editorial-hero-copy relative">
        @if ($kind === 'blog')
            @include('editorial.badges', ['hideRegions' => true])
        @endif
        @if ($slide ?? false)
            <h2 class="max-w-4xl">{{ $item->title }}</h2>
        @else
            <h1 class="max-w-4xl">{{ $item->title }}</h1>
        @endif
        @if ($item->excerpt)<p class="editorial-lead max-w-3xl">{{ $item->excerpt }}</p>@endif
        <div class="editorial-meta flex flex-wrap items-center gap-4">
            @if ($item->kind === 'blog')
                @if ($item->content_updated_at)<time datetime="{{ $item->content_updated_at->toAtomString() }}">Обновлено {{ $item->content_updated_at->format('d.m.Y') }}</time>@endif
            @else
                <time datetime="{{ $item->published_at->toAtomString() }}">Опубликовано {{ $item->published_at->format('d.m.Y') }}</time>
            @endif
            @if ($slide ?? false)@include('editorial.regions')@endif
        </div>
        @if ($slide ?? false)
            <a class="button_1 inline-flex" href="{{ $item->public_url }}">{{ $kind === 'blog' ? 'Читать статью' : 'Читать новость' }}</a>
        @endif
    </div>
</div>
