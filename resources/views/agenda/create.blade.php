<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($agenda) ? 'Editar Agendamento' : 'Novo Agendamento' }} - Estética PRO</title>
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
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background-color: #f9fafb; color: var(--text); min-height: 100vh; display: flex; }

        /* Sidebar */
        .sidebar { width: var(--sidebar-width); background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%); color: white; display: flex; flex-direction: column; box-shadow: 0 0 25px rgba(0, 0, 0, 0.1); z-index: 10; }
        .sidebar-header { padding: 24px; text-align: center; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .sidebar-header h1 { font-size: 24px; font-weight: 700; }
        .sidebar-nav { flex: 1; padding: 20px 16px; }
        .nav-item { display: flex; align-items: center; padding: 14px 16px; border-radius: 12px; margin-bottom: 8px; transition: all 0.3s ease; cursor: pointer; text-decoration: none; color: white; }
        .nav-item:hover { background: rgba(255, 255, 255, 0.1); }
        .nav-item.active { background: rgba(255, 255, 255, 0.15); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        .nav-item i { width: 24px; margin-right: 12px; font-size: 18px; }
        .nav-item span { font-weight: 500; }
        .sidebar-footer { padding: 16px; border-top: 1px solid rgba(255, 255, 255, 0.1); }

        /* Main */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .topbar { height: 70px; background: white; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); display: flex; align-items: center; justify-content: space-between; padding: 0 24px; }
        .user-info { display: flex; align-items: center; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; margin-right: 12px; flex-shrink: 0; }
        .user-details { max-width: 180px; }
        .user-details h3 { font-size: 16px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-details p { font-size: 13px; color: var(--text-light); }
        .topbar-actions { display: flex; align-items: center; position: relative; }
        .action-btn { background: none; border: none; cursor: pointer; margin-left: 16px; color: var(--text-light); font-size: 18px; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; }
        .action-btn:hover { background: #f3f4f6; color: var(--primary); }
        .settings-menu { position: absolute; top: 50px; right: 0; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15); width: 200px; padding: 8px 0; z-index: 100; opacity: 0; visibility: hidden; transform: translateY(-10px); transition: all 0.3s ease; }
        .settings-menu.active { opacity: 1; visibility: visible; transform: translateY(0); }
        .menu-item { display: flex; align-items: center; padding: 12px 16px; cursor: pointer; transition: all 0.3s ease; width: 100%; background: none; border: none; font-family: inherit; font-size: inherit; text-align: left; }
        .menu-item:hover { background: #f9fafb; }
        .menu-item i { margin-right: 12px; width: 18px; color: var(--text-light); }
        .menu-divider { height: 1px; background: #f3f4f6; margin: 4px 0; }

        .content { padding: 24px; flex: 1; display: flex; justify-content: center; align-items: flex-start; }
        .form-container { background: white; padding: 30px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); width: 100%; max-width: 800px; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-title { font-size: 24px; font-weight: 700; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .back-link { display: flex; align-items: center; text-decoration: none; color: var(--text-light); font-weight: 500; transition: all 0.3s ease; }
        .back-link:hover { color: var(--primary); }
        .back-link i { margin-right: 8px; }

        .form-grid { display: grid; grid-template-columns: 1fr; gap: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group.full-width { grid-column: 1 / -1; }
        .form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }

        label { display: block; font-weight: 500; margin-bottom: 8px; color: var(--text); }
        input, select, textarea { width: 100%; padding: 14px 16px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 14px; transition: all 0.3s ease; font-family: 'Poppins', sans-serif; }
        input:focus, select:focus, textarea:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.2); }
        textarea { min-height: 120px; resize: vertical; }

        .error-container { background: #fef2f2; color: var(--danger); padding: 16px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #fecaca; }
        .error-container ul { margin-left: 20px; margin-top: 8px; }

        .form-actions { display: flex; justify-content: flex-end; margin-top: 20px; gap: 12px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 14px 24px; border: none; border-radius: 12px; font-weight: 500; cursor: pointer; transition: all 0.3s ease; font-size: 16px; text-decoration: none; }
        .btn-secondary { background: #f3f4f6; color: var(--text); }
        .btn-secondary:hover { background: #e5e7eb; }
        .btn-primary { background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; box-shadow: 0 4px 14px rgba(236, 72, 153, 0.4); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(236, 72, 153, 0.5); }
        .btn-icon { margin-right: 8px; }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .sidebar-header h1, .nav-item span { display: none; }
            .nav-item { justify-content: center; padding: 16px; }
            .nav-item i { margin-right: 0; }
            .form-row { grid-template-columns: 1fr; }
            .page-header { flex-direction: column; align-items: flex-start; gap: 15px; }
            .back-link { align-self: flex-end; }
            .form-actions { flex-direction: column; }
            .btn { width: 100%; justify-content: center; }
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
            <a href="{{ route('funcionarios.index') }}" class="nav-item {{ request()->routeIs('funcionarios.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i><span>Funcionários</span>
            </a>
            <a href="{{ route('servicos.index') }}" class="nav-item {{ request()->routeIs('servicos.*') ? 'active' : '' }}">
                <i class="fas fa-scissors"></i><span>Serviços</span>
            </a>
            <a href="{{ route('agenda.index') }}" class="nav-item {{ request()->routeIs('agenda.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i><span>Agenda</span>
            </a>
            <a href="#" class="nav-item"><i class="fas fa-hand-holding-usd"></i><span>Comissões</span></a>
            <a href="{{ route('clientes.index') }}" class="nav-item {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                <i class="fas fa-user"></i><span>Clientes</span>
            </a>
            <a href="{{ route('cargos.index') }}" class="nav-item {{ request()->routeIs('cargos.*') ? 'active' : '' }}">
                <i class="fas fa-briefcase"></i><span>Cargos</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}" style="width:100%;">
                @csrf
                <button type="submit" class="nav-item" style="width:100%; background:none; border:none; color:white; cursor:pointer;">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Sair</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div class="user-info">
                <div class="user-avatar">EP</div>
                <div class="user-details">
                    <h3>{{ Auth::user()->name ?? 'Usuário' }}</h3>
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

        <!-- Content -->
        <div class="content">
            <div class="form-container">
                <div class="page-header">
                    <h1 class="page-title">{{ isset($agenda) ? 'Editar Agendamento' : 'Novo Agendamento' }}</h1>
                    <a href="{{ route('agenda.index') }}" class="back-link">
                        <i class="fas fa-arrow-left"></i> Voltar à agenda
                    </a>
                </div>

                @if ($errors->any())
                    <div class="error-container">
                        <strong>Corrija os seguintes erros:</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Definições de ação/método --}}
                @php
                    use Carbon\Carbon;
                    $isEdit = isset($agenda);
                    $action = $isEdit ? route('agenda.update', $agenda->id) : route('agenda.store');

                    // valores pré-preenchidos quando editando
                    $selFuncionario = old('funcionario_id', $isEdit ? $agenda->funcionario_id : '');
                    $selCliente     = old('cliente_id', $isEdit ? $agenda->cliente_id : '');
                    $selServico     = old('servico_id', $isEdit ? $agenda->servico_id : '');
                    $dataValue      = old('data', $isEdit ? Carbon::parse($agenda->inicio)->format('Y-m-d') : '');
                    $horaValue      = old('hora', $isEdit ? Carbon::parse($agenda->inicio)->format('H:i') : '');
                    $obsValue       = old('observacoes', $isEdit ? ($agenda->observacoes ?? '') : '');
                    $statusValue    = old('status', $isEdit ? ($agenda->status ?? 'agendado') : 'agendado');
                @endphp

                <form method="POST" action="{{ $action }}">
                    @csrf
                    @if($isEdit)
                        @method('PUT')
                    @endif

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="funcionario_id">Funcionário *</label>
                            <select id="funcionario_id" name="funcionario_id" required>
                                <option value="">Selecione...</option>
                                @foreach($funcionarios as $f)
                                    <option value="{{ $f->id }}" {{ (string)$selFuncionario === (string)$f->id ? 'selected' : '' }}>
                                        {{ $f->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="cliente_id">Cliente *</label>
                            <select id="cliente_id" name="cliente_id" required>
                                <option value="">Selecione...</option>
                                @foreach($clientes as $c)
                                    <option value="{{ $c->id }}" {{ (string)$selCliente === (string)$c->id ? 'selected' : '' }}>
                                        {{ $c->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="servico_id">Serviço *</label>
                            <select id="servico_id" name="servico_id" required>
                                <option value="">Selecione...</option>
                                @foreach($servicos as $s)
                                    <option value="{{ $s->id }}" {{ (string)$selServico === (string)$s->id ? 'selected' : '' }}>
                                        {{ $s->nome }} ({{ $s->duracao_minutos ?? 30 }} min)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="data">Data *</label>
                                <input type="date" id="data" name="data" value="{{ $dataValue }}" required>
                            </div>

                            <div class="form-group">
                                <label for="hora">Hora (início) *</label>
                                <input type="time" id="hora" name="hora" value="{{ $horaValue }}" required>
                            </div>
                        </div>

                        {{-- Status (opcional alterar aqui também) --}}
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="agendado"  {{ $statusValue==='agendado'  ? 'selected' : '' }}>Agendado</option>
                                <option value="confirmado"{{ $statusValue==='confirmado'? 'selected' : '' }}>Confirmado</option>
                                <option value="concluido" {{ $statusValue==='concluido' ? 'selected' : '' }}>Concluído</option>
                                <option value="cancelado" {{ $statusValue==='cancelado' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="observacoes">Observações (opcional)</label>
                            <textarea id="observacoes" name="observacoes" rows="3" placeholder="Observações sobre o agendamento">{{ $obsValue }}</textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('agenda.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times btn-icon"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save btn-icon"></i> {{ $isEdit ? 'Salvar Alterações' : 'Salvar Agendamento' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
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
</body>
</html>
