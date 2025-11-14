@extends('layouts.app')

@section('title', 'Agenda - Estética PRO')

@section('content')
    {{-- ===== ESTILOS ESPECÍFICOS DA PÁGINA (inline) ===== --}}
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #ec4899; --secondary: #7e22ce; --text: #1f2937; --text-light: #6b7280;
            --success: #10b981; --warning: #f59e0b; --danger: #ef4444;
        }

        .content { padding: 11px; }

        /* Header IDÊNTICO ao do Dashboard - MESMA ALTURA E TAMANHO */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;

        }

        .page-title {
            font-size: 28px !important;
            font-weight: 700 !important;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
            line-height: 1.2;
        }

        .header-actions { 
            display: flex; 
            gap: 10px; 
            flex-wrap: wrap;
            align-items: center;
        }

        .btn {
            text-decoration: none;
            padding: 12px 16px;
            border-radius: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: #fff;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: var(--text);
        }

        .btn-info {
            background: #0EA5E9;
            color: #fff;
        }

        .btn-icon { 
            margin-right: 8px; 
            font-size: 14px;
        }

        .filter-section { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            flex-wrap: wrap; 
        }

        .filter-label { 
            font-weight: 500; 
            color: var(--text); 
            white-space: nowrap; 
            font-size: 14px;
        }

        .filter-select { 
            padding: 10px 12px;
            border: 2px solid #f3d1e5;
            border-radius: 12px;
            font-size: 14px;
            min-width: 180px;
            background: #fff;
            font-family: 'Poppins', sans-serif;
            height: 44px;
            box-sizing: border-box;
        }

        .alert { 
            padding: 16px; 
            border-radius: 12px; 
            margin-bottom: 20px; 
            display: flex; 
            align-items: center; 
            gap: 12px; 
        }

        .alert-success { 
            background: #ecfdf5; 
            color: #065f46; 
            border: 1px solid #a7f3d0; 
        }

        .calendar-container { 
            background: #fff; 
            border-radius: 16px; 
            overflow: hidden; 
            box-shadow: 0 4px 20px rgba(0,0,0,.05); 
            margin-bottom: 30px; 
            padding: 20px; 
            height: 90vh; 
        }

        #calendar { height: 100%; }

        .fc { font-family: 'Poppins', sans-serif; }
        .fc-toolbar { flex-wrap: wrap; gap: 15px; }
        .fc-toolbar-title { font-size: 1.3rem; font-weight: 600; color: var(--text); }
        .fc-button { background: #fff; border: 1px solid #d1d5db; color: var(--text); font-weight: 500; padding: 8px 16px; border-radius: 8px; transition: all .3s ease; }
        .fc-button:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
        .fc-button-primary:not(:disabled).fc-button-active { background: var(--primary); border-color: var(--primary); }
        .fc-col-header-cell { background: #f8fafc; padding: 12px 0; font-weight: 600; color: var(--text); border-color: #e5e7eb; }
        .fc-day-today { background-color: rgba(236,72,153,.1) !important; }
        .fc-event .fc-event-main { color: #fff; }
        .fc-event.st-agendado { background: #3b82f6 !important; border-color: #3b82f6 !important; }
        .fc-event.st-confirmado { background: #10b981 !important; border-color: #10b981 !important; }
        .fc-event.st-concluido { background: #7e22ce !important; border-color: #7e22ce !important; }
        .fc-event.st-cancelado { background: #ef4444 !important; border-color: #ef4444 !important; }

        .modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.5); display: none; align-items: center; justify-content: center; z-index: 2000; backdrop-filter: blur(4px); }
        .modal-backdrop.active { display: flex; }
        .modal-card { width: 95%; max-width: 500px; background: #fff; border-radius: 20px; padding: 0; box-shadow: 0 25px 50px rgba(0,0,0,.3); overflow: hidden; }
        .modal-header { background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); padding: 24px; color: #fff; position: relative; }
        .modal-title { font-size: 20px; font-weight: 700; margin: 0; }
        .modal-close { position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,.2); border: none; width: 36px; height: 36px; border-radius: 50%; color: #fff; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .3s ease; }
        .modal-close:hover { background: rgba(255,255,255,.3); transform: rotate(90deg); }
        .modal-body { padding: 24px; }
        .modal-row { display: flex; align-items: center; padding: 16px 0; border-bottom: 1px solid #f3f4f6; }
        .modal-row:last-child { border-bottom: none; }
        .modal-label { width: 120px; font-weight: 600; color: var(--text); display: flex; align-items: center; gap: 8px; }
        .modal-label i { color: var(--primary); width: 16px; }
        .modal-value { flex: 1; color: var(--text); font-weight: 500; display: flex; align-items: center; }
        .select-status { width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 12px; font-family: 'Poppins', sans-serif; font-size: 14px; background: #fff; transition: all .3s ease; }
        .select-status:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(236,72,153,.2); }
        .modal-actions { display: flex; gap: 12px; padding: 20px 24px; background: #f9fafb; border-top: 1px solid #e5e7eb; }
        .modal-actions .btn { flex: 1; justify-content: center; padding: 12px 16px; font-weight: 500; font-size: 14px; }

        @media (max-width: 768px) {
            .page-header { flex-direction: column; align-items: flex-start; gap: 16px; }
            .header-actions { width: 100%; justify-content: space-between; }
            .filter-section { width: 100%; }
            .filter-select { flex: 1; min-width: auto; }
            .modal-card { margin: 20px; width: calc(100% - 40px); }
            .modal-row { flex-direction: column; gap: 8px; align-items: flex-start; }
            .modal-label { width: 100%; }
            .modal-actions { flex-direction: column; }
        }
    </style>

    <div class="content">
        {{-- Cabeçalho IDÊNTICO ao do Dashboard - MESMA ALTURA E TAMANHO --}}
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
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="{{ route('agenda.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus btn-icon"></i> Novo Agendamento
                    </a>
                    <a href="{{ route('agenda.bloqueios.index') }}" class="btn btn-secondary">
                        <i class="fas fa-lock btn-icon"></i> Bloqueios
                    </a>
                    <a href="{{ route('settings.edit') }}" class="btn btn-secondary">
                        <i class="fas fa-cog btn-icon"></i> Configurações
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

    {{-- Modal (detalhes + status + editar) --}}
    <div class="modal-backdrop" id="eventModal">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title" id="mTitle">Detalhes do Agendamento</div>
                <button class="modal-close" id="mClose">&times;</button>
            </div>
            <div class="modal-body">
                <div class="modal-row">
                    <div class="modal-label"><i class="fas fa-user"></i>Cliente</div>
                    <div class="modal-value" id="mCliente" style="margin-left:12px;">—</div>
                </div>
                <div class="modal-row">
                    <div class="modal-label"><i class="fas fa-scissors"></i>Serviço</div>
                    <div class="modal-value" id="mServico" style="margin-left:12px;">—</div>
                </div>
                <div class="modal-row">
                    <div class="modal-label"><i class="fas fa-users"></i>Funcionário</div>
                    <div class="modal-value" id="mFuncionario" style="margin-left:12px;">—</div>
                </div>
                <div class="modal-row">
                    <div class="modal-label"><i class="fas fa-clock"></i>Horário</div>
                    <div class="modal-value" id="mHorario" style="margin-left:12px;">—</div>
                </div>
                <div class="modal-row">
                    <div class="modal-label"><i class="fas fa-sticky-note"></i>Observações</div>
                    <div class="modal-value" style="margin-left:12px;">
                        <div id="mObservacoes" class="observacoes-content">—</div>
                    </div>
                </div>
                <div class="modal-row">
                    <div class="modal-label"><i class="fas fa-tag"></i>Status</div>
                    <div class="modal-value" style="margin-left:12px;">
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
                <button class="btn btn-primary" id="mSalvarStatus" style="font-weight:500;">
                    <i class="fas fa-check btn-icon"></i>Salvar Status
                </button>
                <a class="btn btn-info" id="mEditar" style="font-weight:500;">
                    <i class="fas fa-pen btn-icon"></i>Editar
                </a>
            </div>
        </div>
    </div>

    {{-- Partials opcionais --}}
    @include('partials.change_password_modal')
    @include('partials.toast')

    {{-- ===== SCRIPTS ESPECÍFICOS DA PÁGINA (inline) ===== --}}
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const filtro = document.getElementById('filtro-func');
            const editUrlTpl   = @json(route('agenda.edit', ':id'));
            const statusUrlTpl = @json(route('agenda.status.update', ':id'));
            const csrfToken    = @json(csrf_token());
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

            function getLocalTzOffsetStr(date = new Date()) {
                const offMin = -date.getTimezoneOffset();
                const sign = offMin >= 0 ? '+' : '-';
                const abs = Math.abs(offMin);
                const hh = String(Math.floor(abs / 60)).padStart(2, '0');
                const mm = String(abs % 60).padStart(2, '0');
                return `${sign}${hh}:${mm}`;
            }
            function ensureOffset(isoLike) {
                if (typeof isoLike !== 'string') return isoLike;
                const hasOffset = /Z|[+-]\d{2}:\d{2}$/.test(isoLike);
                if (hasOffset) return isoLike;
                const tLike = isoLike.replace(' ', 'T');
                return `${tLike}${getLocalTzOffsetStr()}`;
            }
            function openModal(evt) {
                currentEvent = evt;
                mTitle.textContent = 'Detalhes do Agendamento';
                const xp = evt.extendedProps || {};
                mCliente.textContent     = xp.cliente_nome     || '—';
                mServico.textContent     = xp.servico_nome     || '—';
                mFuncionario.textContent = xp.funcionario_nome || '—';
                const start = evt.start, end = evt.end;
                const hrFmt = (d) => d.toLocaleTimeString('pt-BR', {hour:'2-digit', minute:'2-digit', hour12:false});
                const dtFmt = (d) => d.toLocaleDateString('pt-BR');
                mHorario.textContent = `${dtFmt(start)} ${hrFmt(start)} - ${hrFmt(end)}`;
                mObservacoes.textContent = (xp.observacoes || '') || 'Nenhuma observação';
                mStatus.value = xp.status || 'agendado';
                mEditar.href = editUrlTpl.replace(':id', evt.id);
                modal.classList.add('active');
            }
            function closeModal(){ modal.classList.remove('active'); currentEvent = null; }
            mClose.addEventListener('click', closeModal);
            modal.addEventListener('click', (e)=>{ if(e.target === modal) closeModal(); });

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek', locale:'pt-br', timeZone:'local', nowIndicator:true, allDaySlot:false,
                slotMinTime: '{{ $slotMinTime }}:00', slotMaxTime: '{{ $slotMaxTime }}:00', height:'90vh',
                slotDuration:'00:15:00', snapDuration:'00:15:00', slotLabelInterval:{hours:1},
                slotLabelFormat:{hour:'2-digit', minute:'2-digit', hour12:false},
                headerToolbar:{ left:'prev,next today', center:'title', right:'timeGridWeek,timeGridDay,listWeek' },
                buttonText:{ today:'Hoje', week:'Semana', day:'Dia', list:'Lista' },
                events:{ url:'{{ route('agenda.events') }}', extraParams:()=>({ funcionario_id: filtro.value || '' }) },
                eventDataTransform: function(raw){
                    const copy = {...raw};
                    if (typeof copy.start==='string') copy.start = ensureOffset(copy.start);
                    if (typeof copy.end==='string') copy.end = ensureOffset(copy.end);
                    copy.extendedProps = copy.extendedProps || {};
                    if (!copy.extendedProps.status && copy.className){
                        const cls = Array.isArray(copy.className) ? copy.className : [copy.className];
                        const st = cls.find(c=>/^st-/.test(c));
                        if (st) copy.extendedProps.status = st.replace(/^st-/, '');
                    }
                    return copy;
                },
                eventTimeFormat:{ hour:'2-digit', minute:'2-digit', hour12:false },
                eventClassNames:(arg)=>{ const st=(arg.event.extendedProps?.status||'').toLowerCase(); return st?[`st-${st}`]:[]; },
                eventDidMount:(info)=>{ info.el.addEventListener('dblclick', (e)=>{ e.preventDefault(); openModal(info.event); }); },
                eventClick:(info)=>{ info.jsEvent.preventDefault(); }
            });

            mSalvarStatus.addEventListener('click', async ()=>{
                if(!currentEvent) return;
                const id = currentEvent.id; const url = statusUrlTpl.replace(':id', id);
                try {
                    const resp = await fetch(url, { method:'POST', headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN':csrfToken, 'Accept':'application/json' }, body:JSON.stringify({ status:mStatus.value }) });
                    if(!resp.ok) throw new Error('Falha ao atualizar status');
                    currentEvent.setExtendedProp('status', mStatus.value);
                    calendar.refetchEvents(); closeModal();
                } catch (err) { alert('Não foi possível atualizar o status. Tente novamente.'); }
            });

            filtro.addEventListener('change', ()=>{
                const p=new URLSearchParams(window.location.search);
                if(filtro.value) p.set('funcionario_id', filtro.value); else p.delete('funcionario_id');
                const newUrl=`${window.location.pathname}?${p.toString()}`;
                window.history.replaceState({}, '', newUrl);
                calendar.refetchEvents();
            });

            @if(!empty($selectedFuncionarioId))
                document.getElementById('filtro-func').value = '{{ $selectedFuncionarioId }}';
            @endif
            calendar.render();
        });
    </script>
@endsection