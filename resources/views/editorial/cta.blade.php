@if (data_get($item->cta, 'enabled') && data_get($item->cta, 'title') && data_get($item->cta, 'button'))
<section class="editorial-cta space-y-5 rounded-2xl bg-brand-dark text-white" data-editorial-block="cta">
    <span class="editorial-eyebrow">Следующий шаг</span>
    <h2>{{ $item->cta['title'] }}</h2>
    @if (data_get($item->cta, 'text'))<p>{{ $item->cta['text'] }}</p>@endif
    <a href="{{ $item->map_url }}" class="editorial-cta-button inline-flex">{{ $item->cta['button'] }}</a>
</section>
@endif
