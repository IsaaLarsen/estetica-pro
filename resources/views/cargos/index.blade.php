@extends('layouts.app')

@section('title', 'Cargos - Estética PRO')

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

        /* IMPORTANTE: 11px aqui como você pediu */
        .content { padding: 11px; flex: 1; }

        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 16px; }
        .page-title { font-size: 28px; font-weight: 700; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .header-actions { display: flex; gap: 16px; flex-wrap: wrap; }
        .search-box { position: relative; }
        .search-input { padding: 12px 16px 12px 40px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 14px; width: 250px; transition: all 0.3s ease; }
        .search-input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.2); }
        .search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-light); }

        .btn { display: flex; align-items: center; padding: 12px 20px; border-radius: 12px; font-weight: 500; cursor: pointer; transition: all 0.3s ease; border: none; text-decoration: none; }
        .btn-primary { background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; box-shadow: 0 4px 14px rgba(236, 72, 153, 0.4); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(236, 72, 153, 0.5); }
        .btn-icon { margin-right: 8px; }

        /* Tabela */
        .table-container { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); margin-bottom: 30px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        thead { background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; }
        th { padding: 16px; text-align: left; font-weight: 500; position: relative; }
        tbody tr { border-bottom: 1px solid #f3f4f6; transition: all 0.3s ease; cursor: pointer; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #f9fafb; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05); }
        td { padding: 16px; }

        .cargo-info { display: flex; align-items: center; }
        .cargo-details h3 { font-size: 16px; font-weight: 500; margin-bottom: 4px; }
        .cargo-details p { font-size: 14px; color: var(--text-light); }

        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; display: inline-block; }
        .status-active { background: #ecfdf5; color: var(--success); }
        .status-inactive { background: #fef3c7; color: var(--warning); }

        /* Ações */
        .actions { display: flex; gap: 8px; }
        .action-btn-table { width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; background: none; border: none; }
        .action-edit { color: var(--primary); background: var(--primary-light); }
        .action-edit:hover { background: var(--primary); color: white; }
        .action-delete { color: var(--danger); background: #fee2e2; }
        .action-delete:hover { background: var(--danger); color: white; }

        /* Mensagens */
        .alert { padding: 16px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; }
        .alert-success { background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background-color: #fef3c7; color: #7c2d12; border: 1px solid #fde68a; }

        .hint-text { font-size: 14px; color: var(--text-light); margin-top: 16px; display: block; }

        /* Responsivo */
        @media (max-width: 768px) {
            .page-header { flex-direction: column; align-items: flex-start; gap: 16px; }
            .header-actions { width: 100%; justify-content: space-between; }
            .search-input { width: 100%; }
        }
    </style>

    <div class="content">
        <div class="page-header">
            <h1 class="page-title">Cargos</h1>
            <div class="header-actions">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Buscar cargo..." id="searchInput">
                </div>
                <a href="{{ route('cargos.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus btn-icon"></i>
                    Novo Cargo
                </a>
            </div>
        </div>

        {{-- Mensagens --}}
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Cargo</th>
                        <th>Descrição</th>
                        <th>Status</th>
                        <th style="width:120px;">Ações</th>
                    </tr>
                </thead>
                <tbody id="cargosTableBody">
                    @forelse($cargos as $cargo)
                        <tr class="cargo-row"
                            data-href="{{ route('cargos.funcionarios', $cargo) }}"
                            title="Duplo clique para ver os funcionários deste cargo">
                            <td>
                                <div class="cargo-info">
                                    <div class="cargo-details">
                                        <h3>{{ $cargo->nome }}</h3>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $cargo->descricao }}</td>
                            <td>
                                <span class="status-badge {{ $cargo->ativo ? 'status-active' : 'status-inactive' }}">
                                    {{ $cargo->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('cargos.edit', $cargo) }}" class="action-btn-table action-edit" title="Editar">
                                        <i class="fas fa-pen-to-square"></i>
                                    </a>
                                    <form method="POST" action="{{ route('cargos.destroy', $cargo) }}"
                                          onsubmit="return confirm('Tem certeza que deseja excluir este cargo?');">
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
                            <td colspan="4" style="text-align:center; padding: 32px; color: var(--text-light);">
                                <i class="fas fa-briefcase" style="font-size:28px; color:#e5e7eb; display:block; margin-bottom:10px;"></i>
                                Nenhum cargo cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <span class="hint-text">
            <i class="fas fa-lightbulb"></i> Dica: dê <strong>duplo clique</strong> em um cargo para ver os funcionários.
        </span>
    </div>

    <script>
        // (Opcional) integra com o dropdown do layout se existir
        const settingsBtn = document.getElementById('settingsBtn');
        const settingsMenu = document.getElementById('settingsMenu');
        if (settingsBtn && settingsMenu) {
            settingsBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                settingsMenu.classList.toggle('active');
            });
            document.addEventListener('click', function (e) {
                if (!settingsMenu.contains(e.target) && e.target !== settingsBtn) {
                    settingsMenu.classList.remove('active');
                }
            });
        }

        // Busca na tabela
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const term = this.value.toLowerCase();
                document.querySelectorAll('.cargo-row').forEach(row => {
                    const text = row.innerText.toLowerCase();
                    row.style.display = text.includes(term) ? '' : 'none';
                });
            });
        }

        // Duplo clique: navegar para a rota de funcionários do cargo
        document.querySelectorAll('.cargo-row').forEach(function(row) {
            row.addEventListener('dblclick', function() {
                const href = this.getAttribute('data-href');
                if (href) window.location.href = href;
            });
        });
    </script>

    @include('partials.change_password_modal')
    @include('partials.toast')
@endsection
