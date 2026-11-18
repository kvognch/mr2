<nav class="editorial-pagination" aria-label="Страницы материалов">
    @if ($items->onFirstPage())<span class="editorial-page-link is-disabled" aria-disabled="true">Назад</span>
    @else<a class="editorial-page-link" href="{{ $items->previousPageUrl() }}" rel="prev">Назад</a>@endif
    @foreach ($items->getUrlRange(max(1, $items->currentPage()-2), min($items->lastPage(), $items->currentPage()+2)) as $page => $url)
        <a href="{{ $url }}" @class(['editorial-page-link', 'is-current' => $page === $items->currentPage()]) @if($page === $items->currentPage())aria-current="page"@endif>{{ $page }}</a>
    @endforeach
    @if ($items->hasMorePages())<a class="editorial-page-link" href="{{ $items->nextPageUrl() }}" rel="next">Вперёд</a>
    @else<span class="editorial-page-link is-disabled" aria-disabled="true">Вперёд</span>@endif
</nav>
