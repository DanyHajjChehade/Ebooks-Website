{{-- Book Planet pagination (DESIGN.md §4.14). Phones show ‹ 3 of 12 ›. --}}
@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="page-link" aria-disabled="true"><x-icon name="chevron-left" class="icon-sm"/><span class="hidden sm:inline">Previous</span><span class="sr-only sm:hidden">Previous page</span></span>
        @else
            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"><x-icon name="chevron-left" class="icon-sm"/><span class="hidden sm:inline">Previous</span><span class="sr-only sm:hidden">Previous page</span></a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="page-link hidden sm:inline-flex" aria-hidden="true">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="page-link hidden sm:inline-flex" aria-current="page"><span class="sr-only">Page </span>{{ $page }}</span>
                    @else
                        <a class="page-link hidden sm:inline-flex" href="{{ $url }}"><span class="sr-only">Page </span>{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        <span class="text-sm text-muted tabular px-2 sm:hidden" data-page-of>{{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next"><span class="hidden sm:inline">Next</span><span class="sr-only sm:hidden">Next page</span><x-icon name="chevron-right" class="icon-sm"/></a>
        @else
            <span class="page-link" aria-disabled="true"><span class="hidden sm:inline">Next</span><span class="sr-only sm:hidden">Next page</span><x-icon name="chevron-right" class="icon-sm"/></span>
        @endif
    </nav>
@endif
