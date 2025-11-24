@extends('layouts.app')

@section('title', 'Funcionários do cargo - Estética PRO')

@section('content')
    <style>
        :root {
            --primary: #ec4899;
            --primary-dark: #db2777;
            --primary-light: #fbcfe8;
            --secondary: #7e22ce;
            --text: #1f2937;
            --text-light: #6b7280;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --sidebar-width: 260px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        .content { padding: 11px; flex: 1; }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 16px;
        }
        .page-title {
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .page-subtitle {
            font-size: 14px;
            color: var(--text-light);
            margin-top: 4px;
        }
        .header-actions {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .btn {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(236, 72, 153, 0.4);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(236, 72, 153, 0.5);
        }
        .btn-secondary {
            background: #f3f4f6;
            color: var(--text);
            border: 2px solid #e5e7eb;
        }
        .btn-secondary:hover {
            background: #e5e7eb;
        }
        .btn-icon { margin-right: 8px; }

        /* Busca */
        .search-box { position: relative; }
        .search-input {
            padding: 12px 16px 12px 40px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 14px;
            width: 260px;
            transition: all 0.3s ease;
        }
        .search-input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.2);
        }
        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }

        /* Tabela */
        .table-container {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        thead {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }
        th {
            padding: 16px;
            text-align: left;
            font-weight: 500;
        }
        tbody tr {
            border-bottom: 1px solid #f3f4f6;
            transition: all 0.2s ease;
        }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover {
            background: #f9fafb;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.03);
        }
        td { padding: 14px 16px; }

        .func-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .func-name {
            font-size: 15px;
            font-weight: 600;
            color: var(--text);
        }
        .func-meta {
            font-size: 12px;
            color: var(--text-light);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        .status-active {
            background: #ecfdf5;
            color: var(--success);
        }
        .status-inactive {
            background: #f3f4f6;
            color: var(--text-light);
        }

        /* Ações */
        .actions { display: flex; gap: 8px; }
        .action-btn-table {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            background: none;
            border: none;
        }
        .action-edit {
            color: var(--primary);
            background: var(--primary-light);
        }
        .action-edit:hover {
            background: var(--primary);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 32px;
            color: var(--text-light);
        }
        .empty-state i {
            font-size: 28px;
            color: #e5e7eb;
            display: block;
            margin-bottom: 10px;
        }

        .hint-text {
            font-size: 14px;
            color: var(--text-light);
            margin-top: 8px;
            display: block;
        }

        @media (max-width: 768px) {
            .page-header { 
                flex-direction: column; 
                align-items: flex-start; 
            }
            .header-actions { width: 100%; justify-content: space-between; }
            .search-input { width: 100%; }
        }
    </style>

    <div class="content">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    Funcionários do cargo
                </h1>
                <div class="page-subtitle">
                    Cargo: <strong>{{ $cargo->nome }}</strong>  
                    @if(!$funcionarios->isEmpty())
                        • {{ $funcionarios->count() }} funcionário(s) vinculado(s)
                    @endif
                </div>
            </div>

            <div class="header-actions">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Buscar funcionário..." id="searchFuncionario">
                </div>

                <a href="{{ route('cargos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left btn-icon"></i>
                    Voltar aos cargos
                </a>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Funcionário</th>
                        <th>CPF</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <th>Status</th>
                        <th style="width:120px;">Ações</th>
                    </tr>
                </thead>
                <tbody id="funcionariosTableBody">
                    @forelse($funcionarios as $f)
                        <tr class="func-row">
                            <td>
                                <div class="func-info">
                                    <span class="func-name">{{ $f->nome }}</span>
                                    @if($f->email)
                                        <span class="func-meta">{{ $f->email }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $f->cpf ?: '—' }}</td>
                            <td>{{ $f->email ?: '—' }}</td>
                            <td>{{ $f->telefone ?: '—' }}</td>
                            <td>
                                <span class="status-badge {{ $f->ativo ? 'status-active' : 'status-inactive' }}">
                                    {{ $f->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="action-btn-table action-edit"
                                       href="{{ route('funcionarios.edit', $f->id) }}"
                                       title="Editar funcionário">
                                        <i class="fas fa-pen-to-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="fas fa-user-slash"></i>
                                Nenhum funcionário cadastrado com este cargo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <span class="hint-text">
            <i class="fas fa-lightbulb"></i>
            Dica: use o botão <strong>Editar</strong> para alterar dados do funcionário ou trocar o cargo.
        </span>
    </div>

    <script>
        // Filtro de busca por funcionário
        const input = document.getElementById('searchFuncionario');
        if (input) {
            input.addEventListener('input', function () {
                const term = this.value.toLowerCase();
                document.querySelectorAll('.func-row').forEach(row => {
                    const text = row.innerText.toLowerCase();
                    row.style.display = text.includes(term) ? '' : 'none';
                });
            });
        }
    </script>

    @include('partials.change_password_modal')
    @include('partials.toast')
@endsection
