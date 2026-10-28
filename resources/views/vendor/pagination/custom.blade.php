{{-- resources/views/vendor/pagination/custom.blade.php --}}
@if ($paginator->hasPages())
<style>
    .pgn{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin:16px 0;font-size:14px;color:#251b10}
    .pgn-info{margin:0}
    .pgn-list{display:flex;flex-wrap:wrap;gap:4px;list-style:none;margin:0;padding:0}
    .pgn-list a,.pgn-list span{display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 12px;border:1px solid #e5dccb;border-radius:8px;background:#fff;color:#251b10;text-decoration:none;line-height:1}
    .pgn-list a:hover{background:#faf5ea;border-color:#c9bba0}
    .pgn-list .active span{background:#a8302a;border-color:#a8302a;color:#fff;font-weight:600}
    .pgn-list .disabled span{opacity:.45;cursor:not-allowed}
</style>
<nav class="pgn" role="navigation" aria-label="Pagination">
    <p class="pgn-info">Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data</p>
    <ul class="pgn-list">
        @if ($paginator->onFirstPage())
            <li class="disabled"><span>&laquo; Sebelumnya</span></li>
        @else
            <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo; Sebelumnya</a></li>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="disabled"><span>{{ $element }}</span></li>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="active"><span>{{ $page }}</span></li>
                    @else
                        <li><a href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">Selanjutnya &raquo;</a></li>
        @else
            <li class="disabled"><span>Selanjutnya &raquo;</span></li>
        @endif
    </ul>
</nav>
@endif
