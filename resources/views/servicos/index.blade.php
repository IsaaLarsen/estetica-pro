<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serviços - Estética PRO</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9fafb;
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            display: flex;
            flex-direction: column;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }

        .sidebar-header {
            padding: 24px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h1 {
            font-size: 24px;
            font-weight: 700;
        }

        .sidebar-nav {
            flex: 1;
            padding: 20px 16px;
            display: flex;
            flex-direction: column;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            color: white;
            font-weight: 500;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-item.active {
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .nav-item i {
            width: 24px;
            margin-right: 12px;
            font-size: 18px;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        /* Topbar */
        .topbar {
            height: 70px;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 12px;
        }

        .user-details h3 {
            font-size: 16px;
            font-weight: 600;
        }

        .user-details p {
            font-size: 13px;
            color: var(--text-light);
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            position: relative;
        }

        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            margin-left: 16px;
            color: var(--text-light);
            font-size: 18px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background-color: #f3f4f6;
            color: var(--primary);
        }

        .settings-menu {
            position: absolute;
            top: 50px;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            width: 200px;
            padding: 8px 0;
            z-index: 100;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .settings-menu.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            background: none;
            border: none;
            font-family: inherit;
            font-size: inherit;
            text-align: left;
        }

        .menu-item:hover {
            background-color: #f9fafb;
        }

        .menu-item i {
            margin-right: 12px;
            width: 18px;
            color: var(--text-light);
        }

        .menu-divider {
            height: 1px;
            background-color: #f3f4f6;
            margin: 4px 0;
        }

        /* Content */
        .content {
            padding: 24px;
            flex: 1;
        }

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

        .header-actions {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
        }

        .search-input {
            padding: 12px 16px 12px 40px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 14px;
            width: 250px;
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

        .btn-icon {
            margin-right: 8px;
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
            position: relative;
        }

        tbody tr {
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.3s ease;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        tbody tr:hover {
            background: #f9fafb;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        td {
            padding: 16px;
        }

        .service-info {
            display: flex;
            align-items: center;
        }

        .service-details h3 {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .service-details p {
            font-size: 14px;
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
            background: #fef3c7;
            color: var(--warning);
        }

        /* Ações */
        .actions {
            display: flex;
            gap: 8px;
        }

        .action-btn-table {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
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

        .action-delete {
            color: var(--danger);
            background: #fee2e2;
        }

        .action-delete:hover {
            background: var(--danger);
            color: white;
        }

        /* Paginação */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 30px;
        }

        .pagination-item {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            color: var(--text);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .pagination-item:hover,
        .pagination-item.active {
            background: var(--primary);
            color: white;
        }

        /* Mensagens */
        .alert {
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background-color: #fef3c7;
            color: #7c2d12;
            border: 1px solid #fde68a;
        }

        /* Responsivo */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }

            .sidebar-header h1,
            .nav-item span {
                display: none;
            }

            .nav-item {
                justify-content: center;
                padding: 16px;
            }

            .nav-item i {
                margin-right: 0;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .header-actions {
                width: 100%;
                justify-content: space-between;
            }

            .search-input {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h1>Estética PRO</h1>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i><span>Dashboard</span>
            </a>
            <a href="{{ route('funcionarios.index') }}"
                class="nav-item {{ request()->routeIs('funcionarios.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i><span>Funcionários</span>
            </a>
            <a href="{{ route('servicos.index') }}"
                class="nav-item {{ request()->routeIs('servicos.*') ? 'active' : '' }}">
                <i class="fas fa-scissors"></i><span>Serviços</span>
            </a>
            <a href="{{ route('agenda.index') }}" class="nav-item {{ request()->routeIs('agenda.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i><span>Agenda</span>
            </a>
            <a href="{{ route('comissoes.index') }}"
                class="nav-item {{ request()->routeIs('comissoes.*') ? 'active' : '' }}">
                    <i class="fas fa-hand-holding-usd"></i><span>Comissões</span>
            </a>
            <a href="{{ route('clientes.index') }}" class="nav-item {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                <i class="fas fa-user"></i><span>Clientes</span>
            </a>
            <a href="{{ route('cargos.index') }}" class="nav-item {{ request()->routeIs('cargos.*') ? 'active' : '' }}">
                <i class="fas fa-briefcase"></i><span>Cargos</span>
            </a>
        </nav>

        <div class="menu-divider"></div>
        <form method="GET" action="{{ route('logout') }}" style="width:100%;">
            <button type="submit" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sair</span>
            </button>
        </form>
    </aside>

    <!-- Conteúdo principal -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div class="user-info">
                <div class="user-avatar">EP</div>
                <div class="user-details">
                    <h3>{{ $usuario->nome }}</h3>
                    <p>Administrador</p>
                </div>
            </div>

            <div class="topbar-actions">
                <button class="action-btn"><i class="fas fa-bell"></i></button>
                <button class="action-btn" id="settingsBtn"><i class="fas fa-cog"></i></button>

                <div class="settings-menu" id="settingsMenu">
                    <div class="menu-item" id="editPasswordBtn">
                        <i class="fas fa-key"></i>
                        <span>Editar Senha</span>
                    </div>
                    <div class="menu-divider"></div>
                    <form method="POST" action="{{ route('logout') }}" style="width:100%;">
                        @csrf
                        <button type="submit" class="menu-item">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Sair</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="page-header">
                <h1 class="page-title">Serviços</h1>
                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" placeholder="Buscar serviço..." id="searchInput">
                    </div>
                    <a href="{{ route('servicos.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus btn-icon"></i>
                        Novo Serviço
                    </a>
                </div>
            </div>

            <!-- Mensagens de alerta -->
            @if (session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Valor (R$)</th>
                            <th>% Comissão</th>
                            <th>Duração (min)</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($servicos as $s)
                            <tr>
                                <td>
                                    <div class="service-info">
                                        <div class="service-details">
                                            <h3>{{ $s->nome }}</h3>
                                            <p>{{ Str::limit($s->descricao, 30) ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>R$ {{ number_format($s->valor, 2, ',', '.') }}</td>
                                <td>{{ number_format($s->comissao_percent, 2, ',', '.') }}%</td>
                                <td>{{ $s->duracao_minutos }}</td>
                                <td>
                                    <span class="status-badge {{ $s->ativo ? 'status-active' : 'status-inactive' }}">
                                        {{ $s->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <!-- Editar -->
                                        <a href="{{ route('servicos.edit', $s->id) }}" class="action-btn-table action-edit"
                                            title="Editar">
                                            <i class="fas fa-pen-to-square"></i>
                                        </a>

                                        <!-- Excluir -->
                                        <form action="{{ route('servicos.destroy', $s->id) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn-table action-delete" title="Excluir"
                                                onclick="return confirm('Tem certeza que deseja excluir este serviço?')">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center; padding: 32px; color: var(--text-light);">
                                    <i class="fas fa-scissors"
                                        style="font-size:28px; color:#e5e7eb; display:block; margin-bottom:10px;"></i>
                                    Nenhum serviço cadastrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginação: só renderiza se $servicos for um paginator --}}
            @if (method_exists($servicos, 'hasPages') && $servicos->hasPages())
                <div class="pagination">
                    {{-- Anterior --}}
                    @if($servicos->onFirstPage())
                        <div class="pagination-item disabled"><i class="fas fa-chevron-left"></i></div>
                    @else
                        <a href="{{ $servicos->previousPageUrl() }}" class="pagination-item">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    @endif

                    {{-- Números de página --}}
                    @for ($i = 1; $i <= $servicos->lastPage(); $i++)
                        @if ($i == $servicos->currentPage())
                            <div class="pagination-item active">{{ $i }}</div>
                        @else
                            <a href="{{ $servicos->url($i) }}" class="pagination-item">{{ $i }}</a>
                        @endif
                    @endfor

                    {{-- Próximo --}}
                    @if($servicos->hasMorePages())
                        <a href="{{ $servicos->nextPageUrl() }}" class="pagination-item">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <div class="pagination-item disabled"><i class="fas fa-chevron-right"></i></div>
                    @endif
                </div>
            @endif

        </div>
    </div>

    <script>
        const settingsBtn = document.getElementById('settingsBtn');
        const settingsMenu = document.getElementById('settingsMenu');

        // Configuração do menu
        settingsBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            settingsMenu.classList.toggle('active');
        });

        document.addEventListener('click', function (e) {
            if (!settingsMenu.contains(e.target) && e.target !== settingsBtn) {
                settingsMenu.classList.remove('active');
            }
        });

        // Busca local
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const term = this.value.toLowerCase();
                document.querySelectorAll('tbody tr').forEach(row => {
                    const text = row.innerText.toLowerCase();
                    row.style.display = text.includes(term) ? '' : 'none';
                });
            });
        }

        // Função para editar senha (exemplo)
        document.getElementById('editPasswordBtn').addEventListener('click', function () {
            alert('Funcionalidade de editar senha será implementada aqui.');
            settingsMenu.classList.remove('active');
        });
    </script>
</body>

</html>
