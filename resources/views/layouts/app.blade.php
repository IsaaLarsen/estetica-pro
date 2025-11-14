<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Estética PRO')</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ec4899;
            --primary-dark: #db2777;
            --primary-light: #fbcfe8;
            --secondary: #7e22ce;
            --text: #1f2937;
            --text-light: #6b7280;
            --sidebar-width: 260px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9fafb;
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }

        /* Sidebar Fixa */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            display: flex;
            flex-direction: column;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.1);
            z-index: 10;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar-header { padding: 24px; text-align: center; border-bottom: 1px solid rgba(255,255,255,.1); }
        .sidebar-header h1 { font-size: 24px; font-weight: 700; }
        .sidebar-nav { flex: 1; padding: 20px 16px; display: flex; flex-direction: column; }
        .nav-item {
            display: flex; align-items: center; padding: 14px 16px; border-radius: 12px;
            margin-bottom: 8px; transition: all .3s ease; text-decoration: none; color: white; font-weight: 500;
        }
        .nav-item:hover { background: rgba(255,255,255,.1); }
        .nav-item.active { background: rgba(255,255,255,.15); box-shadow: 0 4px 12px rgba(0,0,0,.1); }
        .nav-item i { width: 24px; margin-right: 12px; font-size: 18px; }

        .sidebar-footer { padding: 16px; border-top: 1px solid rgba(255,255,255,.1); }
        .logout-btn {
            display: flex; align-items: center; width: 100%; padding: 14px 16px; border-radius: 12px;
            background: rgba(255,255,255,.1); color: white; border: none; font-family: 'Poppins', sans-serif;
            font-weight: 500; cursor: pointer; transition: all .3s ease; text-align: left;
        }
        .logout-btn:hover { background: rgba(255,255,255,.15); transform: translateY(-2px); }
        .logout-btn i { width: 24px; margin-right: 12px; font-size: 18px; }

        /* Conteúdo Principal */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; margin-left: var(--sidebar-width); width: calc(100% - var(--sidebar-width)); }

        /* Topbar */
        .topbar {
            height: 70px; background: white; box-shadow: 0 2px 10px rgba(0,0,0,.05);
            display: flex; align-items: center; justify-content: space-between; padding: 0 24px;
            position: sticky; top: 0; z-index: 5;
        }
        .user-info { display: flex; align-items: center; }
        .user-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; margin-right: 12px;
        }
        .user-details h3 { font-size: 16px; font-weight: 600; }
        .user-details p { font-size: 13px; color: var(--text-light); }

        .topbar-actions { display: flex; align-items: center; position: relative; }
        .action-btn {
            background: none; border: none; cursor: pointer; margin-left: 16px; color: var(--text-light);
            font-size: 18px; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all .3s ease;
        }
        .action-btn:hover { background-color: #f3f4f6; color: var(--primary); }
        .settings-menu {
            position: absolute; top: 50px; right: 0; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,.15);
            width: 200px; padding: 8px 0; z-index: 100; opacity: 0; visibility: hidden; transform: translateY(-10px); transition: all .3s ease;
        }
        .settings-menu.active { opacity: 1; visibility: visible; transform: translateY(0); }
        .menu-item {
            display: flex; align-items: center; padding: 12px 16px; cursor: pointer; transition: all .3s ease;
            width: 100%; background: none; border: none; font-family: inherit; font-size: inherit; text-align: left;
        }
        .menu-item:hover { background-color: #f9fafb; }
        .menu-item i { margin-right: 12px; width: 18px; color: var(--text-light); }
        .menu-divider { height: 1px; background-color: #f3f4f6; margin: 4px 0; }

        /* Área de Conteúdo */
        .content { padding: 24px; flex: 1; }

        /* Responsividade */
        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .sidebar-header h1, .nav-item span, .logout-btn span { display: none; }
            .nav-item, .logout-btn { justify-content: center; padding: 16px; }
            .nav-item i, .logout-btn i { margin-right: 0; }
            .main-content { margin-left: 70px; width: calc(100% - 70px); }
        }

        /* Selects & Select2 tema */
        select { appearance: none; -webkit-appearance: none; -moz-appearance: none; background-color: white; }
        select:hover, select:focus {
            background-color: white; outline: none;
            box-shadow: 0 0 0 3px rgba(236,72,153,.2); border-color: var(--primary);
        }
        .select2-container--default .select2-selection--single{
            height:48px;border:2px solid #f3d1e5;border-radius:12px;display:flex;align-items:center;padding:6px 12px;background:#fff;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered{
            line-height:32px;font-family:'Poppins',sans-serif;color:var(--text);
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow{height:100%;right:10px;}
        .select2-container--default .select2-selection--single:focus,
        .select2-container--default.select2-container--open .select2-selection--single{
            outline:none;border-color:var(--primary);box-shadow:0 0 0 4px rgba(236,72,153,.15);
        }
        .select2-dropdown{border:2px solid #f3d1e5;border-radius:12px;overflow:hidden;box-shadow:0 10px 24px rgba(0,0,0,.08);}
        .select2-results__option--highlighted{background-color:#fde7f3 !important;color:var(--text) !important;}
        .select2-results__option[aria-selected=true]{background-color:#f9e0ef !important;color:var(--text) !important;}
    </style>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
</head>

<body>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h1>Estética PRO</h1>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i><span>Dashboard</span>
            </a>
            <a href="{{ route('agenda.index') }}" class="nav-item {{ request()->routeIs('agenda.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i><span>Agenda</span>
            </a>
            <a href="{{ route('comissoes.index') }}" class="nav-item {{ request()->routeIs('comissoes.*') ? 'active' : '' }}">
                <i class="fas fa-hand-holding-usd"></i><span>Comissões</span>
            </a>
            <a href="{{ route('funcionarios.index') }}" class="nav-item {{ request()->routeIs('funcionarios.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i><span>Funcionários</span>
            </a>
            <a href="{{ route('clientes.index') }}" class="nav-item {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                <i class="fas fa-user"></i><span>Clientes</span>
            </a>
            <a href="{{ route('servicos.index') }}" class="nav-item {{ request()->routeIs('servicos.*') ? 'active' : '' }}">
                <i class="fas fa-scissors"></i><span>Serviços</span>
            </a>
            <a href="{{ route('cargos.index') }}" class="nav-item {{ request()->routeIs('cargos.*') ? 'active' : '' }}">
                <i class="fas fa-briefcase"></i><span>Cargos</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <form method="GET" action="{{ route('logout') }}" style="width:100%;">
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Sair</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Conteúdo principal -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div class="user-info">
                <div class="user-avatar">EP</div>
                <div class="user-details">
                    <h3>{{ $usuario->nome ?? 'Usuário' }}</h3>

                    @php
                        $roleRaw = strtolower($usuario->role ?? $usuario->tipo ?? '');
                        $papelExibicao = match ($roleRaw) {
                            'admin'       => 'Administrador',
                            'funcionario' => 'Funcionário',
                            default       => ($roleRaw ? ucfirst($roleRaw) : '—'),
                        };
                    @endphp
                    <p>{{ $papelExibicao }}</p>
                </div>
            </div>

            <div class="topbar-actions">
                <button class="action-btn"><i class="fas fa-bell"></i></button>
                <button class="action-btn" id="settingsBtn"><i class="fas fa-cog"></i></button>

                <!-- Menu de Configurações -->
                <div class="settings-menu" id="settingsMenu">
                    <div class="menu-item" id="editPasswordBtn">
                        <i class="fas fa-key"></i>
                        <span>Editar Senha</span>
                    </div>
                    <div class="menu-divider"></div>
                    <form method="GET" action="{{ route('logout') }}" style="width:100%;">
                        <button type="submit" class="menu-item">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Sair</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Área de Conteúdo -->
        <div class="content">
            @yield('content')
        </div>
    </div>

    <script>
        // Abrir/fechar menu configurações
        const settingsBtn = document.getElementById('settingsBtn');
        const settingsMenu = document.getElementById('settingsMenu');
        settingsBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            settingsMenu.classList.toggle('active');
        });
        document.addEventListener('click', function (e) {
            if (!settingsMenu.contains(e.target) && e.target !== settingsBtn) {
                settingsMenu.classList.remove('active');
            }
        });
    </script>

    @yield('scripts')
    @include('partials.change_password_modal')
    @include('partials.toast')
</body>
</html>
