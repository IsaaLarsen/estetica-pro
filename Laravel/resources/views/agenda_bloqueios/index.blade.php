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

        .page-header{
            display:flex; justify-content:space-between; align-items:center;
            margin-bottom:20px; gap:16px; flex-wrap:wrap;
        }

        .page-title{
            font-size:28px; font-weight:700;
            background:linear-gradient(135deg,var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
            margin:0;
        }

        .header-actions{
            display:flex; gap:12px; align-items:center; flex-wrap:wrap;
        }

        /* Botões */
        .btn{
            display:flex; align-items:center; gap:8px;
            padding:10px 18px; border-radius:12px;
            font-weight:500; border:none; cursor:pointer;
            text-decoration:none; font-size:14px;
        }

        .btn-primary{
            background:linear-gradient(135deg,var(--primary),var(--secondary));
            color:#fff; box-shadow:0 4px 14px rgba(236,72,153,.4);
        }
        .btn-primary:hover{
            transform:translateY(-2px);
            box-shadow:0 6px 20px rgba(236,72,153,.5);
        }

        .btn-secondary{
            background:#f3f4f6;
            color:var(--text);
            border:1px solid #e5e7eb;
        }
        .btn-secondary:hover{
            background:#e5e7eb;
        }

        /* Filtros */
        .filters-card{
            background:#fff;
            border-radius:16px;
            padding:16px 20px;
            box-shadow:0 3px 12px rgba(0,0,0,.04);
            margin-bottom:20px;
        }
        .filters-form{
            display:flex;
            flex-wrap:wrap;
            gap:12px 16px;
            align-items:flex-end;
        }
        .filter-group{
            display:flex;
            flex-direction:column;
            min-width:150px;
        }
        .filter-group label{
            font-size:13px;
            font-weight:500;
            margin-bottom:4px;
            color:var(--text);
        }
        .filter-group input,
        .filter-group select{
            padding:8px 10px;
            border-radius:10px;
            border:1px solid #e5e7eb;
            font-size:13px;
        }
        .filter-group input:focus,
        .filter-group select:focus{
            border-color:var(--primary);
            outline:none;
            box-shadow:0 0 0 2px rgba(236,72,153,.25);
        }

        .filter-actions{
            display:flex;
            gap:8px;
        }

        /* Alertas */
        .alert{
            padding:12px 16px; border-radius:12px;
            margin-bottom:20px; font-size:14px;
        }
        .alert-success{
            background:#ecfdf5; color:#166534;
            border:1px solid #bbf7d0;
        }

        /* Tabela */
        .table-container{
            background:#fff; border-radius:16px; overflow:hidden;
            box-shadow:0 4px 20px rgba(0,0,0,.05); margin-bottom:30px;
            overflow-x:auto;
        }
        table{ width:100%; border-collapse:collapse; min-width:800px; }
        thead{
            background:linear-gradient(135deg,var(--primary),var(--secondary));
            color:#fff;
        }
        th{ padding:16px; text-align:left; font-weight:500; }
        tbody tr{
            border-bottom:1px solid #f3f4f6; transition:background .3s;
        }
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
            width:34px; height:34px; border-radius:8px;
            display:flex; align-items:center; justify-content:center;
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
        .empty-state i{
            font-size:28px; color:#e5e7eb; display:block; margin-bottom:10px;
        }

        /* Paginação wrapper (Laravel links) */
        .pagination-wrapper{
            margin-top:30px;
            display:flex;
            justify-content:center;
        }

        @media (max-width:768px){
            .page-header{ flex-direction:column; align-items:flex-start; }
            .header-actions{ width:100%; justify-content:flex-start; }
            .filters-form{ flex-direction:column; align-items:flex-start; }
            .filter-actions{ width:100%; }
        }
    </style>

    <div class="content">
        {{-- Cabeçalho --}}
        <div class="page-header">
            <h1 class="page-title">Bloqueios de Agenda</h1>
            <div class="header-actions">
                {{-- Voltar para agenda --}}
                <a href="{{ route('agenda.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar para agenda
                </a>

                {{-- Novo bloqueio --}}
                <a href="{{ route('agenda.bloqueios.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Novo Bloqueio
                </a>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="filters-card">
            <form method="GET" action="{{ route('agenda.bloqueios.index') }}" class="filters-form">
                <div class="filter-group">
                    <label for="data_inicio">Data início</label>
                    <input type="date"
                           id="data_inicio"
                           name="data_inicio"
                           value="{{ request('data_inicio') }}">
                </div>

                <div class="filter-group">
                    <label for="data_fim">Data fim</label>
                    <input type="date"
                           id="data_fim"
                           name="data_fim"
                           value="{{ request('data_fim') }}">
                </div>

                <div class="filter-group" style="min-width:200px;">
                    <label for="funcionario_id">Profissional</label>
                    <select name="funcionario_id" id="funcionario_id">
                        <option value="">Todos</option>
                        @foreach($funcionarios as $f)
                            <option value="{{ $f->id }}"
                                {{ (string)request('funcionario_id') === (string)$f->id ? 'selected' : '' }}>
                                {{ $f->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>

                    <a href="{{ route('agenda.bloqueios.index') }}" class="btn btn-secondary">
                        <i class="fas fa-eraser"></i> Limpar
                    </a>
                </div>
            </form>
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
                                        @csrf
                                        @method('DELETE')
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
                {{ $bloqueios->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    @include('partials.toast')
@endsection
