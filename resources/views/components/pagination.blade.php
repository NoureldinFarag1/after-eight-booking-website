@props(['paginator'])
@php
    $isPaginator = $paginator instanceof \Illuminate\Contracts\Pagination\Paginator;
@endphp
@if ($isPaginator && method_exists($paginator, 'hasPages') && $paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $window = 2; // how many pages either side of current
        $pages = [];
        // Always include first and last with window around current, insert ellipses when gaps
        for ($page = 1; $page <= $last; $page++) {
            if ($page == 1 || $page == $last || ($page >= $current - $window && $page <= $current + $window)) {
                $pages[] = ['page' => $page];
            } else {
                // Insert a dots marker if previous item not dots and not adjacent
                $prev = end($pages);
                if (!isset($prev['dots'])) {
                    $pages[] = ['dots' => true];
                }
            }
        }
    @endphp
    <nav class="ae-pagination" role="navigation" aria-label="Pagination Navigation">
        <ul class="pagination pagination-sm mb-0">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="Previous">
                    <span class="page-link" aria-hidden="true">&laquo;</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous">&laquo;</a>
                </li>
            @endif

            {{-- Page Number Links --}}
            @foreach($pages as $entry)
                @if(isset($entry['dots']))
                    <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                @else
                    @php $page = $entry['page']; @endphp
                    @if($page === $current)
                        <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a></li>
                    @endif
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">&raquo;</a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="Next">
                    <span class="page-link" aria-hidden="true">&raquo;</span>
                </li>
            @endif
        </ul>
        <div class="small text-muted mt-2 text-center">
            Showing
            @if ($paginator->firstItem())
                <span class="fw-semibold">{{ $paginator->firstItem() }}</span>
                to <span class="fw-semibold">{{ $paginator->lastItem() }}</span>
            @else
                <span class="fw-semibold">{{ $paginator->count() }}</span>
            @endif
            of <span class="fw-semibold">{{ $paginator->total() }}</span> results
        </div>
    </nav>
@endif
