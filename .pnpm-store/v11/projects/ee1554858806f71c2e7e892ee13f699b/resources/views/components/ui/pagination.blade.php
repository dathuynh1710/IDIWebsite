@props([
    'paginator',
    'showSummary' => true,
    'perPageOptions' => [],
    'perPageModel' => 'perPage',
])

@php
    $usesLivewirePagination = app('livewire')->current() !== null;
    $perPageOptions = array_values(array_unique(array_filter(
        array_map('intval', $perPageOptions),
        fn ($option) => $option > 0,
    )));
    $showsPerPageSelector = $usesLivewirePagination && count($perPageOptions) > 0;
@endphp

@if($paginator->hasPages() || $showsPerPageSelector)
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $pageName = $paginator->getPageName();
        $visiblePages = [1, $lastPage];

        for ($page = max(1, $currentPage - 2); $page <= min($lastPage, $currentPage + 2); $page++) {
            $visiblePages[] = $page;
        }

        $visiblePages = array_values(array_unique($visiblePages));
        sort($visiblePages);

        $paginationItems = [];
        $previousVisiblePage = null;

        foreach ($visiblePages as $page) {
            if ($previousVisiblePage !== null && $page - $previousVisiblePage > 1) {
                $paginationItems[] = null;
            }

            $paginationItems[] = $page;
            $previousVisiblePage = $page;
        }
    @endphp

    <nav {{ $attributes->class(['pagination']) }} aria-label="Phân trang">
        <div class="pagination__meta">
            @if($showsPerPageSelector)
                <label class="pagination__per-page">
                    <span>Hiển thị</span>
                    <span class="pagination__select-wrap">
                        <select wire:model.live="{{ $perPageModel }}" aria-label="Số kết quả hiển thị trên mỗi trang">
                            @foreach($perPageOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                        <x-ui.icon name="chevron-down" size="15" />
                    </span>
                    <span>/ trang</span>
                </label>
            @endif

            @if($showSummary)
                <p class="pagination__summary" aria-live="polite">
                    Kết quả <strong>{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</strong>
                    trên tổng số <strong>{{ $paginator->total() }}</strong>
                </p>
            @endif
        </div>

        <div class="pagination__controls">
            @if($paginator->onFirstPage())
                <span class="pagination__nav is-disabled" aria-disabled="true">
                    <x-ui.icon name="chevron-left" size="16" />
                    <span class="pagination__nav-label">Trước</span>
                </span>
            @elseif($usesLivewirePagination)
                <button class="pagination__nav" type="button" wire:click="previousPage('{{ $pageName }}')" wire:loading.attr="disabled" aria-label="Về trang trước">
                    <x-ui.icon name="chevron-left" size="16" />
                    <span class="pagination__nav-label">Trước</span>
                </button>
            @else
                <a class="pagination__nav" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Về trang trước">
                    <x-ui.icon name="chevron-left" size="16" />
                    <span class="pagination__nav-label">Trước</span>
                </a>
            @endif

            <ol class="pagination__pages" aria-label="Danh sách trang">
                @foreach($paginationItems as $item)
                    @if($item === null)
                        <li><span class="pagination__ellipsis" aria-hidden="true">…</span></li>
                    @elseif($item === $currentPage)
                        <li wire:key="paginator-{{ $pageName }}-page-{{ $item }}">
                            <span class="pagination__page is-current" aria-current="page" aria-label="Trang {{ $item }}, trang hiện tại">
                                {{ $item }}
                            </span>
                        </li>
                    @elseif($usesLivewirePagination)
                        <li wire:key="paginator-{{ $pageName }}-page-{{ $item }}">
                            <button class="pagination__page" type="button" wire:click="gotoPage({{ $item }}, '{{ $pageName }}')" wire:loading.attr="disabled" aria-label="Đến trang {{ $item }}">
                                {{ $item }}
                            </button>
                        </li>
                    @else
                        <li>
                            <a class="pagination__page" href="{{ $paginator->url($item) }}" aria-label="Đến trang {{ $item }}">
                                {{ $item }}
                            </a>
                        </li>
                    @endif
                @endforeach
            </ol>

            @if($paginator->hasMorePages())
                @if($usesLivewirePagination)
                    <button class="pagination__nav" type="button" wire:click="nextPage('{{ $pageName }}')" wire:loading.attr="disabled" aria-label="Đến trang sau">
                        <span class="pagination__nav-label">Sau</span>
                        <x-ui.icon name="chevron-right" size="16" />
                    </button>
                @else
                    <a class="pagination__nav" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Đến trang sau">
                        <span class="pagination__nav-label">Sau</span>
                        <x-ui.icon name="chevron-right" size="16" />
                    </a>
                @endif
            @else
                <span class="pagination__nav is-disabled" aria-disabled="true">
                    <span class="pagination__nav-label">Sau</span>
                    <x-ui.icon name="chevron-right" size="16" />
                </span>
            @endif
        </div>
    </nav>
@endif
