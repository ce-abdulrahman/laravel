@if ($paginator->hasPages())
<nav class="quran-pagination-nav d-flex flex-wrap align-items-center justify-content-between gap-2 px-1" aria-label="Pagination">

    {{-- Mobile: Simple prev/next --}}
    <div class="d-flex d-sm-none gap-2 w-100 justify-content-between">
        @if ($paginator->onFirstPage())
            <span class="quran-page-btn disabled">
                <i class="bi bi-chevron-right"></i>
                {{ __('common.previous') }}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="quran-page-btn">
                <i class="bi bi-chevron-right"></i>
                {{ __('common.previous') }}
            </a>
        @endif

        <span class="small text-muted align-self-center">
            {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
        </span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="quran-page-btn">
                {{ __('common.next') }}
                <i class="bi bi-chevron-left"></i>
            </a>
        @else
            <span class="quran-page-btn disabled">
                {{ __('common.next') }}
                <i class="bi bi-chevron-left"></i>
            </span>
        @endif
    </div>

    {{-- Desktop: Full pagination --}}
    <div class="d-none d-sm-flex align-items-center gap-3 w-100 justify-content-between">

        {{-- Info text --}}
        <p class="small text-muted mb-0">
            {{ __('common.showing') }}
            <span class="fw-semibold text-body">{{ $paginator->firstItem() }}</span>
            {{ __('common.to') }}
            <span class="fw-semibold text-body">{{ $paginator->lastItem() }}</span>
            {{ __('common.of') }}
            <span class="fw-semibold text-body">{{ $paginator->total() }}</span>
            {{ __('common.results') }}
        </p>

        {{-- Page buttons --}}
        <ul class="quran-pagination mb-0">

            {{-- Prev --}}
            @if ($paginator->onFirstPage())
                <li class="quran-page-item disabled">
                    <span class="quran-page-link" aria-label="{{ __('common.previous') }}">
                        <i class="bi bi-chevron-right"></i>
                    </span>
                </li>
            @else
                <li class="quran-page-item">
                    <a class="quran-page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('common.previous') }}">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            @endif

            {{-- Page Numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="quran-page-item disabled">
                        <span class="quran-page-link quran-page-dots">{{ $element }}</span>
                    </li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="quran-page-item active" aria-current="page">
                                <span class="quran-page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="quran-page-item">
                                <a class="quran-page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li class="quran-page-item">
                    <a class="quran-page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('common.next') }}">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
            @else
                <li class="quran-page-item disabled">
                    <span class="quran-page-link" aria-label="{{ __('common.next') }}">
                        <i class="bi bi-chevron-left"></i>
                    </span>
                </li>
            @endif

        </ul>
    </div>

</nav>
@endif
