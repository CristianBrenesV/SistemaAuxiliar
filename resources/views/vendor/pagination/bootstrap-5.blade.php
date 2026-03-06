@if ($paginator->hasPages())
<nav aria-label="Paginación de usuarios" class="mt-4">
    <ul class="pagination pagination-dark justify-content-center">

        {{-- Botón anterior --}}
        @if ($paginator->onFirstPage())
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" aria-label="Anterior">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        @endif

        {{-- Números --}}
        @foreach ($elements as $element)

            {{-- Array de páginas --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url }}">
                            {{ $page }}
                        </a>
                    </li>
                @endforeach
            @endif

        @endforeach

        {{-- Botón siguiente --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" aria-label="Siguiente">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        @endif

    </ul>
</nav>
@endif
