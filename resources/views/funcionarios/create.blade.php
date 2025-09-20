<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($funcionario) ? 'Editar Funcionário' : 'Cadastrar Funcionário' }} - Estética PRO</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* === Layout (igual ao seu) === */
        :root {
            --primary: #ec4899;
            --primary-dark: #db2777;
            --primary-light: #fbcfe8;
            --secondary: #7e22ce;
            --text: #1f2937;
            --text-light: #6b7280;
            --sidebar-width: 260px;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f9fafb; color:var(--text); min-height:100vh; display:flex; }
        .sidebar { width:var(--sidebar-width); background:linear-gradient(180deg,var(--primary)0%,var(--secondary)100%); color:white; display:flex; flex-direction:column; box-shadow:0 0 25px rgba(0,0,0,0.1); z-index:10; }
        .sidebar-header { padding:24px; text-align:center; border-bottom:1px solid rgba(255,255,255,0.1); }
        .sidebar-header h1 { font-size:24px; font-weight:700; }
        .sidebar-nav { flex:1; padding:20px 16px; display:flex; flex-direction:column; }
        .nav-item { display:flex; align-items:center; padding:14px 16px; border-radius:12px; margin-bottom:8px; transition:.3s; text-decoration:none; color:white; font-weight:500; }
        .nav-item:hover { background:rgba(255,255,255,0.1); }
        .nav-item.active { background:rgba(255,255,255,0.15); box-shadow:0 4px 12px rgba(0,0,0,0.1); }
        .nav-item i { width:24px; margin-right:12px; font-size:18px; }
        .sidebar-footer { padding:16px; border-top:1px solid rgba(255,255,255,0.1); }
        .main-content { flex:1; display:flex; flex-direction:column; overflow-y:auto; }
        .topbar { height:70px; background:white; box-shadow:0 2px 10px rgba(0,0,0,0.05); display:flex; align-items:center; justify-content:space-between; padding:0 24px; }
        .user-info { display:flex; align-items:center; }
        .user-avatar { width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,var(--primary)0%,var(--secondary)100%); display:flex; align-items:center; justify-content:center; color:white; font-weight:600; margin-right:12px; }
        .user-details h3 { font-size:16px; font-weight:600; }
        .user-details p { font-size:13px; color:var(--text-light); }
        .topbar-actions { display:flex; align-items:center; position:relative; }
        .action-btn { background:none; border:none; cursor:pointer; margin-left:16px; color:var(--text-light); font-size:18px; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; transition:.3s; }
        .action-btn:hover { background:#f3f4f6; color:var(--primary); }
        .settings-menu { position:absolute; top:50px; right:0; background:white; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.15); width:200px; padding:8px 0; z-index:100; opacity:0; visibility:hidden; transform:translateY(-10px); transition:.3s; }
        .settings-menu.active { opacity:1; visibility:visible; transform:translateY(0); }
        .menu-item { display:flex; align-items:center; padding:12px 16px; cursor:pointer; transition:.3s; width:100%; background:none; border:none; font-family:inherit; font-size:inherit; text-align:left; }
        .menu-item:hover { background:#f9fafb; }
        .menu-item i { margin-right:12px; width:18px; color:var(--text-light); }
        .menu-divider { height:1px; background:#f3f4f6; margin:4px 0; }
        .content { padding:24px; flex:1; display:flex; justify-content:center; align-items:flex-start; }
        .form-container { background:white; padding:30px; border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,0.08); width:100%; max-width:700px; }
        .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; }
        .page-title { font-size:24px; font-weight:700; background:linear-gradient(135deg,var(--primary)0%,var(--secondary)100%); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .back-link { display:flex; align-items:center; text-decoration:none; color:var(--text-light); font-weight:500; transition:.3s; }
        .back-link:hover { color:var(--primary); }
        .back-link i { margin-right:8px; }
        .form-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:20px; }
        .form-group { margin-bottom:20px; }
        .form-group.full-width { grid-column:1/-1; }
        .form-group.centered { display:flex; justify-content:center; align-items:center; margin:10px 0 30px; }
        label { display:block; font-weight:500; margin-bottom:8px; color:var(--text); }
        input,select,textarea { width:100%; padding:14px 16px; border:2px solid #e5e7eb; border-radius:12px; font-size:14px; transition:.2s; font-family:'Poppins',sans-serif; }
        input:focus,select:focus,textarea:focus { border-color:var(--primary); outline:none; box-shadow:0 0 0 3px rgba(236,72,153,0.2); }
        .checkbox-group { display:flex; align-items:center; gap:10px; background:#f9fafb; padding:15px 20px; border-radius:12px; border:2px solid #e5e7eb; }
        .checkbox { width:20px; height:20px; accent-color:var(--primary); }
        .form-actions { display:flex; justify-content:flex-end; margin-top:20px; }
        .btn { display:inline-flex; align-items:center; justify-content:center; padding:14px 24px; border:none; border-radius:12px; font-weight:500; cursor:pointer; transition:.3s; font-size:16px; }
        .btn-primary { background:linear-gradient(135deg,var(--primary)0%,var(--secondary)100%); color:white; box-shadow:0 4px 14px rgba(236,72,153,0.4); }
        .btn-primary:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(236,72,153,0.5); }
        .btn-icon { margin-right:8px; }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h1>Estética PRO</h1>
        </div>
        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="nav-item">
                <i class="fas fa-chart-line"></i><span>Dashboard</span>
            </a>
            <a href="{{ route('funcionarios.index') }}" class="nav-item active">
                <i class="fas fa-users"></i><span>Funcionários</span>
            </a>
            <a href="{{ route('servicos.index') }}" class="nav-item">
                <i class="fas fa-scissors"></i><span>Serviços</span>
    </a>
            <a href="#" class="nav-item"><i class="fas fa-calendar-alt"></i><span>Agenda</span></a>
            <a href="#" class="nav-item"><i class="fas fa-hand-holding-usd"></i><span>Comissões</span></a>
            <a href="#" class="nav-item"><i class="fas fa-user"></i><span>Clientes</span></a>
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
                    <h1 class="page-title">
                        {{ isset($funcionario) ? 'Editar Funcionário' : 'Cadastrar Funcionário' }}
                    </h1>
                    <a href="{{ route('funcionarios.index') }}" class="back-link">
                        <i class="fas fa-arrow-left"></i> Voltar à lista
                    </a>
                </div>

                <form method="POST" 
                      action="{{ isset($funcionario) ? route('funcionarios.update', $funcionario->id) : route('funcionarios.store') }}">
                    @csrf
                    @if(isset($funcionario))
                        @method('PUT')
                    @endif

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nome">Nome Completo</label>
                            <input type="text" id="nome" name="nome" required placeholder="Digite o nome completo"
                                   value="{{ old('nome', $funcionario->nome ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label for="cpf">CPF</label>
                            <input type="text" id="cpf" name="cpf" required placeholder="000.000.000-00"
                                   value="{{ old('cpf', $funcionario->cpf ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label for="email">E-mail</label>
                            <input type="email" id="email" name="email" required placeholder="funcionario@esteticapro.com"
                                   value="{{ old('email', $funcionario->email ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label for="telefone">Telefone</label>
                            <input type="text" id="telefone" name="telefone" required placeholder="(00) 00000-0000"
                                   value="{{ old('telefone', $funcionario->telefone ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label for="cargo">Cargo</label>
                            <select id="cargo" name="cargo" required>
                                <option value="">Selecione um cargo</option>
                                <option value="cabeleireiro" {{ old('cargo', $funcionario->cargo ?? '') == 'cabeleireiro' ? 'selected' : '' }}>Cabeleireiro(a)</option>
                                <option value="esteticista" {{ old('cargo', $funcionario->cargo ?? '') == 'esteticista' ? 'selected' : '' }}>Esteticista</option>
                                <option value="manicure" {{ old('cargo', $funcionario->cargo ?? '') == 'manicure' ? 'selected' : '' }}>Manicure</option>
                                <option value="massoterapeuta" {{ old('cargo', $funcionario->cargo ?? '') == 'massoterapeuta' ? 'selected' : '' }}>Massoterapeuta</option>
                                <option value="recepcionista" {{ old('cargo', $funcionario->cargo ?? '') == 'recepcionista' ? 'selected' : '' }}>Recepcionista</option>
                                <option value="gerente" {{ old('cargo', $funcionario->cargo ?? '') == 'gerente' ? 'selected' : '' }}>Gerente</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="especialidade">Especialidade</label>
                            <input type="text" id="especialidade" name="especialidade"
                                   placeholder="Ex: Coloração, Limpeza de Pele"
                                   value="{{ old('especialidade', $funcionario->especialidade ?? '') }}">
                        </div>
                        <div class="form-group full-width">
                            <label for="endereco">Endereço</label>
                            <textarea id="endereco" name="endereco" rows="3"
                                      placeholder="Digite o endereço completo">{{ old('endereco', $funcionario->endereco ?? '') }}</textarea>
                        </div>
                        <div class="form-group full-width centered">
                            <div class="checkbox-group">
                                <input type="checkbox" id="ativo" name="ativo" value="1" class="checkbox"
                                       {{ old('ativo', $funcionario->ativo ?? 1) ? 'checked' : '' }}>
                                <label for="ativo" style="margin-bottom: 0; font-weight: 600;">Funcionário ativo</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save btn-icon"></i>
                            {{ isset($funcionario) ? 'Atualizar Funcionário' : 'Salvar Funcionário' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
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
</body>

</html>
