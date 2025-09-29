<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estética PRO</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @stack('styles') {{-- mantenha seus estilos grandes via stack --}}
</head>

<body>
    @php
        $u = session('usuario');
        $nomeUsuario = 'Convidado';
        if ($u) {
            if (is_object($u)) {
                $nomeUsuario = $u->nome ?? ($u->name ?? 'Usuário');
            } elseif (is_array($u)) {
                $nomeUsuario = $u['nome'] ?? ($u['name'] ?? 'Usuário');
            }
        }
    @endphp

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
            <a href="{{ route('cargos.index') }}" class="nav-item {{ request()->routeIs('cargos.*') ? 'active' : '' }}">
                <i class="fas fa-briefcase"></i><span>Cargos</span>
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
            <a href="{{ route('clientes.index') }}"
                class="nav-item {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                <i class="fas fa-user"></i><span>Clientes</span>
            </a>
        </nav>

        <!-- Botão logout no rodapé -->
        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}" style="width:100%;">
                @csrf
                <button type="submit" class="nav-item"
                    style="width:100%; background:none; border:none; color:white; text-align:left; cursor:pointer;">
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
                    <h3>{{ $nomeUsuario }}</h3>
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
            @yield('content')
        </div>
    </div>

    <script>
        // Abrir/fechar menu configurações
        const settingsBtn = document.getElementById('settingsBtn');
        const settingsMenu = document.getElementById('settingsMenu');
        settingsBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            settingsMenu?.classList.toggle('active');
        });
        document.addEventListener('click', function (e) {
            if (settingsMenu && !settingsMenu.contains(e.target) && e.target !== settingsBtn) {
                settingsMenu.classList.remove('active');
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
