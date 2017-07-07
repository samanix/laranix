@if ($paginator->hasPages())
    <div class="ui pagination menu">
        @if ($paginator->onFirstPage())
            <a class="disabled item">&laquo;</a>
        @else
            <a class="item" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo;</a>
        @endif

        @foreach($elements as $element)
            @if (is_string($element))
                <a class="disabled item">{{ $element }}</a>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page === $paginator->currentPage())
                        <a class="active item">{{ $page }}</a>
                    @else
                        <a class="item" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="item" href="{{ $paginator->nextPageUrl() }}" rel="next">&raquo;</a>
        @else
            <a class="disabled item">&raquo;</a>
        @endif
    </div>
@endif
