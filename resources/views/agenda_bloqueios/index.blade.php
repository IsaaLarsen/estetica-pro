@extends('layouts.app')

@section('title', 'Bloqueios de Agenda - Estética PRO')

@section('content')
    {{-- Estilos ESPECÍFICOS desta página --}}
    <style>
        :root{
            --primary:#ec4899; --primary-dark:#db2777; --primary-light:#fbcfe8;
            --secondary:#7e22ce; --text:#1f2937; --text-light:#6b7280;
            --success:#10b981; --warning:#f59e0b; --danger:#ef4444;
        }

        .content{ padding:11px; }

        .page-header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; gap:16px; flex-wrap:wrap; }
        .page-title{
            font-size:28px; font-weight:700;
            background:linear-gradient(135deg,var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
            margin:0;
        }
        .header-actions{ display:flex; gap:16px; align-items:center; flex-wrap:wrap; }

        /* Botões */
        .btn{ display:flex; align-items:center; gap:8px; padding:12px 20px; border-radius:12px; font-weight:500; border:none; cursor:pointer; text-decoration:none; }
        .btn-primary{ background:linear-gradient(135deg,var(--primary),var(--secondary)); color:#fff; box-shadow:0 4px 14px rgba(236,72,153,.4); }
        .btn-primary:hover{ transform:translateY(-2px); box-shadow:0 6px 20px rgba(236,72,153,.5); }

        /* Alertas */
        .alert{ padding:12px 16px; border-radius:12px; margin-bottom:20px; font-size:14px; }
        .alert-success{ background:#ecfdf5; color:#166534; border:1px solid #bbf7d0; }

        /* Tabela */
        .table-container{ background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.05); margin-bottom:30px; overflow-x:auto; }
        table{ width:100%; border-collapse:collapse; min-width:800px; }
        thead{ background:linear-gradient(135deg,var(--primary),var(--secondary)); color:#fff; }
        th{ padding:16px; text-align:left; font-weight:500; }
        tbody tr{ border-bottom:1px solid #f3f4f6; transition:background .3s; }
        tbody tr:last-child{ border-bottom:none; }
        tbody tr:hover{ background:#f9fafb; }
        td{ padding:16px; }

        /* Badges para profissionais */
        .badge{
            display:inline-block; background:#f3f4f6; color:#111827;
            padding:6px 12px; border-radius:20px; font-size:12px; font-weight:500;
            margin:0 4px 4px 0;
        }
        .badge-secondary{ background:#e5e7eb; color:#374151; }

        /* Ações */
        .actions{ display:flex; gap:8px; }
        .action-btn-table{
            width:34px; height:34px; border-radius:8px; display:flex; align-items:center; justify-content:center;
            cursor:pointer; transition:.2s; background:none; border:none;
        }
        .action-edit{ color:var(--primary); background:var(--primary-light); }
        .action-edit:hover{ background:var(--primary); color:#fff; }
        .action-delete{ color:var(--danger); background:#fee2e2; }
        .action-delete:hover{ background:var(--danger); color:#fff; }

        /* Estados vazios */
        .empty-state{
            text-align:center; padding:32px; color:var(--text-light);
            font-style:italic;
        }
        .empty-state i{ font-size:28px; color:#e5e7eb; display:block; margin-bottom:10px; }

        /* Paginação wrapper (Laravel links) */
        .pagination-wrapper{
            margin-top:30px;
            display:flex;
            justify-content:center;
        }

        @media (max-width:768px){
            .page-header{ flex-direction:column; align-items:flex-start; }
            .header-actions{ width:100%; justify-content:space-between; }
        }
    </style>

    <div class="content">
        {{-- Cabeçalho --}}
        <div class="page-header">
            <h1 class="page-title">Bloqueios de Agenda</h1>
            <div class="header-actions">
                <a href="{{ route('agenda.bloqueios.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Novo Bloqueio
                </a>
            </div>
        </div>

        {{-- Alertas --}}
        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Tabela --}}
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Profissionais</th>
                        <th>Início</th>
                        <th>Fim</th>
                        <th>Motivo</th>
                        <th style="width:140px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bloqueios as $b)
                    <tr>
                        <td>
                            @if($b->aplicar_todos)
                                <span class="badge badge-secondary">
                                    <i class="fas fa-users"></i> Todos os profissionais
                                </span>
                            @else
                                @if($b->funcionarios->isEmpty())
                                    —
                                @else
                                    @foreach($b->funcionarios as $func)
                                        <span class="badge">
                                            {{ $func->nome }}
                                        </span>
                                    @endforeach
                                @endif
                            @endif
                        </td>
                        <td>{{ $b->inicio->format('d/m/Y H:i') }}</td>
                        <td>{{ $b->fim->format('d/m/Y H:i') }}</td>
                        <td>{{ $b->motivo ?: '—' }}</td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('agenda.bloqueios.edit', $b->id) }}"
                                   class="action-btn-table action-edit" title="Editar">
                                    <i class="fas fa-pen-to-square"></i>
                                </a>
                                <form method="POST"
                                      action="{{ route('agenda.bloqueios.destroy',$b) }}"
                                      onsubmit="return confirm('Remover bloqueio?')"
                                      style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn-table action-delete" title="Excluir">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            Nenhum bloqueio cadastrado.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginação (real, usando Laravel) --}}
        @if($bloqueios->hasPages())
            <div class="pagination-wrapper">
                {{ $bloqueios->links() }}
            </div>
        @endif
    </div>

    @include('partials.toast')
@endsection
