@if ($paginator->total() > $paginator->perPage())
    @php
        $current = $paginator->currentPage();
        $last    = $paginator->lastPage();
        $window  = 3;

        // calcula janela deslizante: ex 1-2-3, depois 2-3-4, etc.
        $start = max(1, $current - 1);
        $end   = min($last, $start + $window - 1);

        if (($end - $start + 1) < $window) {
            $start = max(1, $end - $window + 1);
        }
    @endphp

    <style>
        .ep-pagination-wrapper{
            display:flex;
            flex-direction:column;
            align-items:center;
            gap:8px;
            margin-top:16px;
        }
        .ep-pagination{
            display:inline-flex;
            align-items:center;
            gap:8px;
        }
        .ep-page-btn{
            min-width:36px;
            height:36px;
            border-radius:999px;
            border:none;
            background:#fff;
            box-shadow:0 4px 12px rgba(15,23,42,.08);
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:14px;
            color:#111827;
            text-decoration:none;
            cursor:pointer;
            transition:.2s;
        }
        .ep-page-btn:hover{
            transform:translateY(-1px);
            box-shadow:0 6px 16px rgba(15,23,42,.12);
        }
        .ep-page-btn--active{
            background:linear-gradient(135deg,#ec4899 0%,#db2777 100%);
            color:#fff;
        }
        .ep-page-btn--disabled{
            opacity:.4;
            cursor:default;
            box-shadow:none;
        }

        .ep-pagination-jump{
            display:flex;
            align-items:center;
            gap:6px;
            font-size:13px;
            color:#6b7280;
        }
        .ep-pagination-jump input[type="number"]{
            width:64px;
            padding:6px 8px;
            border-radius:999px;
            border:1px solid #e5e7eb;
            font-size:13px;
            text-align:center;
        }
        .ep-pagination-jump button{
            border:none;
            border-radius:999px;
            padding:6px 12px;
            font-size:13px;
            font-weight:500;
            cursor:pointer;
            background:#fff;
            color:#ec4899;
            border:1px solid #fbcfe8;
            transition:.2s;
        }
        .ep-pagination-jump button:hover{
            background:#fdf2f8;
        }
        .ep-pagination-info{
            font-size:12px;
            color:#9ca3af;
        }

        @media(max-width:640px){
            .ep-pagination-wrapper{
                gap:6px;
            }
            .ep-pagination{
                gap:4px;
            }
            .ep-page-btn{
                min-width:32px;
                height:32px;
                font-size:13px;
            }
        }
    </style>

    <div class="ep-pagination-wrapper">
        {{-- info dos registros --}}
        <div class="ep-pagination-info">
            Mostrando {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}
            de {{ $paginator->total() }} registros
        </div>

        {{-- paginação principal --}}
        <div class="ep-pagination">
            {{-- anterior --}}
            @if ($paginator->onFirstPage())
                <span class="ep-page-btn ep-page-btn--disabled">&lt;</span>
            @else
                <a class="ep-page-btn" href="{{ $paginator->previousPageUrl() }}">&lt;</a>
            @endif

            {{-- páginas da janela --}}
            @for ($page = $start; $page <= $end; $page++)
                @if ($page == $current)
                    <span class="ep-page-btn ep-page-btn--active">{{ $page }}</span>
                @else
                    <a class="ep-page-btn" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                @endif
            @endfor

            {{-- próxima --}}
            @if ($paginator->hasMorePages())
                <a class="ep-page-btn" href="{{ $paginator->nextPageUrl() }}">&gt;</a>
            @else
                <span class="ep-page-btn ep-page-btn--disabled">&gt;</span>
            @endif
        </div>

        {{-- campo para ir direto pra página --}}
        <form method="GET" class="ep-pagination-jump">
            @foreach(request()->except('page') as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach

            <span>Ir para página</span>
            <input
                type="number"
                name="page"
                min="1"
                max="{{ $last }}"
                value="{{ $current }}"
            >
            <button type="submit">Ir</button>
        </form>
    </div>
@endif
