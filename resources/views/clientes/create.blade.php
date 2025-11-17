<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($cliente) ? 'Editar Cliente' : 'Novo Cliente' }} - Estética PRO</title>
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
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
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
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: white;
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

        .nav-item span {
            font-weight: 500;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Conteúdo Principal */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

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
            flex-shrink: 0;
        }

        .user-details {
            max-width: 180px;
        }

        .user-details h3 {
            font-size: 16px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
            background: #f3f4f6;
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
            background: #f9fafb;
        }

        .menu-item i {
            margin-right: 12px;
            width: 18px;
            color: var(--text-light);
        }

        .menu-divider {
            height: 1px;
            background: #f3f4f6;
            margin: 4px 0;
        }

        .content {
            padding: 24px;
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 800px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .back-link {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--text-light);
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: var(--primary);
        }

        .back-link i {
            margin-right: 8px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group.centered {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 10px 0 30px;
        }

        label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--text);
        }

        input, select, textarea {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        input:focus, select:focus, textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.2);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        /* Checkbox personalizado - CORREÇÃO DO ALINHAMENTO */
        .form-switch-container {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }

        .form-switch {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f9fafb;
            padding: 12px 20px;
            border-radius: 12px;
            border: 2px solid #e5e7eb;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        /* CORREÇÃO: Centralizar verticalmente o switch com o texto */
        .form-switch {
            display: flex;
            align-items: center; /* Esta linha garante alinhamento vertical */
        }

        .form-switch label[for="ativo"] {
            margin-bottom: 0;
            font-weight: 600;
            display: flex;
            align-items: center; /* Alinha o texto verticalmente */
        }

        .error-message {
            color: var(--danger);
            font-size: 14px;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .error-container {
            background: #fef2f2;
            color: var(--danger);
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid #fecaca;
        }

        .error-container ul {
            margin-left: 20px;
            margin-top: 8px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 24px;
            border: none;
            border-radius: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: var(--text);
            text-decoration: none;
            margin-right: 12px;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
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

        /* Máscaras para campos */
        .input-mask {
            position: relative;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }

            .sidebar-header h1, .nav-item span {
                display: none;
            }

            .nav-item {
                justify-content: center;
                padding: 16px;
            }

            .nav-item i {
                margin-right: 0;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .back-link {
                align-self: flex-end;
            }

            .form-actions {
                flex-direction: column;
                gap: 12px;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .form-switch-container {
                justify-content: flex-start;
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
            <a href="#" class="nav-item">
                <i class="fas fa-hand-holding-usd"></i><span>Comissões</span>
            </a>
             <a href="{{ route('clientes.index') }}" class="nav-item {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                <i class="fas fa-user"></i><span>Clientes</span>
            </a>
                        <a href="{{ route('cargos.index') }}" class="nav-item {{ request()->routeIs('cargos.*') ? 'active' : '' }}">
                <i class="fas fa-briefcase"></i><span>Cargos</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <form method="GET" action="{{ route('logout') }}" style="width:100%;">
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
                    <h3>{{ $usuario->nome ?? 'Usuário' }}</h3>
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
                    <form method="GET" action="{{ route('logout') }}" style="width:100%;">
                        <button type="submit" class="menu-item">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Sair</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Conteúdo -->
        <div class="content">
            <div class="form-container">
                <div class="page-header">
                    <h1 class="page-title">{{ isset($cliente) ? 'Editar Cliente' : 'Novo Cliente' }}</h1>
                    <a href="{{ route('clientes.index') }}" class="back-link">
                        <i class="fas fa-arrow-left"></i> Voltar à lista
                    </a>
                </div>

                @if ($errors->any())
                    <div class="error-container">
                        <strong>Corrija os seguintes erros:</strong>
                        <ul>
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ isset($cliente) ? route('clientes.update', $cliente->id) : route('clientes.store') }}">
                    @csrf
                    @if(isset($cliente))
                        @method('PUT')
                    @endif

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nome">Nome *</label>
                            <input type="text" id="nome" name="nome" value="{{ old('nome', $cliente->nome ?? '') }}" required placeholder="Nome completo do cliente">
                        </div>

                        <div class="form-group">
                            <label for="telefone">Telefone</label>
                            <input type="text" id="telefone" name="telefone" value="{{ old('telefone', $cliente->telefone ?? '') }}" placeholder="(00) 00000-0000">
                        </div>

                        <div class="form-group">
                            <label for="data_nascimento">Data de Nascimento</label>
                            <input type="date" id="data_nascimento" name="data_nascimento" value="{{ old('data_nascimento', $cliente->data_nascimento ?? '') }}">
                        </div>

                        <div class="form-group">
                            <label for="cpf">CPF</label>
                            <input type="text" id="cpf" name="cpf" value="{{ old('cpf', $cliente->cpf ?? '') }}" placeholder="000.000.000-00">
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $cliente->email ?? '') }}" placeholder="cliente@email.com">
                        </div>

                        {{-- Senha (opcional) --}}
                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha @if(empty($cliente)) (senha padrão "123456") @else (preencha para alterar) @endif</label>
                            <input type="password" name="senha" id="senha" class="form-control" minlength="6" autocomplete="new-password">
                        </div>

                        <div class="form-group full-width">
                            <label for="endereco">Endereço</label>
                            <input type="text" id="endereco" name="endereco" value="{{ old('endereco', $cliente->endereco ?? '') }}" placeholder="Endereço completo">
                        </div>

                        <!-- Switch de cliente ativo - CORRIGIDO O ALINHAMENTO -->
                        <div class="form-group full-width centered">
                            <div class="form-switch">
                                <!-- Garante envio de 0 quando desmarcado -->
                                <input type="hidden" name="ativo" value="0">
                                <label class="switch" for="ativo">
                                    <input
                                        type="checkbox"
                                        id="ativo"
                                        name="ativo"
                                        value="1"
                                        {{ old('ativo', ($cliente->ativo ?? 1)) ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                                <label for="ativo" style="margin-bottom: 0; font-weight: 600;">Cliente ativo</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times btn-icon"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save btn-icon"></i>
                            {{ isset($cliente) ? 'Atualizar Cliente' : 'Salvar Cliente' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Configurações do menu dropdown
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

        // Máscaras para os campos
        // Máscara para telefone
        const telefoneInput = document.getElementById('telefone');
        if (telefoneInput) {
            telefoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');

                if (value.length <= 11) {
                    // Formato: (00) 00000-0000
                    value = value.replace(/(\d{2})(\d)/, '($1) $2');
                    value = value.replace(/(\d{5})(\d)/, '$1-$2');
                    value = value.replace(/(-\d{4})\d+?$/, '$1');
                }

                e.target.value = value;
            });
        }

        // Máscara para CPF
        const cpfInput = document.getElementById('cpf');
        if (cpfInput) {
            cpfInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');

                if (value.length <= 11) {
                    // Formato: 000.000.000-00
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                }

                e.target.value = value;
            });
        }
    </script>
</body>
</html>
