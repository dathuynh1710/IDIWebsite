@props(['paginator'])
@if($paginator->hasPages())
    <nav class="pagination" aria-label="Phân trang">
        <span>Hiển thị {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} / {{ $paginator->total() }}</span>
        <div>
            @if($paginator->onFirstPage())
                <span class="is-disabled">Trước</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev">Trước</a>
            @endif
            <span>Trang {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next">Sau</a>
            @else
                <span class="is-disabled">Sau</span>
            @endif
        </div>
    </nav>
@endif
