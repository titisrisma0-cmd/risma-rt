@if ($paginator->hasPages())
    <nav class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 w-100 py-1 pagination-nav" aria-label="Navigasi Halaman">
        <div class="small pagination-summary text-center text-md-start" style="color: var(--ink-soft);">
            @if ($paginator->firstItem())
                {{ __('Showing') }} <span class="fw-bold" style="color: var(--ink);">{{ $paginator->firstItem() }}</span>
                {{ __('to') }} <span class="fw-bold" style="color: var(--ink);">{{ $paginator->lastItem() }}</span>
                {{ __('of') }} <span class="fw-bold" style="color: var(--ink);">{{ $paginator->total() }}</span> {{ __('results') }}
            @else
                {{ __('Showing') }} <span class="fw-bold" style="color: var(--ink);">{{ $paginator->count() }}</span> {{ __('results') }}
            @endif
        </div>

        <ul class="pagination pagination-emerald mb-0 justify-content-center flex-wrap gap-1">
            {{-- Tombol Halaman Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="page-link" aria-hidden="true">@lang('pagination.previous')</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">@lang('pagination.previous')</a>
                </li>
            @endif

            {{-- Elemen Angka Halaman --}}
            @foreach ($elements as $element)
                {{-- Separator "..." --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                @endif

                {{-- Nomor Halaman --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Tombol Halaman Berikutnya --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">@lang('pagination.next')</a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="page-link" aria-hidden="true">@lang('pagination.next')</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
