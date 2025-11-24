@extends('layouts.app')

@section('title', 'Configurações da Agenda - Estética PRO')

@section('content')
    <style>
        :root{
            --primary:#ec4899; --primary-dark:#db2777; --primary-light:#fbcfe8;
            --secondary:#7e22ce; --text:#1f2937; --text-light:#6b7280;
            --success:#10b981; --warning:#f59e0b; --danger:#ef4444;
            --sidebar-width:260px;
        }
        *{margin:0;padding:0;box-sizing:border-box}

        .content{
            padding:11px;
            flex:1;
            display:flex;
            justify-content:center;
            align-items:flex-start;
        }

        .form-container{
            background:#fff;
            padding:30px;
            border-radius:16px;
            box-shadow:0 4px 20px rgba(0,0,0,.08);
            width:100%;
            max-width:900px;
        }

        .page-header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:30px
        }

        .page-title{
            font-size:24px;
            font-weight:700;
            background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);
            -webkit-background-clip:text;
            -webkit-text-fill-color:transparent
        }

        .back-link{
            display:flex;
            align-items:center;
            text-decoration:none;
            color:var(--text-light);
            font-weight:500;
            transition:.3s
        }
        .back-link:hover{color:var(--primary)}
        .back-link i{margin-right:8px}

        .section-title{
            font-size:18px;
            font-weight:600;
            color:var(--text);
            margin-bottom:8px;
        }

        .section-subtitle{
            font-size:13px;
            color:var(--text-light);
            margin-bottom:18px;
        }

        .form-grid{display:grid;grid-template-columns:1fr;gap:20px}
        .form-group{margin-bottom:16px;}
        label{display:block;font-weight:500;margin-bottom:8px;color:var(--text)}
        input,textarea,select{
            width:100%;
            padding:14px 16px;
            border:2px solid #e5e7eb;
            border-radius:12px;
            font-size:14px;
            transition:.2s;
            font-family:'Poppins',sans-serif
        }
        input:focus,textarea:focus,select:focus{
            border-color:var(--primary);
            outline:none;
            box-shadow:0 0 0 3px rgba(236,72,153,.2)
        }

        select[multiple]{ min-height:120px; }

        .time-inputs{display:flex;gap:20px;margin-bottom:15px}
        .time-group{flex:1}
        .hint-text{color:var(--text-light);font-size:13px;margin-bottom:12px;line-height:1.5}

        .form-actions{
            display:flex;
            justify-content:flex-end;
            margin-top:20px;
            gap:10px
        }
        .btn{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:12px 20px;
            border:none;
            border-radius:12px;
            font-weight:500;
            cursor:pointer;
            transition:.3s;
            font-size:14px;
            text-decoration:none
        }
        .btn-primary{
            background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);
            color:#fff;
            box-shadow:0 4px 14px rgba(236,72,153,.4)
        }
        .btn-primary:hover{
            transform:translateY(-2px);
            box-shadow:0 6px 20px rgba(236,72,153,.5)
        }
        .btn-light{
            background:#f3f4f6;
            color:var(--text);
            border:2px solid #e5e7eb
        }
        .btn-light:hover{background:#e5e7eb}
        .btn-danger{
            background:#fee2e2;
            color:#b91c1c;
            border:1px solid #fecaca;
        }
        .btn-danger:hover{
            background:#ef4444;
            color:#fff;
        }
        .btn-icon{margin-right:8px}

        .alert-success{
            background:#dcfce7;
            color:#166534;
            padding:12px 14px;
            border-radius:10px;
            border:1px solid #22c55e33;
            margin-bottom:20px;
            font-size:14px
        }
        .alert-error{
            background:#fee2e2;
            color:#b91c1c;
            padding:12px 14px;
            border-radius:10px;
            border:1px solid #ef444433;
            margin-bottom:20px;
            font-size:14px
        }
        .error-list{margin:0;padding-left:1.2rem}
        .error-list li{margin-bottom:4px}

        .section-divider{
            margin:24px 0;
            border-top:1px dashed #e5e7eb;
        }

        /* Checkbox customizado (mesmo estilo dos bloqueios) */
        .checkbox-group{
            display:flex;
            align-items:center;
            gap:12px;
        }
        .checkbox-custom{
            width:20px;
            height:20px;
            border:2px solid #e5e7eb;
            border-radius:6px;
            position:relative;
            cursor:pointer;
            transition:.2s;
        }
        .checkbox-custom.checked{
            background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);
            border-color:var(--primary);
        }
        .checkbox-custom.checked::after{
            content:"✓";
            position:absolute;
            color:#fff;
            font-size:13px;
            font-weight:700;
            top:50%;
            left:50%;
            transform:translate(-50%, -52%);
        }
        .checkbox-label{
            margin:0;
            cursor:pointer;
            font-weight:500;
            color:var(--text);
        }

        /* === DIAS DA SEMANA (bolinhas) === */
        .weekdays-wrapper{
            margin-top:8px;
            margin-bottom:8px;
        }
        .weekdays-label{
            font-weight:500;
            color:var(--text);
            margin-bottom:6px;
        }
        .weekdays-legend{
            font-size:12px;
            color:var(--text-light);
            margin-bottom:8px;
        }
        .weekdays{
            display:flex;
            gap:10px;
            flex-wrap:wrap;
        }
        .weekday-pill{
            position:relative;
            width:36px;
            height:36px;
            border-radius:999px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:600;
            font-size:14px;
            color:#6b7280;
            background:#f3f4f6;
            cursor:pointer;
            transition:.2s;
            box-shadow:0 1px 3px rgba(0,0,0,.05);
        }
        .weekday-pill input{
            position:absolute;
            opacity:0;
            pointer-events:none;
        }
        .weekday-pill.active{
            background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);
            color:#fff;
            box-shadow:0 3px 10px rgba(236,72,153,.4);
        }

        /* Tabela de exceções */
        .ex-table-wrapper{
            margin-top:10px;
            border-radius:12px;
            border:1px solid #e5e7eb;
            overflow:hidden;
        }
        table{
            width:100%;
            border-collapse:collapse;
            font-size:13px;
        }
        thead{
            background:#f9fafb;
        }
        th,td{
            padding:10px 12px;
            text-align:left;
            border-bottom:1px solid #f3f4f6;
        }
        th{
            font-weight:600;
            color:var(--text);
        }
        tbody tr:hover{
            background:#f9fafb;
        }
        .empty-row{
            text-align:center;
            font-style:italic;
            color:var(--text-light);
        }

        .badge-day{
            display:inline-flex;
            align-items:center;
            gap:6px;
            padding:4px 10px;
            border-radius:999px;
            background:#eef2ff;
            color:#4338ca;
            font-size:12px;
            font-weight:500;
        }
        .badge-day i{font-size:11px;}

        .small-text{
            font-size:12px;
            color:var(--text-light);
        }

        @media (max-width:768px){
            .form-container{padding:20px}
            .page-header{flex-direction:column;align-items:flex-start;gap:10px}
            .time-inputs{flex-direction:column}
            th,td{font-size:12px;padding:8px}
        }
    </style>

    <div class="content">
        <div class="form-container">
            <div class="page-header">
                <h1 class="page-title">Configurações da Agenda</h1>
                <a href="{{ route('agenda.index') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i> Voltar à agenda
                </a>
            </div>

            @if (session('success'))
                <div class="alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert-error">
                    <ul class="error-list">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- 1) CONFIGURAÇÕES GERAIS DE EXPEDIENTE --}}
            <form method="POST" action="{{ route('settings.update') }}">
                @csrf

                <div class="form-grid">
                    <div>
                        <div class="section-title">Expediente padrão</div>
                        <p class="section-subtitle">
                            Defina o horário padrão de funcionamento da clínica.  
                            Esses horários serão usados em todos os dias, <strong>exceto</strong> nos dias
                            em que você configurar um expediente especial.
                        </p>
                    </div>

                    <div class="time-inputs">
                        <div class="time-group">
                            <label for="expediente_inicio">Início do expediente *</label>
                            <input type="time" id="expediente_inicio" name="expediente_inicio"
                                   value="{{ old('expediente_inicio', $inicio) }}" required>
                        </div>
                        <div class="time-group">
                            <label for="expediente_fim">Fim do expediente *</label>
                            <input type="time" id="expediente_fim" name="expediente_fim"
                                   value="{{ old('expediente_fim', $fim) }}" required>
                        </div>
                    </div>

                    {{-- DIAS DA SEMANA (bolinhas D S T Q Q S S) --}}
                    @php
                        // 0=Dom,1=Seg,2=Ter,3=Qua,4=Qui,5=Sex,6=Sáb
                        $labels = ['D','S','T','Q','Q','S','S'];
                        $diasSelecionados = collect(old('dias_semana', $diasSemana ?? []))
                            ->map(fn($d) => (int)$d)
                            ->all();
                    @endphp

                    <div class="weekdays-wrapper">
                        <div class="weekdays-label">Dias de funcionamento padrão</div>
                        <div class="weekdays-legend">
                            <span style="font-weight:600;">Bolinhas coloridas:</span> dia com atendimento &nbsp;•&nbsp;
                            <span style="font-weight:600;">cinza:</span> fechado.
                        </div>
                        <div class="weekdays">
                            @foreach(range(0,6) as $i)
                                @php $checked = in_array($i, $diasSelecionados); @endphp
                                <label class="weekday-pill {{ $checked ? 'active' : '' }}">
                                    <input type="checkbox"
                                           name="dias_semana[]"
                                           value="{{ $i }}"
                                           {{ $checked ? 'checked' : '' }}>
                                    <span>{{ $labels[$i] }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="hint-text" style="margin-top:8px;">
                            Exemplo: se você marcar apenas <strong>S T Q Q S</strong>, significa que a clínica abre
                            de segunda a sexta e fica fechada no domingo e sábado (bolinhas cinza).
                        </p>
                    </div>

                    <p class="hint-text">
                        <i class="fas fa-lightbulb"></i>
                        Exemplo: se normalmente a clínica atende das <strong>08:00</strong> às <strong>18:00</strong>,
                        mas em um dia específico quiser ampliar o horário (ex.: das <strong>05:00</strong> às <strong>21:00</strong>),
                        use a seção de <strong>“Dias especiais de expediente”</strong> abaixo.
                    </p>
                </div>

                <div class="form-actions">
                    <a href="{{ route('agenda.index') }}" class="btn btn-light">
                        <i class="fas fa-times btn-icon"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save btn-icon"></i> Salvar Configurações
                    </button>
                </div>
            </form>

            {{-- DIVISOR --}}
            <div class="section-divider"></div>

            {{-- 2) DIAS ESPECIAIS DE EXPEDIENTE (mantido igual) --}}
            <div class="form-grid">
                <div>
                    <div class="section-title">Dias especiais de expediente</div>
                    <p class="section-subtitle">
                        Use esta seção para configurar dias específicos com horários diferentes do padrão. <br>
                        Exemplo: no dia <strong>20/11</strong> um ou mais profissionais atenderem das
                        <strong>05:00</strong> às <strong>21:00</strong>.
                    </p>
                </div>

                {{-- Formulário para adicionar nova exceção --}}
                <form method="POST" action="{{ route('settings.excecoes.store') }}" style="margin-bottom:10px;">
                    @csrf

                    {{-- Profissionais + aplicar a todos --}}
                    <div class="form-grid" style="margin-bottom:10px;">
                        <div class="form-group">
                            <label for="funcionarios_excecao">Profissionais</label>
                            <select id="funcionarios_excecao"
                                    name="funcionarios[]"
                                    multiple
                                    @if(old('aplicar_todos')) disabled @endif>
                                @php
                                    $oldFuncionarios = collect(old('funcionarios', []));
                                @endphp

                                @foreach($funcionarios as $f)
                                    <option value="{{ $f->id }}"
                                        @if($oldFuncionarios->contains($f->id)) selected @endif>
                                        {{ $f->nome }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="hint-text">
                                Segure CTRL (ou CMD no Mac) para selecionar mais de um profissional.
                                Se marcar "Aplicar a todos", não precisa selecionar.
                            </p>
                        </div>

                        <div class="form-group">
                            <label style="margin-bottom:4px;">Aplicar a todos</label>
                            <div class="checkbox-group">
                                <div class="checkbox-custom {{ old('aplicar_todos') ? 'checked' : '' }}"
                                     id="excecaoCustomCheckbox"></div>
                                <input type="checkbox"
                                       name="aplicar_todos"
                                       id="excecao_aplicar_todos"
                                       value="1"
                                       {{ old('aplicar_todos') ? 'checked' : '' }}
                                       style="display:none;">
                                <label for="excecao_aplicar_todos" class="checkbox-label">
                                    Aplicar a todos os profissionais
                                </label>
                            </div>
                            <p class="hint-text">
                                Marque esta opção quando o horário especial valer para a clínica inteira.
                            </p>
                        </div>
                    </div>

                    <div class="time-inputs">
                        <div class="time-group">
                            <label for="excecao_data">Data *</label>
                            <input type="date" id="excecao_data" name="data"
                                   value="{{ old('data') }}" required>
                        </div>
                        <div class="time-group">
                            <label for="excecao_inicio">Início especial *</label>
                            <input type="time" id="excecao_inicio" name="inicio"
                                   value="{{ old('inicio') }}" required>
                        </div>
                        <div class="time-group">
                            <label for="excecao_fim">Fim especial *</label>
                            <input type="time" id="excecao_fim" name="fim"
                                   value="{{ old('fim') }}" required>
                        </div>
                    </div>

                    <p class="hint-text">
                        O horário informado aqui <strong>substitui</strong> o expediente padrão somente neste dia,
                        para os profissionais selecionados (ou para todos, se marcado).
                    </p>

                    <div class="form-actions" style="justify-content:flex-start;margin-top:0;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus btn-icon"></i> Adicionar dia especial
                        </button>
                    </div>
                </form>

                {{-- Lista de exceções existentes --}}
                <div>
                    <div class="hint-text" style="margin-bottom:8px;">
                        <strong>Dias especiais cadastrados</strong>
                    </div>

                    <div class="ex-table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Horário especial</th>
                                    <th>Profissionais</th>
                                    <th width="120">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($excecoes as $ex)
                                    <tr>
                                        <td>
                                            <span class="badge-day">
                                                <i class="fas fa-calendar-day"></i>
                                                {{ $ex->data->format('d/m/Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::createFromFormat('H:i:s', $ex->inicio)->format('H:i') }}
                                            &nbsp;–&nbsp;
                                            {{ \Carbon\Carbon::createFromFormat('H:i:s', $ex->fim)->format('H:i') }}
                                            <div class="small-text">
                                                Substitui o expediente padrão neste dia.
                                            </div>
                                        </td>
                                        <td>
                                            @if($ex->aplicar_todos)
                                                <span class="badge-day" style="background:#ecfeff;color:#0369a1;">
                                                    <i class="fas fa-users"></i>
                                                    Todos os profissionais
                                                </span>
                                            @else
                                                @if($ex->funcionarios->isEmpty())
                                                    <span class="small-text">Nenhum profissional vinculado.</span>
                                                @else
                                                    @foreach($ex->funcionarios as $f)
                                                        <span class="badge-day" style="margin-bottom:4px;">
                                                            <i class="fas fa-user"></i> {{ $f->nome }}
                                                        </span>
                                                    @endforeach
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            <form method="POST"
                                                  action="{{ route('settings.excecoes.destroy', $ex->id) }}"
                                                  onsubmit="return confirm('Remover este dia especial de expediente?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fas fa-trash-alt btn-icon"></i> Remover
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="empty-row">
                                            Nenhum dia especial configurado no momento.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <p class="hint-text" style="margin-top:10px;">
                        Dica: use esta funcionalidade para datas comemorativas, ações promocionais ou dias com plantão em horários estendidos,
                        podendo limitar a quais profissionais esse plantão se aplica.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Validação básica para expediente padrão
            const inicioInput = document.getElementById('expediente_inicio');
            const fimInput = document.getElementById('expediente_fim');

            function validateTimes() {
                const inicio = inicioInput.value;
                const fim = fimInput.value;

                if (inicio && fim && inicio >= fim) {
                    fimInput.setCustomValidity('O fim do expediente deve ser após o início');
                } else {
                    fimInput.setCustomValidity('');
                }
            }

            inicioInput.addEventListener('change', validateTimes);
            fimInput.addEventListener('change', validateTimes);

            // Validação simples do form de dia especial
            const exInicio = document.getElementById('excecao_inicio');
            const exFim    = document.getElementById('excecao_fim');

            function validateExcecaoTimes() {
                if (!exInicio || !exFim) return;
                const i = exInicio.value;
                const f = exFim.value;
                if (i && f && i >= f) {
                    exFim.setCustomValidity('O fim do expediente especial deve ser após o início');
                } else {
                    exFim.setCustomValidity('');
                }
            }

            if (exInicio && exFim) {
                exInicio.addEventListener('change', validateExcecaoTimes);
                exFim.addEventListener('change', validateExcecaoTimes);
            }

            // Lógica do "Aplicar a todos" x multi-select de profissionais
            const chkEx    = document.getElementById('excecao_aplicar_todos');
            const customEx = document.getElementById('excecaoCustomCheckbox');
            const selEx    = document.getElementById('funcionarios_excecao');

            if (chkEx && customEx && selEx) {
                function toggleSelectEx() {
                    selEx.disabled = chkEx.checked;
                    if (chkEx.checked) {
                        for (let o of selEx.options) {
                            o.selected = false;
                        }
                    }
                }

                function updateCustomEx() {
                    if (chkEx.checked) {
                        customEx.classList.add('checked');
                    } else {
                        customEx.classList.remove('checked');
                    }
                }

                customEx.addEventListener('click', function() {
                    chkEx.checked = !chkEx.checked;
                    toggleSelectEx();
                    updateCustomEx();
                });

                chkEx.addEventListener('change', function() {
                    toggleSelectEx();
                    updateCustomEx();
                });

                // inicializa estado
                toggleSelectEx();
                updateCustomEx();
            }

            // === Bolinhas dos dias da semana ===
            document.querySelectorAll('.weekday-pill input').forEach(function (chk) {
                chk.addEventListener('change', function () {
                    const pill = this.closest('.weekday-pill');
                    if (!pill) return;
                    if (this.checked) {
                        pill.classList.add('active');
                    } else {
                        pill.classList.remove('active');
                    }
                });
            });
        });
    </script>
@endsection
