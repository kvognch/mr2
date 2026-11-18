@foreach($items as $item)
<article data-direction="{{ $item->directions->first()?->slug }}" class="editorial-card flex min-w-0 flex-col overflow-hidden rounded-2xl bg-white shadow-sm">
<div class="editorial-card-visual {{ $item->image_url ? 'editorial-card-visual--image' : '' }} relative bg-brand-gray-light">
@if($item->image_url)<img src="{{ $item->image_url }}" alt="" loading="lazy" class="absolute inset-0 size-full object-cover"/>@endif
<div class="relative p-4">@if($kind==='blog')@if($item->rubric)<span class="editorial-card-rubric rounded-full bg-brand-gray-light px-3 py-1 text-sm text-brand-blue">{{ $item->rubric->name }}</span>@endif @else @include('editorial.badges',['hideRegions'=>true]) @endif</div></div>
<div class="editorial-card-body flex flex-1 flex-col">
@if($item->kind==='news')<div class="editorial-card-regions flex flex-wrap gap-2">@if($item->is_federal)<span>Вся Россия</span>@else @foreach($item->regions as $region)<span class="rounded-full bg-brand-gray-light px-3 py-1">{{ $region->name }}</span>@endforeach @endif</div>@endif<h2 class="editorial-card-title">{{ $item->title }}</h2><p class="text-brand-gray-dark">{{ $item->excerpt }}</p>
@if($item->kind==='news' && $item->audience)<p class="editorial-audience rounded-xl bg-brand-gray-light p-3 text-sm"><strong>Кого касается:</strong> {{ $item->audience }}</p>@endif
<div class="editorial-card-footer mt-auto flex flex-wrap items-center justify-between gap-3"><span class="text-brand-gray-dark">@if($item->kind==='blog')@if($item->content_updated_at)Обновлено {{ $item->content_updated_at->format('d.m.Y') }}@endif @else {{ $item->published_at->format('d.m.Y') }} @endif</span><a href="{{ $item->public_url }}" class="editorial-card-open">{{ $item->kind==='blog'?'Читать статью':'Читать новость' }} →</a></div>
</div></article>
@endforeach
<template data-next-url="{{ $items->nextPageUrl() }}"></template>
