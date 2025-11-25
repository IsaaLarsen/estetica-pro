<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Estética PRO')</title>

    <!-- Favicon (logo em alta resolução) -->
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('image/logoEP.png') }}">
    <link rel="shortcut icon" type="image/png" sizes="64x64" href="{{ asset('image/logoEP.png') }}">

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
            --sidebar-width: 260px;
            --base-font-size: 14px;
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
            font-size: var(--base-font-size);
            line-height: 1.5;
        }

        /* ===== ACESSIBILIDADE GERAL ===== */

        /* Link de pular direto pro conteúdo */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 12px;
            background: #111827;
            color: #fff;
            padding: 8px 14px;
            border-radius: 999px;
            z-index: 9999;
            text-decoration: none;
            font-size: 13px;
            transition: top .2s ease;
        }

        .skip-link:focus {
            top: 12px;
        }

        /* Conteúdo apenas para leitores de tela */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        /* Foco visível em elementos interativos */
        a:focus-visible,
        button:focus-visible,
        input:focus-visible,
        select:focus-visible,
        textarea:focus-visible {
            outline: 2px solid #ec4899;
            outline-offset: 2px;
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

        .sidebar-header {
            padding: 24px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, .1);
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
            transition: all .3s ease;
            text-decoration: none;
            color: white;
            font-weight: 500;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, .1);
        }

        .nav-item.active {
            background: rgba(255, 255, 255, .15);
            box-shadow: 0 4px 12px rgba(0, 0, 0, .1);
        }

        .nav-item i {
            width: 24px;
            margin-right: 12px;
            font-size: 18px;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid rgba(255, 255, 255, .1);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 14px 16px;
            border-radius: 12px;
            background: rgba(255, 255, 255, .1);
            color: white;
            border: none;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all .3s ease;
            text-align: left;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, .15);
            transform: translateY(-2px);
        }

        .logout-btn i {
            width: 24px;
            margin-right: 12px;
            font-size: 18px;
        }

        /* Conteúdo Principal */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
        }

        /* Topbar */
        .topbar {
            height: 70px;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            position: sticky;
            top: 0;
            z-index: 5;
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
            transition: all .3s ease;
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
            box-shadow: 0 10px 25px rgba(0, 0, 0, .15);
            width: 220px;
            padding: 8px 0;
            z-index: 100;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all .3s ease;
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
            transition: all .3s ease;
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

        /* Área de Conteúdo */
        .content {
            padding: 24px;
            flex: 1;
        }

        /* Responsividade (MOBILE) */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }

            /* some o header pra não ficar espaço vazio */
            .sidebar-header {
                display: none;
                padding: 0;
                border-bottom: none;
            }

            /* ajusta padding pra ícone ficar logo no topo */
            .sidebar-nav {
                padding: 16px 8px;
            }

            .sidebar-header h1,
            .nav-item span,
            .logout-btn span {
                display: none;
            }

            .nav-item,
            .logout-btn {
                justify-content: center;
                padding: 16px 0;
            }

            .nav-item i,
            .logout-btn i {
                margin-right: 0;
            }

            .main-content {
                margin-left: 70px;
                width: calc(100% - 70px);
            }
        }

        /* ====== SELECTS (filtros “pill”) ====== */
        select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-color: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 999px;
            padding: 8px 18px;
            font-size: 14px;
            color: var(--text);
            line-height: 1.3;
            width: auto;
            max-width: 100%;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(15, 23, 42, .04);
            transition: all .2s ease;
        }

        select:hover {
            box-shadow: 0 6px 18px rgba(15, 23, 42, .06);
        }

        select:focus {
            background-color: #fff;
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(236, 72, 153, .22);
        }

        select option {
            padding: 10px 12px;
            background: #ffffff;
            color: var(--text);
        }

        select option:hover {
            background: #fde7f3 !important;
            color: var(--primary-dark) !important;
        }

        select option:checked {
            background: #f9d8eb !important;
            color: var(--primary-dark) !important;
        }

        .select2-container--default .select2-selection--single {
            height: 46px;
            border: 2px solid #f3d1e5;
            border-radius: 999px;
            display: flex;
            align-items: center;
            padding: 6px 12px;
            background: #fff;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 32px;
            font-family: 'Poppins', sans-serif;
            color: var(--text);
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
            right: 10px;
        }

        .select2-container--default .select2-selection--single:focus,
        .select2-container--default.select2-container--open .select2-selection--single {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(236, 72, 153, .15);
        }

        .select2-dropdown {
            border: 2px solid #f3d1e5;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 24px rgba(0, 0, 0, .08);
        }

        .select2-results__option--highlighted {
            background-color: #fde7f3 !important;
            color: var(--text) !important;
        }

        .select2-results__option[aria-selected=true] {
            background-color: #f9e0ef !important;
            color: var(--text) !important;
        }

        /* Animação do modal de confirmação */
        @keyframes pop {
            0% {
                transform: scale(.85);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
</head>

<body>
    <!-- Link de pular direto pro conteúdo principal -->
    <a href="#main-content" class="skip-link">Ir direto para o conteúdo</a>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>

    <!-- Sidebar -->
    <aside class="sidebar" role="navigation" aria-label="Menu principal">
        <div class="sidebar-header">
            <h1>Estética PRO</h1>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}"
               class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
               @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                <i class="fas fa-chart-line"></i><span>Dashboard</span>
            </a>

            @php
                $user = session('usuario');
                $isFuncionario = $user && ($user->role === 'funcionario');
            @endphp

            @if($isFuncionario)
                <a href="{{ route('minha.agenda') }}"
                   class="nav-item {{ request()->routeIs('minha.agenda*') ? 'active' : '' }}"
                   @if(request()->routeIs('minha.agenda*')) aria-current="page" @endif>
                    <i class="fas fa-calendar-alt"></i><span>Agenda</span>
                </a>
            @else
                <a href="{{ route('agenda.index') }}"
                   class="nav-item {{ request()->routeIs('agenda.*') ? 'active' : '' }}"
                   @if(request()->routeIs('agenda.*')) aria-current="page" @endif>
                    <i class="fas fa-calendar-alt"></i><span>Agenda</span>
                </a>
            @endif

            @if(!$isFuncionario)
                <a href="{{ route('comissoes.index') }}"
                   class="nav-item {{ request()->routeIs('comissoes.*') ? 'active' : '' }}"
                   @if(request()->routeIs('comissoes.*')) aria-current="page" @endif>
                    <i class="fas fa-hand-holding-usd"></i><span>Comissões</span>
                </a>
            @endif

            @if(!$isFuncionario)
                <a href="{{ route('funcionarios.index') }}"
                   class="nav-item {{ request()->routeIs('funcionarios.*') ? 'active' : '' }}"
                   @if(request()->routeIs('funcionarios.*')) aria-current="page" @endif>
                    <i class="fas fa-users"></i><span>Funcionários</span>
                </a>
            @endif

            @if(!$isFuncionario)
                <a href="{{ route('clientes.index') }}"
                   class="nav-item {{ request()->routeIs('clientes.*') ? 'active' : '' }}"
                   @if(request()->routeIs('clientes.*')) aria-current="page" @endif>
                    <i class="fas fa-user"></i><span>Clientes</span>
                </a>
            @endif

            @if(!$isFuncionario)
                <a href="{{ route('servicos.index') }}"
                   class="nav-item {{ request()->routeIs('servicos.*') ? 'active' : '' }}"
                   @if(request()->routeIs('servicos.*')) aria-current="page" @endif>
                    <i class="fas fa-scissors"></i><span>Serviços</span>
                </a>
            @endif

            @if(!$isFuncionario)
                <a href="{{ route('cargos.index') }}"
                   class="nav-item {{ request()->routeIs('cargos.*') ? 'active' : '' }}"
                   @if(request()->routeIs('cargos.*')) aria-current="page" @endif>
                    <i class="fas fa-briefcase"></i><span>Cargos</span>
                </a>
            @endif

            @if(!$isFuncionario)
                <a href="{{ route('relatorios.index') }}"
                   class="nav-item {{ request()->routeIs('relatorios.*') ? 'active' : '' }}"
                   @if(request()->routeIs('relatorios.*')) aria-current="page" @endif>
                    <i class="fas fa-file-alt"></i><span>Relatórios</span>
                </a>
            @endif

            @if(!$isFuncionario)
                <a href="{{ route('feedbacks.index') }}"
                   class="nav-item {{ request()->routeIs('feedbacks.*') ? 'active' : '' }}"
                   @if(request()->routeIs('feedbacks.*')) aria-current="page" @endif>
                    <i class="fas fa-file-alt"></i><span>Feedbacks</span>
                </a>
            @endif

            @if(!$isFuncionario && session('usuario') && strtolower(session('usuario')->role ?? '') === 'admin')
                <a href="{{ route('logs.index') }}"
                   class="nav-item {{ request()->routeIs('logs.*') ? 'active' : '' }}"
                   @if(request()->routeIs('logs.*')) aria-current="page" @endif>
                    <i class="fas fa-clipboard-list"></i><span>LOGs do Sistema</span>
                </a>
            @endif
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
        <header class="topbar" role="banner">
            @php
                $user = session('usuario');
                $nomeUsuario = trim($user->nome ?? '');
                $iniciais = 'EP';
                if ($nomeUsuario !== '') {
                    $partes = preg_split('/\s+/', $nomeUsuario);
                    $primeira = $partes[0] ?? '';
                    $segunda = $partes[1] ?? ($partes[0] ?? '');
                    $iniciais = mb_strtoupper(
                        mb_substr($primeira, 0, 1) .
                        mb_substr($segunda, 0, 1)
                    );
                }
            @endphp

            <div class="user-info">
                <div class="user-avatar">{{ $iniciais }}</div>
                <div class="user-details">
                    <h3>{{ $nomeUsuario !== '' ? $nomeUsuario : 'Usuário' }}</h3>
                    <p>
                        @php
                            if ($user) {
                                $role = $user->role ?? 'usuário';
                                echo $role === 'funcionario' ? 'Funcionário' :
                                     ($role === 'admin' ? 'Administrador' : ucfirst($role));
                            } else {
                                echo 'Visitante';
                            }
                        @endphp
                    </p>
                </div>
            </div>

            <div class="topbar-actions">
                <!-- só o botão de configurações -->
                <button class="action-btn"
                        id="settingsBtn"
                        type="button"
                        aria-label="Abrir menu de configurações"
                        aria-haspopup="true"
                        aria-expanded="false">
                    <i class="fas fa-cog" aria-hidden="true"></i>
                </button>

                <div class="settings-menu" id="settingsMenu" role="menu" aria-label="Menu de configurações">
                    <div class="menu-item" id="editPasswordBtn" role="menuitem" tabindex="0">
                        <i class="fas fa-key" aria-hidden="true"></i>
                        <span>Editar Senha</span>
                    </div>

                    <div class="menu-divider"></div>

                    <form method="GET" action="{{ route('logout') }}" style="width:100%;">
                        <button type="submit" class="menu-item" role="menuitem">
                            <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                            <span>Sair</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Área de Conteúdo -->
        <main class="content" id="main-content" role="main">
            @yield('content')
        </main>
    </div>

    <script>
        // Abrir/fechar menu configurações com atualização de aria-expanded
        const settingsBtn = document.getElementById('settingsBtn');
        const settingsMenu = document.getElementById('settingsMenu');
        if (settingsBtn && settingsMenu) {
            settingsBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                const isOpen = settingsMenu.classList.toggle('active');
                settingsBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
            document.addEventListener('click', function (e) {
                if (!settingsMenu.contains(e.target) && e.target !== settingsBtn) {
                    if (settingsMenu.classList.contains('active')) {
                        settingsMenu.classList.remove('active');
                        settingsBtn.setAttribute('aria-expanded', 'false');
                    }
                }
            });
        }
    </script>

    <!-- ========= MODAL GLOBAL DE CONFIRMAÇÃO ========= -->
    <div id="confirmModal" style="
            position: fixed; inset: 0; background: rgba(0,0,0,.45);
            display: none; justify-content: center; align-items: center;
            z-index: 9999; backdrop-filter: blur(3px);
        " role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
        <div style="
                background: #fff; padding: 28px; border-radius: 16px;
                width: 100%; max-width: 420px;
                box-shadow: 0 25px 50px rgba(0,0,0,.2);
                animation: pop .25s ease-out;
                font-family: 'Poppins', sans-serif;
            ">
            <h3 id="confirmTitle" style="
                    margin: 0 0 10px;
                    font-size: 20px; font-weight: 700;
                    background: linear-gradient(135deg,#ec4899,#7e22ce);
                    -webkit-background-clip:text; -webkit-text-fill-color:transparent;
                ">Confirmação</h3>

            <p id="confirmMessage" style="
                    color: #6b7280; margin-bottom: 22px; font-size: 14px;
                "></p>

            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <button id="confirmCancel" style="padding:10px 18px; border-radius:10px;
                        background:#f3f4f6; border:none; cursor:pointer;
                        font-weight:500; font-size:14px;">
                    Cancelar
                </button>

                <button id="confirmOk" style="padding:10px 18px; border-radius:10px; border:none;
                        font-weight:500; font-size:14px; cursor:pointer;
                        background:linear-gradient(135deg,#ec4899,#7e22ce);
                        color:#fff; box-shadow:0 4px 14px rgba(236,72,153,.4);">
                    Confirmar
                </button>
            </div>
        </div>
    </div>

    <script>
        // Função global para mostrar modal de confirmação (Promise)
        window.confirmCustom = function (message) {
            return new Promise(resolve => {
                const modal = document.getElementById('confirmModal');
                const msg = document.getElementById('confirmMessage');
                const okBtn = document.getElementById('confirmOk');
                const cancelBtn = document.getElementById('confirmCancel');

                msg.innerText = message;
                modal.style.display = 'flex';

                const close = () => {
                    modal.style.display = 'none';
                    document.onkeydown = null;
                };

                const confirmHandler = () => { close(); resolve(true); };
                const cancelHandler = () => { close(); resolve(false); };

                okBtn.onclick = confirmHandler;
                cancelBtn.onclick = cancelHandler;

                document.onkeydown = (e) => {
                    if (e.key === 'Escape') cancelHandler();
                };
            });
        };

        document.addEventListener('DOMContentLoaded', function () {
            // 1) FORMs que tinham onsubmit="return confirm('...')"
            document.querySelectorAll('form[onsubmit]').forEach(function (form) {
                const onsubmit = form.getAttribute('onsubmit') || '';
                const match = onsubmit.match(/confirm\((['"])(.*?)\1\)/);
                if (!match) return;

                const message = match[2];

                form.removeAttribute('onsubmit');
                form.dataset.confirmMessage = message;

                form.addEventListener('submit', function (e) {
                    const msg = form.dataset.confirmMessage;
                    if (!msg) return;
                    e.preventDefault();

                    window.confirmCustom(msg).then(ok => {
                        if (ok) {
                            delete form.dataset.confirmMessage;
                            form.submit();
                        }
                    });
                });
            });

            // 2) BOTÕES / LINKS que tinham onclick="return confirm('...')"
            document.querySelectorAll('[onclick]').forEach(function (el) {
                const onclick = el.getAttribute('onclick') || '';
                const match = onclick.match(/confirm\((['"])(.*?)\1\)/);
                if (!match) return;

                const message = match[2];

                el.removeAttribute('onclick');
                el.dataset.confirmMessage = message;

                el.addEventListener('click', function (e) {
                    const msg = el.dataset.confirmMessage;
                    if (!msg) return;

                    e.preventDefault();

                    const form = el.closest('form');
                    const href = el.getAttribute('href');

                    window.confirmCustom(msg).then(ok => {
                        if (!ok) return;

                        delete el.dataset.confirmMessage;

                        if (form) {
                            form.submit();
                        } else if (href) {
                            window.location.href = href;
                        }
                    });
                });
            });
        });
    </script>

    @yield('scripts')
    @include('partials.change_password_modal')
    @include('partials.toast')
</body>

</html>
