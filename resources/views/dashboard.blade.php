<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Estética PRO</title>
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
        body { font-family: 'Poppins', sans-serif; background-color: #f9fafb; color: var(--text); min-height: 100vh; display: flex; }
        .sidebar { width: var(--sidebar-width); background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%); color: white; display: flex; flex-direction: column; box-shadow: 0 0 25px rgba(0, 0, 0, 0.1); z-index: 10; }
        .sidebar-header { padding: 24px; text-align: center; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .sidebar-header h1 { font-size: 24px; font-weight: 700; }
        .sidebar-nav { flex: 1; padding: 20px 16px; display: flex; flex-direction: column; }
        .nav-item { display: flex; align-items: center; padding: 14px 16px; border-radius: 12px; margin-bottom: 8px; transition: all 0.3s ease; text-decoration: none; color: white; font-weight: 500; }
        .nav-item:hover { background: rgba(255, 255, 255, 0.1); }
        .nav-item.active { background: rgba(255, 255, 255, 0.15); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        .nav-item i { width: 24px; margin-right: 12px; font-size: 18px; }
        .sidebar-footer { padding: 16px; border-top: 1px solid rgba(255, 255, 255, 0.1); }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .topbar { height: 70px; background: white; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); display: flex; align-items: center; justify-content: space-between; padding: 0 24px; }
        .user-info { display: flex; align-items: center; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; margin-right: 12px; }
        .user-details h3 { font-size: 16px; font-weight: 600; }
        .user-details p { font-size: 13px; color: var(--text-light); }
        .topbar-actions { display: flex; align-items: center; position: relative; }
        .action-btn { background: none; border: none; cursor: pointer; margin-left: 16px; color: var(--text-light); font-size: 18px; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; }
        .action-btn:hover { background-color: #f3f4f6; color: var(--primary); }
        .settings-menu { position: absolute; top: 50px; right: 0; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15); width: 200px; padding: 8px 0; z-index: 100; opacity: 0; visibility: hidden; transform: translateY(-10px); transition: all 0.3s ease; }
        .settings-menu.active { opacity: 1; visibility: visible; transform: translateY(0); }
        .menu-item { display: flex; align-items: center; padding: 12px 16px; cursor: pointer; transition: all 0.3s ease; width: 100%; background: none; border: none; font-family: inherit; font-size: inherit; text-align: left; }
        .menu-item:hover { background-color: #f9fafb; }
        .menu-item i { margin-right: 12px; width: 18px; color: var(--text-light); }
        .menu-divider { height: 1px; background-color: #f3f4f6; margin: 4px 0; }
        .content { padding: 24px; flex: 1; }
        .welcome-section { margin-bottom: 30px; }
        .welcome-section h2 { font-size: 24px; font-weight: 600; margin-bottom: 8px; }
        .welcome-section p { color: var(--text-light); }
        .metrics-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px; margin-bottom: 30px; }
        .metric-card { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .metric-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1); }
        .metric-header { display: flex; align-items: center; margin-bottom: 16px; }
        .metric-icon { width: 48px; height: 48px; border-radius: 12px; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 20px; margin-right: 12px; }
        .metric-title { font-size: 16px; font-weight: 500; color: var(--text-light); }
        .metric-value { font-size: 28px; font-weight: 700; color: var(--primary); margin-bottom: 8px; }
        .metric-change { font-size: 14px; display: flex; align-items: center; }
        .metric-change.positive { color: #10b981; }
        .metric-change.negative { color: #ef4444; }
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
            <a href="{{ route('funcionarios.index') }}" class="nav-item {{ request()->routeIs('funcionarios.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i><span>Funcionários</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-scissors"></i><span>Serviços</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-calendar-alt"></i><span>Agenda</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-hand-holding-usd"></i><span>Comissões</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-user"></i><span>Clientes</span>
            </a>
        </nav>
        
        <!-- Botão logout no rodapé -->
        <div class="sidebar-footer">
            <form method="GET" action="{{ route('logout') }}" style="width:100%;">
                <button type="submit" class="nav-item" style="width:100%; background:none; border:none; color:white; display:flex; align-items:center; text-align:left; cursor:pointer;">
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
                    <h3>{{ $usuario->nome }}</h3>
                    <p>Administrador</p>
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
        
        <!-- Conteúdo -->
        <div class="content">
            <div class="welcome-section">
                <h2>Bem-vindo, {{ $usuario->nome }}</h2>
                <p>Aqui está o resumo das atividades do seu salão hoje</p>
            </div>

            <!-- Cards de Métricas -->
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-header">
                        <div class="metric-icon"><i class="fas fa-calendar-check"></i></div>
                        <div class="metric-title">Agendamentos</div>
                    </div>
                    <div class="metric-value">12</div>
                    <div class="metric-change positive"><i class="fas fa-arrow-up"></i> 20% desde ontem</div>
                </div>
                <div class="metric-card">
                    <div class="metric-header">
                        <div class="metric-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="metric-title">Serviços Concluídos</div>
                    </div>
                    <div class="metric-value">8</div>
                    <div class="metric-change positive"><i class="fas fa-arrow-up"></i> 14% desde ontem</div>
                </div>
                <div class="metric-card">
                    <div class="metric-header">
                        <div class="metric-icon"><i class="fas fa-dollar-sign"></i></div>
                        <div class="metric-title">Faturamento</div>
                    </div>
                    <div class="metric-value">R$ 1.200,00</div>
                    <div class="metric-change positive"><i class="fas fa-arrow-up"></i> 18% desde ontem</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Abrir/fechar menu configurações
        const settingsBtn = document.getElementById('settingsBtn');
        const settingsMenu = document.getElementById('settingsMenu');
        settingsBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            settingsMenu.classList.toggle('active');
        });
        document.addEventListener('click', function(e) {
            if (!settingsMenu.contains(e.target) && e.target !== settingsBtn) {
                settingsMenu.classList.remove('active');
            }
        });
    </script>
</body>
</html>
