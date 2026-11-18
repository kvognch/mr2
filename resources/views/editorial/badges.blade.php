<div class="editorial-badges flex flex-wrap">
@foreach($item->directions as $direction)<span class="rounded-full bg-brand-gray-light px-3 py-1 text-brand-blue">{{ $direction->name }}</span>@endforeach
@if($item->rubric)<span class="rounded-full bg-brand-gray-light px-3 py-1 text-brand-blue">{{ $item->rubric->name }}</span>@endif
@if(!($hideRegions??false))
@if($item->kind==='news' && $item->is_federal)<span class="rounded-full bg-brand-gray-light px-3 py-1 text-brand-blue">Вся Россия</span>@else @foreach($item->regions as $region)<span class="rounded-full bg-brand-gray-light px-3 py-1 text-brand-blue">{{ $region->name }}</span>@endforeach @endif
@endif
</div>