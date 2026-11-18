@extends('editorial.layout')
@section('title', $kind==='blog'?'Блог МногоРесурсов':'Новости МногоРесурсов')
@section('meta-description', $kind==='blog'?'Статьи о подключении, документах, проектировании и строительстве инженерных сетей.':'Новости инженерной инфраструктуры, изменения правил и тарифов.')
@section('editorial')
<h1 class="editorial-list-title sr-only">{{ $kind==='blog'?'Блог':'Новости' }}</h1>
@if($slides->isNotEmpty())
<section class="editorial-carousel" data-editorial-block="carousel" aria-label="Главные материалы" x-data="editorialCarousel({ count: {{ $slides->count() }}, interval: {{ max(3,min(120,(int)($home['interval']??7)))*1000 }} })" @keydown.arrow-right.prevent="select(active + 1)" @keydown.arrow-left.prevent="select(active - 1)">
@foreach($slides as $item)<div x-show="active==={{ $loop->index }}" @if(!$loop->first)x-cloak @endif>@include('editorial.hero',['slide'=>true])</div>@endforeach
@if($slides->count()>1)<div class="editorial-carousel-controls flex items-center gap-3"><span class="editorial-carousel-count" x-text="(active + 1) + ' / {{ $slides->count() }}'">1 / {{ $slides->count() }}</span>@foreach($slides as $item)<button type="button" @click="select({{ $loop->index }})" :aria-pressed="active==={{ $loop->index }}" :class="{ 'is-active': active==={{ $loop->index }} }" class="editorial-carousel-dot" aria-label="Слайд {{ $loop->iteration }}"><span class="sr-only">{{ $loop->iteration }}</span></button>@endforeach</div>@endif
</section>@endif
<section class="editorial-filters" data-editorial-block="filters" aria-label="Фильтры материалов">
<h2>Выберите направление</h2>
<div class="editorial-direction-row">
<a href="{{ request()->fullUrlWithQuery(['direction'=>null,'page'=>null]) }}" @class(['editorial-direction-chip rounded-full border px-4 py-3','border-brand-blue bg-brand-gray-light'=>!request('direction'),'border-brand-gray bg-white'=>request('direction')])>Все направления</a>
@foreach($directions as $direction)<a href="{{ request()->fullUrlWithQuery(['direction'=>$direction->slug,'page'=>null]) }}" @class(['editorial-direction-chip rounded-full border px-4 py-3 hover:bg-brand-gray-light','border-brand-blue bg-brand-gray-light'=>request('direction')===$direction->slug,'border-brand-gray bg-white'=>request('direction')!==$direction->slug])><img src="{{ $direction->icon_url }}" alt="" class="size-5 shrink-0" loading="lazy" />{{ $direction->name }}</a>@endforeach
</div>
<div class="editorial-filter-bottom"><div class="editorial-filter-rubrics"><h3>Выберите рубрику</h3><div class="flex flex-wrap gap-3">
<a href="{{ request()->fullUrlWithQuery(['rubric'=>null,'page'=>null]) }}" class="editorial-rubric-chip rounded-full border px-4 py-3 {{ !request('rubric')?'is-active':' ' }}">Все материалы</a>
@foreach($rubrics as $rubric)<a href="{{ request()->fullUrlWithQuery(['rubric'=>$rubric->slug,'page'=>null]) }}" class="editorial-rubric-chip rounded-full border px-4 py-3 {{ request('rubric')===$rubric->slug?'is-active':' ' }}">{{ $rubric->name }}</a>@endforeach
</div></div><div class="editorial-filter-region"><label id="editorial-region-label">Регион</label><form aria-labelledby="editorial-region-label" action="{{ route($kind.'.index') }}" method="get">
@foreach(['direction','rubric'] as $key)@if(request($key))<input type="hidden" name="{{ $key }}" value="{{ request($key) }}">@endif @endforeach
@include('directory.partials.filter-dropdown',['name'=>'region','ariaLabel'=>'Регион','placeholder'=>'Все регионы','selectedValue'=>request('region'),'choices'=>$regions->map(fn($r)=>['value'=>(string)$r->id,'label'=>$r->name])->prepend(['value'=>'','label'=>'Все регионы'])->all(),'menuStyle'=>'filter','autoSubmit'=>true])
</form></div>
</div></section>
<section data-editorial-block="results" aria-label="{{ $kind==='blog'?'Статьи':'Новости' }}" x-data="{ next: @js($items->nextPageUrl()), loading: false, error: '', async more() { if (!this.next || this.loading) return; this.loading=true; this.error=''; try { const r=await fetch(this.next,{headers:{'X-Editorial-More':'1'}}); if (!r.ok) throw new Error(); const node=document.createElement('div'); node.innerHTML=await r.text(); this.next=node.querySelector('template[data-next-url]')?.dataset.nextUrl || null; node.querySelectorAll('template').forEach(e=>e.remove()); this.$refs.cards.append(...node.children); } catch(e) { this.error='Не удалось загрузить материалы. Попробуйте ещё раз.'; } finally { this.loading=false; } } }" class="space-y-6">
<div class="editorial-section-heading"><h2>{{ $kind==='blog'?'Статьи':'Новости по выбранным параметрам' }}</h2><p class="text-brand-gray-dark">{{ $kind==='blog'?'Материалы по подключению и инженерным сетям.':'Последние новости по выбранным направлениям и регионам.' }}</p></div>
<div x-ref="cards" class="editorial-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">@include('editorial.cards')</div>
@if($items->isEmpty())<div class="rounded-2xl bg-white p-8 text-center">Материалов пока нет. @if(request()->filled('direction') || request()->filled('rubric') || request()->filled('region'))<a href="{{ route($kind.'.index') }}" class="editorial-filter-reset text-brand-blue hover:underline">Сбросить фильтры</a>@endif</div>@endif
@if($items->total()>12)
<div class="editorial-feed-navigation">
    @include('editorial.pagination')
    <button type="button" class="editorial-more-button" @click="more()" :disabled="loading" x-show="next" x-text="loading ? 'Загрузка…' : 'Показать ещё'">Показать ещё</button>
    <p x-text="error" role="status" class="editorial-feed-error"></p>
</div>
@endif
</section>
@if($kind==='blog' && $latestNews->isNotEmpty())
<section class="editorial-section space-y-6" data-editorial-block="latest-news">
    <div class="editorial-section-heading"><h2>Новости</h2><p class="text-brand-gray-dark">Последние изменения для выбранного региона.</p></div>
    <div class="grid gap-4 md:grid-cols-3">
        @foreach($latestNews as $news)
        <a href="{{ $news->public_url }}" class="editorial-small-card editorial-latest-news space-y-3 rounded-2xl bg-white">
            <div class="editorial-meta flex flex-wrap gap-x-2 text-brand-blue"><time datetime="{{ $news->published_at->toAtomString() }}">{{ $news->published_at->format('d.m.Y') }}</time>@include('editorial.regions',['item'=>$news])</div>
            <h3>{{ $news->title }}</h3>
            <p class="editorial-meta text-brand-gray-dark">{{ $news->directions->pluck('name')->implode(' · ') }}</p>
        </a>
        @endforeach
    </div>
</section>
@endif
@endsection
