@extends('layouts.app')

@section('title', 'Feedbacks - Estética PRO')

@section('content')
<style>
    :root {
        --primary:#ec4899; --primary-dark:#db2777; --primary-light:#fbcfe8;
        --secondary:#7e22ce; --text:#1f2937; --text-light:#6b7280;
        --success:#10b981; --warning:#f59e0b; --danger:#ef4444;
    }

    body{font-family:'Poppins',sans-serif;background:#f9fafb;color:var(--text)}
    .content{padding:11px}

    .page-header{
        display:flex;justify-content:space-between;align-items:center;
        margin-bottom:30px;gap:16px;flex-wrap:wrap;
    }
    .page-title{
        font-size:28px;font-weight:700;
        background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);
        -webkit-background-clip:text;-webkit-text-fill-color:transparent;
    }

    .header-actions{display:flex;gap:16px}
    .search-box{position:relative}
    .search-input{
        padding:12px 16px 12px 40px;border:2px solid #e5e7eb;border-radius:12px;font-size:14px;
        width:250px;transition:.3s;
    }
    .search-input:focus{
        border-color:var(--primary);outline:none;
        box-shadow:0 0 0 3px rgba(236,72,153,.2);
    }
    .search-icon{
        position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-light);
    }

    .table-container{
        background:#fff;border-radius:16px;overflow:hidden;
        box-shadow:0 4px 20px rgba(0,0,0,.05);margin-bottom:30px;overflow-x:auto;
    }
    table{width:100%;border-collapse:collapse;min-width:900px}
    thead{
        background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);
        color:#fff;
    }
    th{padding:16px;text-align:left;font-weight:500}
    td{padding:16px}
    tbody tr{border-bottom:1px solid #f3f4f6;transition:background .3s}
    tbody tr:last-child{border-bottom:none}
    tbody tr:hover{background:#f9fafb}

    .avatar{
        width:40px;height:40px;border-radius:50%;
        background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);
        display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;margin-right:12px;
    }

    .employee-info{display:flex;align-items:center}
    .employee-details h3{font-size:16px;font-weight:500;margin-bottom:4px}
    .employee-details p{font-size:14px;color:var(--text-light)}

    .badge{
        padding:6px 12px;border-radius:20px;font-size:12px;font-weight:500;display:inline-block;
    }

    .badge-green{background:#ecfdf5;color:var(--success)}
    .badge-yellow{background:#fef3c7;color:var(--warning)}
    .badge-red{background:#fee2e2;color:var(--danger)}

    .actions{display:flex;gap:8px}
    .action-btn{width:34px;height:34px;border-radius:8px;
        display:flex;align-items:center;justify-content:center;
        cursor:pointer;transition:.3s;background:none;border:none
    }

    .action-view{color:var(--primary);background:var(--primary-light)}
    .action-view:hover{background:var(--primary);color:#fff}

    @media(max-width:768px){
        .page-header{flex-direction:column;align-items:flex-start}
        .search-input{width:100%}
    }
</style>

<div class="content">

    {{-- HEADER --}}
    <div class="page-header">
        <h1 class="page-title">Feedbacks</h1>

        <div class="header-actions">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" placeholder="Buscar feedback...">
            </div>
        </div>
    </div>

    {{-- TABELA --}}
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Serviço</th>
                    <th>Nota</th>
                    <th>Comentário</th>
                    <th>Data</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>
                @forelse($feedbacks as $f)
                    <tr>
                        {{-- CLIENTE --}}
                        <td>
                            <div class="employee-info">
                                <div class="avatar">
                                    {{ strtoupper(mb_substr($f->cliente->nome ?? '', 0, 1)) }}
                                </div>
                                <div class="employee-details">
                                    <h3>{{ $f->cliente->nome ?? '—' }}</h3>
                                    <p>{{ $f->cliente->email ?? '—' }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- SERVIÇO --}}
                        <td>{{ $f->servico->nome ?? '—' }}</td>

                        {{-- NOTA --}}
                        <td>
                            @if($f->nota)
                                @php
                                    $color = $f->nota >= 4 ? 'badge-green' : ($f->nota >= 3 ? 'badge-yellow' : 'badge-red');
                                @endphp
                                <span class="badge {{ $color }}">★ {{ $f->nota }}/5</span>
                            @else
                                <span class="badge badge-yellow">Sem nota</span>
                            @endif
                        </td>

                        {{-- COMENTÁRIO --}}
                        <td style="max-width:300px;">
                            <span style="color:var(--text-light)">
                                {{ Str::limit($f->comentario ?? '—', 80) }}
                            </span>
                        </td>

                        {{-- DATA --}}
                        <td>{{ $f->created_at->format('d/m/Y H:i') }}</td>

                        {{-- AÇÕES --}}
                        <td>
                            <div class="actions">
                                <a href="{{ route('feedbacks.show', $f->id) }}"
                                   class="action-btn action-view"
                                   title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:32px;color:var(--text-light);">
                            <i class="fas fa-comment" style="font-size:28px;color:#e5e7eb;margin-bottom:10px"></i>
                            Nenhum feedback registrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINAÇÃO --}}
    @if(method_exists($feedbacks, 'links'))
        <div style="display:flex;justify-content:center;margin-top:16px;">
            {{ $feedbacks->onEachSide(1)->links() }}
        </div>
    @endif
</div>

{{-- SCRIPT DE BUSCA --}}
<script>
    const input = document.querySelector('.search-input');
    input.addEventListener('input', function () {
        const term = this.value.toLowerCase();
        document.querySelectorAll('tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
        });
    });
</script>

@include('partials.toast')
@endsection
