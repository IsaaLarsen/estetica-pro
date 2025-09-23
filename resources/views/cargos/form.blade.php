<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($cargo) ? 'Editar Cargo' : 'Novo Cargo' }} - Estética PRO</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ec4899; --primary-dark: #db2777; --primary-light: #fbcfe8;
            --secondary: #7e22ce; --text: #1f2937; --text-light: #6b7280;
            --success: #10b981; --warning: #f59e0b; --danger: #ef4444;
            --sidebar-width: 260px;
        }
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Poppins',sans-serif;background:#f9fafb;color:var(--text);min-height:100vh;display:flex}
        .sidebar{width:var(--sidebar-width);background:linear-gradient(180deg,var(--primary)0%,var(--secondary)100%);color:#fff;display:flex;flex-direction:column;box-shadow:0 0 25px rgba(0,0,0,.1)}
        .sidebar-header{padding:24px;text-align:center;border-bottom:1px solid rgba(255,255,255,0.1)}
        .sidebar-nav{flex:1;padding:20px 16px;display:flex;flex-direction:column}
        .nav-item{display:flex;align-items:center;padding:14px 16px;border-radius:12px;margin-bottom:8px;transition:.3s;text-decoration:none;color:#fff;font-weight:500}
        .nav-item:hover{background:rgba(255,255,255,0.1)}
        .nav-item.active{background:rgba(255,255,255,0.15);box-shadow:0 4px 12px rgba(0,0,0,0.1)}
        .nav-item i{width:24px;margin-right:12px;font-size:18px}
        .sidebar-footer{padding:16px;border-top:1px solid rgba(255,255,255,0.1)}
        .main-content{flex:1;display:flex;flex-direction:column;overflow-y:auto}
        .topbar{height:70px;background:#fff;box-shadow:0 2px 10px rgba(0,0,0,0.05);display:flex;align-items:center;justify-content:space-between;padding:0 24px}
        .user-info{display:flex;align-items:center}
        .user-avatar{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--primary)0%,var(--secondary)100%);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;margin-right:12px}
        .user-details h3{font-size:16px;font-weight:600}
        .user-details p{font-size:13px;color:var(--text-light)}
        .topbar-actions{display:flex;align-items:center;position:relative}
        .action-btn{background:none;border:none;cursor:pointer;margin-left:16px;color:var(--text-light);font-size:18px;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;transition:.3s}
        .action-btn:hover{background:#f3f4f6;color:var(--primary)}
        .settings-menu{position:absolute;top:50px;right:0;background:#fff;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,0.15);width:200px;padding:8px 0;z-index:100;opacity:0;visibility:hidden;transform:translateY(-10px);transition:.3s}
        .settings-menu.active{opacity:1;visibility:visible;transform:translateY(0)}
        .menu-item{display:flex;align-items:center;padding:12px 16px;cursor:pointer;transition:.3s;width:100%;background:none;border:none;font-family:inherit;font-size:inherit;text-align:left}
        .menu-item:hover{background:#f9fafb}
        .menu-item i{margin-right:12px;width:18px;color:var(--text-light)}
        .menu-divider{height:1px;background:#f3f4f6;margin:4px 0}
        .content{padding:24px;flex:1;display:flex;justify-content:center;align-items:flex-start}
        .form-container{background:#fff;padding:30px;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.08);width:100%;max-width:700px}
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px}
        .page-title{font-size:24px;font-weight:700;background:linear-gradient(135deg,var(--primary)0%,var(--secondary)100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
        .back-link{display:flex;align-items:center;text-decoration:none;color:var(--text-light);font-weight:500;transition:.3s}
        .back-link:hover{color:var(--primary)}
        .back-link i{margin-right:8px}
        .form-grid{display:grid;grid-template-columns:repeat(1,1fr);gap:20px}
        label{display:block;font-weight:500;margin-bottom:8px;color:var(--text)}
        input,textarea{width:100%;padding:14px 16px;border:2px solid #e5e7eb;border-radius:12px;font-size:14px;transition:.2s;font-family:'Poppins',sans-serif}
        input:focus,textarea:focus{border-color:var(--primary);outline:none;box-shadow:0 0 0 3px rgba(236,72,153,0.2)}
        .form-switch{display:flex;align-items:center;gap:10px;background:#f9fafb;padding:12px 20px;border-radius:12px;border:2px solid #e5e7eb}
        .switch{position:relative;display:inline-block;width:50px;height:24px}
        .switch input{opacity:0;width:0;height:0}
        .slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:#ccc;transition:.4s;border-radius:24px}
        .slider:before{position:absolute;content:"";height:18px;width:18px;left:3px;bottom:3px;background:#fff;transition:.4s;border-radius:50%}
        input:checked + .slider{background:linear-gradient(135deg,var(--primary)0%,var(--secondary)100%)}
        input:checked + .slider:before{transform:translateX(26px)}
        .form-actions{display:flex;justify-content:flex-end;margin-top:20px;gap:10px}
        .btn{display:inline-flex;align-items:center;justify-content:center;padding:14px 24px;border:none;border-radius:12px;font-weight:500;cursor:pointer;transition:.3s;font-size:16px;text-decoration:none}
        .btn-primary{background:linear-gradient(135deg,var(--primary)0%,var(--secondary)100%);color:#fff;box-shadow:0 4px 14px rgba(236,72,153,0.4)}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(236,72,153,0.5)}
        .btn-light{background:#f3f4f6;color:var(--text)}
        .btn-icon{margin-right:8px}
        .alert{padding:12px 14px;border-radius:10px;border:1px solid #f59e0b33;background:#fffbeb;color:#92400e;font-size:14px}
        .text-danger{color:#dc2626;font-size:12px;margin-top:6px;display:block}
        @media (max-width:768px){.sidebar{width:70px}.sidebar-header h1,.nav-item span{display:none}.nav-item{justify-content:center;padding:16px}.nav-item i{margin-right:0}}
    </style>
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header"><h1>Estética PRO</h1></div>
        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fas fa-chart-line"></i><span>Dashboard</span></a>
            <a href="{{ route('funcionarios.index') }}" class="nav-item {{ request()->routeIs('funcionarios.*') ? 'active' : '' }}"><i class="fas fa-users"></i><span>Funcionários</span></a>
            <a href="{{ route('servicos.index') }}" class="nav-item {{ request()->routeIs('servicos.*') ? 'active' : '' }}"><i class="fas fa-scissors"></i><span>Serviços</span></a>
            <a href="{{ route('agenda.index') }}" class="nav-item {{ request()->routeIs('agenda.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i><span>Agenda</span>
            </a>
            <a href="{{ route('clientes.index') }}" class="nav-item {{ request()->routeIs('clientes.*') ? 'active' : '' }}"><i class="fas fa-user"></i><span>Clientes</span></a>
            <a href="{{ route('cargos.index') }}" class="nav-item {{ request()->routeIs('cargos.*') ? 'active' : '' }}"><i class="fas fa-briefcase"></i><span>Cargos</span></a>
        </nav>
        <div class="sidebar-footer">
            <form method="GET" action="{{ route('logout') }}" style="width:100%;">
                <button type="submit" class="nav-item" style="width:100%; background:none; border:none; color:white; cursor:pointer;">
                    <i class="fas fa-sign-out-alt"></i><span>Sair</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main -->
    <div class="main-content">
        <div class="topbar">
            <div class="user-info">
                <div class="user-avatar">EP</div>
                <div class="user-details">
                    <h3>{{ session('usuario.nome') ?? 'Administrador' }}</h3>
                    <p>Usuário Master</p>
                </div>
            </div>
            <div class="topbar-actions">
                <button class="action-btn"><i class="fas fa-bell"></i></button>
                <button class="action-btn" id="settingsBtn"><i class="fas fa-cog"></i></button>
                <div class="settings-menu" id="settingsMenu">
                    <div class="menu-item"><i class="fas fa-key"></i><span>Editar Senha</span></div>
                    <div class="menu-divider"></div>
                    <form method="GET" action="{{ route('logout') }}" style="width:100%;">
                        <button type="submit" class="menu-item"><i class="fas fa-sign-out-alt"></i><span>Sair</span></button>
                    </form>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="form-container">
                <div class="page-header">
                    <h1 class="page-title">{{ isset($cargo) ? 'Editar Cargo' : 'Novo Cargo' }}</h1>
                    <a href="{{ route('cargos.index') }}" class="back-link"><i class="fas fa-arrow-left"></i> Voltar à lista</a>
                </div>

                {{-- Mensagens de sucesso/erro --}}
                @if(session('success'))
                    <div class="alert"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
                @endif

                {{-- Form --}}
                <form method="POST" action="{{ isset($cargo) ? route('cargos.update', $cargo) : route('cargos.store') }}">
                    @csrf
                    @if(isset($cargo)) @method('PUT') @endif

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nome">Nome *</label>
                            <input type="text" id="nome" name="nome" required placeholder="Ex.: Esteticista"
                                   value="{{ old('nome', $cargo->nome ?? '') }}">
                            @error('nome') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="descricao">Descrição</label>
                            <textarea id="descricao" name="descricao" rows="3" placeholder="Breve descrição do cargo">{{ old('descricao', $cargo->descricao ?? '') }}</textarea>
                            @error('descricao') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label style="display:block;margin-bottom:10px;">Status</label>
                            <div class="form-switch">
                                <input type="hidden" name="ativo" value="0">
                                <label class="switch" for="ativo">
                                    <input type="checkbox" id="ativo" name="ativo" value="1"
                                           {{ old('ativo', $cargo->ativo ?? 1) ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                                <span>Ativo</span>
                            </div>
                            @error('ativo') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('cargos.index') }}" class="btn btn-light"><i class="fas fa-times btn-icon"></i>Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save btn-icon"></i>
                            {{ isset($cargo) ? 'Atualizar Cargo' : 'Salvar Cargo' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const settingsBtn=document.getElementById('settingsBtn');
        const settingsMenu=document.getElementById('settingsMenu');
        if(settingsBtn&&settingsMenu){
            settingsBtn.addEventListener('click',e=>{e.stopPropagation();settingsMenu.classList.toggle('active');});
            document.addEventListener('click',e=>{ if(!settingsMenu.contains(e.target)&&e.target!==settingsBtn){settingsMenu.classList.remove('active');}});
        }
    </script>
</body>
</html>
