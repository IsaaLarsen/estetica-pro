<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda - Estética PRO</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
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
        body { font-family: 'Poppins', sans-serif; background-color: #f9fafb; color: var(--text); min-height: 100vh; display: flex; }

        /* Sidebar */
        .sidebar { width: var(--sidebar-width); background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%); color: white; display: flex; flex-direction: column; box-shadow: 0 0 25px rgba(0, 0, 0, 0.1); z-index: 10; }
        .sidebar-header { padding: 24px; text-align: center; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .sidebar-header h1 { font-size: 24px; font-weight: 700; }
        .sidebar-nav { flex: 1; padding: 20px 16px; display: flex; flex-direction: column; }
        .nav-item { display: flex; align-items: center; padding: 14px 16px; border-radius: 12px; margin-bottom: 8px; transition: all 0.3s ease; text-decoration: none; color: white; font-weight: 500; }
        .nav-item:hover { background: rgba(255, 255, 255, 0.1); }
        .nav-item.active { background: rgba(255, 255, 255, 0.15); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        .nav-item i { width: 24px; margin-right: 12px; font-size: 18px; }
        .sidebar-footer { padding: 16px; border-top: 1px solid rgba(255, 255, 255, 0.1); }

        /* Main Content */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }

        /* Topbar */
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

        /* Content */
        .content { padding: 24px; flex: 1; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 16px; }
        .page-title { font-size: 28px; font-weight: 700; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .header-actions { display: flex; gap: 16px; flex-wrap: wrap; }

        /* Buttons */
        .btn { display: flex; align-items: center; padding: 12px 20px; border-radius: 12px; font-weight: 500; cursor: pointer; transition: all 0.3s ease; border: none; text-decoration: none; }
        .btn-primary { background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; box-shadow: 0 4px 14px rgba(236, 72, 153, 0.4); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(236, 72, 153, 0.5); }
        .btn-secondary { background: #7e22ce; color: white; box-shadow: 0 4px 14px rgba(126, 34, 206, 0.4); }
        .btn-secondary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(126, 34, 206, 0.5); }
        .btn-info { background: #0EA5E9; color: white; box-shadow: 0 4px 14px rgba(14, 165, 233, 0.4); }
        .btn-info:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(14, 165, 233, 0.5); }
        .btn-icon { margin-right: 8px; }

        /* Filter */
        .filter-section { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .filter-label { font-weight: 500; color: var(--text); white-space: nowrap; }
        .filter-select { padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 14px; min-width: 180px; transition: all 0.3s ease; background: white; font-family: 'Poppins', sans-serif; }
        .filter-select:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.2); }

        /* Calendar Container */
        .calendar-container {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            padding: 20px;
            height: 90vh;
        }
        #calendar { height: 100%; }

        /* Alerts */
        .alert { padding: 16px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; }
        .alert-success { background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }

        /* FullCalendar theming (solid colors, no transparency) */
        .fc { font-family: 'Poppins', sans-serif; }
        .fc-toolbar { flex-wrap: wrap; gap: 15px; }
        .fc-toolbar-title { font-size: 1.3rem; font-weight: 600; color: var(--text); }
        .fc-button { background: white; border: 1px solid #d1d5db; color: var(--text); font-weight: 500; padding: 8px 16px; border-radius: 8px; transition: all 0.3s ease; }
        .fc-button:hover { background: var(--primary); color: white; border-color: var(--primary); }
        .fc-button-primary:not(:disabled).fc-button-active { background: var(--primary); border-color: var(--primary); }
        .fc-col-header-cell { background: #f8fafc; padding: 12px 0; font-weight: 600; color: var(--text); border-color: #e5e7eb; }
        .fc-day-today { background-color: rgba(236, 72, 153, 0.1) !important; }
        .fc-event, .fc-event .fc-event-main, .fc-h-event, .fc-v-event { opacity: 1 !important; }
        .fc-event .fc-event-main { color: #fff; }

        /* Status color classes (solid) */
        .fc-event.st-agendado, .fc-h-event.st-agendado, .fc-v-event.st-agendado { background-color: #3b82f6 !important; border-color: #3b82f6 !important; }
        .fc-event.st-confirmado, .fc-h-event.st-confirmado, .fc-v-event.st-confirmado { background-color: #10b981 !important; border-color: #10b981 !important; }
        .fc-event.st-concluido, .fc-h-event.st-concluido, .fc-v-event.st-concluido { background-color: #7e22ce !important; border-color: #7e22ce !important; }
        .fc-event.st-cancelado, .fc-h-event.st-cancelado, .fc-v-event.st-cancelado { background-color: #ef4444 !important; border-color: #ef4444 !important; }

        /* Modal - NOVO DESIGN */
        .modal-backdrop {
            position: fixed; inset: 0; background: rgba(0,0,0,.5);
            display: none; align-items: center; justify-content: center; z-index: 2000;
            backdrop-filter: blur(4px);
        }
        .modal-backdrop.active { display: flex; }
        .modal-card {
            width: 95%; max-width: 500px; background: #fff; border-radius: 20px; padding: 0;
            box-shadow: 0 25px 50px rgba(0,0,0,.3);
            overflow: hidden;
        }
        .modal-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            padding: 24px;
            color: white;
            position: relative;
        }
        .modal-title {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
        }
        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255,255,255,.2);
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            color: white;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        .modal-close:hover {
            background: rgba(255,255,255,.3);
            transform: rotate(90deg);
        }
        .modal-body {
            padding: 24px;
        }
        .modal-row {
            display: flex;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .modal-row:last-child {
            border-bottom: none;
        }
        .modal-label {
            width: 120px;
            font-weight: 600;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .modal-label i {
            color: var(--primary);
            width: 16px;
        }
        .modal-value {
            flex: 1;
            color: var(--text);
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        .select-status {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            background: white;
            transition: all 0.3s ease;
        }
        .select-status:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.2);
        }
        .modal-actions {
            display: flex;
            gap: 12px;
            padding: 20px 24px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }
        .modal-actions .btn {
            flex: 1;
            justify-content: center;
            padding: 12px 16px;
            font-weight: 500;
            font-size: 14px;
        }
        .observacoes-content {
            background: #f8fafc;
            padding: 12px;
            border-radius: 8px;
            border-left: 4px solid var(--primary);
            font-style: italic;
            color: var(--text-light);
            width: 100%;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .sidebar-header h1, .nav-item span { display: none; }
            .nav-item { justify-content: center; padding: 16px; }
            .nav-item i { margin-right: 0; }
            .page-header { flex-direction: column; align-items: flex-start; gap: 16px; }
            .header-actions { width: 100%; justify-content: space-between; }
            .filter-section { width: 100%; }
            .filter-select { flex: 1; min-width: auto; }
            .modal-card {
                margin: 20px;
                width: calc(100% - 40px);
            }
            .modal-row {
                flex-direction: column;
                gap: 8px;
                align-items: flex-start;
            }
            .modal-label {
                width: 100%;
            }
            .modal-actions {
                flex-direction: column;
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
            <a href="{{ route('funcionarios.index') }}" class="nav-item {{ request()->routeIs('funcionarios.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i><span>Funcionários</span>
            </a>
            <a href="{{ route('servicos.index') }}" class="nav-item {{ request()->routeIs('servicos.*') ? 'active' : '' }}">
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

        <!-- Content -->
        <div class="content">
            <div class="page-header">
                <h1 class="page-title">Agenda</h1>
                <div class="header-actions">
                    <div class="filter-section">
                        <span class="filter-label">Funcionário:</span>
                        <select id="filtro-func" class="filter-select">
                            <option value="">Todos</option>
                            @foreach($funcionarios as $f)
                                <option value="{{ $f->id }}" {{ ($selectedFuncionarioId == $f->id) ? 'selected' : '' }}>
                                    {{ $f->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="action-buttons" style="display: flex; gap: 12px;">
                        <a href="{{ route('agenda.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus btn-icon"></i>Novo agendamento
                        </a>
                        <a href="{{ route('agenda.bloqueios.index') }}" class="btn btn-secondary">
                            <i class="fas fa-lock btn-icon"></i>Bloqueios
                        </a>
                        <a href="{{ route('settings.edit') }}" class="btn btn-info">
                            <i class="fas fa-cog btn-icon"></i>Configurações
                        </a>
                    </div>
                </div>
            </div>

            @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            <div class="calendar-container">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    <!-- Modal (detalhes + status + editar) -->
    <div class="modal-backdrop" id="eventModal">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title" id="mTitle">Detalhes do Agendamento</div>
                <button class="modal-close" id="mClose">&times;</button>
            </div>
            <div class="modal-body">
                <div class="modal-row">
                    <div class="modal-label"><i class="fas fa-user"></i>Cliente</div>
                    <div class="modal-value" id="mCliente" style="margin-left: 12px;">—</div>
                </div>
                <div class="modal-row">
                    <div class="modal-label"><i class="fas fa-scissors"></i>Serviço</div>
                    <div class="modal-value" id="mServico" style="margin-left: 12px;">—</div>
                </div>
                <div class="modal-row">
                    <div class="modal-label"><i class="fas fa-users"></i>Funcionário</div>
                    <div class="modal-value" id="mFuncionario" style="margin-left: 12px;">—</div>
                </div>
                <div class="modal-row">
                    <div class="modal-label"><i class="fas fa-clock"></i>Horário</div>
                    <div class="modal-value" id="mHorario" style="margin-left: 12px;">—</div>
                </div>
                <div class="modal-row">
                    <div class="modal-label"><i class="fas fa-sticky-note"></i>Observações</div>
                    <div class="modal-value" style="margin-left: 12px;">
                        <div id="mObservacoes" class="observacoes-content">—</div>
                    </div>
                </div>
                <div class="modal-row">
                    <div class="modal-label"><i class="fas fa-tag"></i>Status</div>
                    <div class="modal-value" style="margin-left: 12px;">
                        <select id="mStatus" class="select-status">
                            <option value="agendado">Agendado</option>
                            <option value="confirmado">Confirmado</option>
                            <option value="concluido">Concluído</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn btn-primary" id="mSalvarStatus" style="font-weight: 500;">
                    <i class="fas fa-check btn-icon"></i>Salvar Status
                </button>
                <a class="btn btn-info" id="mEditar" style="font-weight: 500;">
                    <i class="fas fa-pen btn-icon"></i>Editar
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const filtro = document.getElementById('filtro-func');

            // URLs para editar e atualizar status (template :id)
            const editUrlTpl   = @json(route('agenda.edit', ':id'));
            const statusUrlTpl = @json(route('agenda.status.update', ':id'));
            const csrfToken    = @json(csrf_token());

            // Modal refs
            const modal = document.getElementById('eventModal');
            const mClose = document.getElementById('mClose');
            const mTitle = document.getElementById('mTitle');
            const mCliente = document.getElementById('mCliente');
            const mServico = document.getElementById('mServico');
            const mFuncionario = document.getElementById('mFuncionario');
            const mHorario = document.getElementById('mHorario');
            const mObservacoes = document.getElementById('mObservacoes');
            const mStatus = document.getElementById('mStatus');
            const mEditar = document.getElementById('mEditar');
            const mSalvarStatus = document.getElementById('mSalvarStatus');

            let currentEvent = null;

            // [TZ FIX] — retorna o offset local em formato +HH:MM / -HH:MM
            function getLocalTzOffsetStr(date = new Date()) {
                const offMin = -date.getTimezoneOffset(); // ex.: -180 -> +03:00
                const sign = offMin >= 0 ? '+' : '-';
                const abs = Math.abs(offMin);
                const hh = String(Math.floor(abs / 60)).padStart(2, '0');
                const mm = String(abs % 60).padStart(2, '0');
                return `${sign}${hh}:${mm}`;
            }

            // [TZ FIX] — se a string não tiver 'Z' nem '+HH:MM', injeta o offset local
            function ensureOffset(isoLike) {
                if (typeof isoLike !== 'string') return isoLike;
                const hasOffset = /Z|[+-]\d{2}:\d{2}$/.test(isoLike);
                if (hasOffset) return isoLike;
                // tenta normalizar 'YYYY-MM-DD HH:mm:ss' -> 'YYYY-MM-DDTHH:mm:ss'
                const tLike = isoLike.replace(' ', 'T');
                return `${tLike}${getLocalTzOffsetStr()}`;
            }

            function openModal(evt) {
                currentEvent = evt;
                mTitle.textContent = 'Detalhes do Agendamento';

                const extendedProps = evt.extendedProps || {};

                mCliente.textContent     = extendedProps.cliente_nome     || '—';
                mServico.textContent     = extendedProps.servico_nome     || '—';
                mFuncionario.textContent = extendedProps.funcionario_nome || '—';

                // [TZ FIX] — usa os Date já normalizados pelo Calendar
                const start = evt.start;
                const end   = evt.end;
                const hrFmt = (d) => d.toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit', hour12:false});
                const dtFmt = (d) => d.toLocaleDateString('pt-BR');
                mHorario.textContent = `${dtFmt(start)} ${hrFmt(start)} - ${hrFmt(end)}`;

                const observacoes = extendedProps.observacoes || '';
                mObservacoes.textContent = observacoes || 'Nenhuma observação';

                mStatus.value = extendedProps.status || 'agendado';
                mEditar.href = editUrlTpl.replace(':id', evt.id);

                modal.classList.add('active');
            }

            function closeModal(){
                modal.classList.remove('active');
                currentEvent = null;
            }

            document.getElementById('mClose').addEventListener('click', closeModal);
            modal.addEventListener('click', (e)=>{ if(e.target === modal) closeModal(); });

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                locale: 'pt-br',
                slotMinTime: '{{ $slotMinTime }}:00',
                slotMaxTime: '{{ $slotMaxTime }}:00',
                nowIndicator: true,
                allDaySlot: false,
                height: '90vh',
                timeZone: 'local',

                /* 4 linhas de 15 min por hora */
                slotDuration: '00:15:00',
                snapDuration: '00:15:00',
                slotLabelInterval: { hours: 1 },
                slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },

                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'timeGridWeek,timeGridDay,listWeek'
                },
                buttonText: {
                    today: 'Hoje',
                    week: 'Semana',
                    day: 'Dia',
                    list: 'Lista'
                },

                events: {
                    url: '{{ route('agenda.events') }}',
                    extraParams: function() {
                        return { funcionario_id: filtro.value || '' };
                    }
                },

                // [TZ FIX] — normaliza cada evento recebido
                eventDataTransform: function(raw) {
                    const copy = { ...raw };
                    // normaliza se vier sem offset
                    if (typeof copy.start === 'string') copy.start = ensureOffset(copy.start);
                    if (typeof copy.end   === 'string') copy.end   = ensureOffset(copy.end);

                    // garante que extendedProps exista
                    copy.extendedProps = copy.extendedProps || {};
                    if (!copy.extendedProps.status && copy.className) {
                        // fallback: tenta extrair pela classe st-*
                        const cls = Array.isArray(copy.className) ? copy.className : [copy.className];
                        const stClass = cls.find(c => /^st-/.test(c));
                        if (stClass) copy.extendedProps.status = stClass.replace(/^st-/, '');
                    }
                    return copy;
                },

                eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },

                eventClassNames: function(arg){
                    const st = (arg.event.extendedProps?.status || '').toLowerCase();
                    return st ? [`st-${st}`] : [];
                },

                eventDidMount: function(info){
                    info.el.addEventListener('dblclick', (e) => {
                        e.preventDefault();
                        openModal(info.event);
                    });
                },

                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                }
            });

            // troca status (AJAX)
            mSalvarStatus.addEventListener('click', async () => {
                if(!currentEvent) return;
                const id = currentEvent.id;
                const url = statusUrlTpl.replace(':id', id);

                try {
                    const resp = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ status: mStatus.value })
                    });
                    if(!resp.ok) throw new Error('Falha ao atualizar status');

                    currentEvent.setExtendedProp('status', mStatus.value);
                    calendar.refetchEvents();
                    closeModal();
                } catch (err) {
                    alert('Não foi possível atualizar o status. Tente novamente.');
                }
            });

            filtro.addEventListener('change', () => {
                const params = new URLSearchParams(window.location.search);
                if (filtro.value) params.set('funcionario_id', filtro.value);
                else params.delete('funcionario_id');
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.replaceState({}, '', newUrl);
                calendar.refetchEvents();
            });

            @if(!empty($selectedFuncionarioId))
                document.getElementById('filtro-func').value = '{{ $selectedFuncionarioId }}';
            @endif

            calendar.render();

            // settings dropdown
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
        });
    </script>
</body>
</html>
