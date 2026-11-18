@if ($item->kind === 'news' && $item->is_federal)
<span>Вся Россия</span>
@else
@foreach ($item->regions as $region)<span>{{ $region->name }}</span>@endforeach
@endif
