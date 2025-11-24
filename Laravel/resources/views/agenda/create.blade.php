@extends('layouts.app')

@section('title', isset($agenda) ? 'Editar Agendamento - Estética PRO' : 'Novo Agendamento - Estética PRO')

@section('content')
    <div class="form-container"
         style="background:white; padding:30px; border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,0.08); max-width:900px; margin:auto;">
        <div class="page-header"
             style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <h1 class="page-title"
                style="font-size:28px; font-weight:700; background:linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">
                {{ isset($agenda) ? 'Editar Agendamento' : 'Novo Agendamento' }}
            </h1>
            <a href="{{ $isFuncionario ? route('minha.agenda') : route('agenda.index') }}" class="back-link"
               style="text-decoration:none; color:var(--text-light); font-weight:500;">
                <i class="fas fa-arrow-left"></i> Voltar à agenda
            </a>
        </div>

        @if ($errors->any())
            <div
                style="background:#fef2f2; color:var(--danger); padding:16px; border-radius:12px; margin-bottom:20px; border:1px solid #fecaca;">
                <strong>Corrija os seguintes erros:</strong>
                <ul style="margin-left:20px; margin-top:8px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            use Carbon\Carbon;
            $isEdit         = isset($agenda);
            $action         = $isEdit ? route('agenda.update', $agenda->id) : route('agenda.store');
            $selFuncionario = old('funcionario_id', $isEdit ? $agenda->funcionario_id : '');
            $selCliente     = old('cliente_id', $isEdit ? $agenda->cliente_id : '');
            $selServico     = old('servico_id', $isEdit ? $agenda->servico_id : '');
            $dataValue      = old('data', $isEdit ? Carbon::parse($agenda->inicio)->format('Y-m-d') : '');
            $horaValue      = old('hora', $isEdit ? Carbon::parse($agenda->inicio)->format('H:i') : '');
            $obsValue       = old('observacoes', $isEdit ? ($agenda->observacoes ?? '') : '');
            $statusValue    = old('status', $isEdit ? ($agenda->status ?? 'agendado') : 'agendado');
            $sessionUser    = session('usuario');
            $isAdmin        = $sessionUser && strtolower($sessionUser->role ?? '') === 'admin';
            
            // 🔥 Se for funcionário, preenche automaticamente o funcionário
            if ($isFuncionario && !$isEdit) {
                $funcionario = DB::table('funcionarios')->where('cpf', $sessionUser->cpf)->first();
                if ($funcionario) {
                    $selFuncionario = $funcionario->id;
                }
            }

            // 🔥 NOVO: Se for FUNCIONÁRIO e só tiver 1 serviço, já seleciona ele no select
            if ($isFuncionario && !$isEdit && isset($servicos) && count($servicos) === 1 && !$selServico) {
                $selServico = $servicos[0]->id;
            }
        @endphp

        {{-- FORM PRINCIPAL (criar/editar) --}}
        <form method="POST" action="{{ $action }}" id="agendaForm">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

                {{-- 1) SERVIÇO PRIMEIRO --}}
                <div class="form-group">
                    <label for="servico_id" style="font-weight:500;">Serviço *</label>
                    <select id="servico_id" name="servico_id" required
                            style="width:100%; padding:14px 16px; border:2px solid #e5e7eb; border-radius:12px;"
                            {{ $isFuncionario && count($servicos) === 1 ? 'disabled' : '' }}>
                        <option value="">Selecione...</option>
                        @foreach($servicos as $s)
                            {{-- Esconde serviços inativos, se a coluna existir --}}
                            @if(isset($s->ativo) && !$s->ativo)
                                @continue
                            @endif
                            <option value="{{ $s->id }}"
                                {{ (string) $selServico === (string) $s->id ? 'selected' : '' }}>
                                {{ $s->nome }} ({{ $s->duracao_minutos ?? 30 }} min)
                            </option>
                        @endforeach
                    </select>
                    @if($isFuncionario && count($servicos) === 1)
                        <input type="hidden" name="servico_id" value="{{ $servicos[0]->id }}">
                        <small style="color:var(--text-light); display:block; margin-top:4px;">
                            Este é o único serviço que você realiza
                        </small>
                    @endif
                </div>

                {{-- 2) FUNCIONÁRIO - COMPORTAMENTO DIFERENTE PARA FUNCIONÁRIO --}}
                <div class="form-group">
                    <label for="funcionario_id" style="font-weight:500;">Funcionário *</label>
                    @if($isFuncionario)
                        {{-- FUNCIONÁRIO: campo readonly/disabled com apenas ele mesmo --}}
                        @php
                            $funcionarioAtual = $funcionarios->first();
                        @endphp
                        <input type="text" 
                               value="{{ $funcionarioAtual->nome ?? 'Você' }}" 
                               disabled
                               style="width:100%; padding:14px 16px; border:2px solid #e5e7eb; border-radius:12px; background:#f9fafb;">
                        <input type="hidden" name="funcionario_id" value="{{ $funcionarioAtual->id ?? '' }}">
                        <small style="color:var(--text-light); display:block; margin-top:4px;">
                            Agendamento para você mesmo
                        </small>
                    @else
                        {{-- ADMIN: select normal com AJAX --}}
                        <select id="funcionario_id" name="funcionario_id" required
                                style="width:100%; padding:14px 16px; border:2px solid #e5e7eb; border-radius:12px;">
                            <option value="">Selecione...</option>
                            @foreach($funcionarios as $f)
                                <option value="{{ $f->id }}"
                                    {{ (string) $selFuncionario === (string) $f->id ? 'selected' : '' }}>
                                    {{ $f->nome }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                {{-- 3) CLIENTE (Select2 AJAX) --}}
                <div class="form-group" style="grid-column:1 / -1;">
                    <label for="cliente_id" style="font-weight:500;">Cliente *</label>
                    <select id="cliente_id" name="cliente_id" required class="js-clientes-ajax" style="width:100%;">
                        @if($selCliente)
                            <option value="{{ $selCliente }}" selected>
                                {{ $clientes->firstWhere('id', $selCliente)->nome ?? 'Selecionado' }}
                            </option>
                        @endif
                    </select>
                </div>

                <div class="form-group">
                    <label for="data" style="font-weight:500;">Data *</label>
                    <input
                        type="date"
                        id="data"
                        name="data"
                        value="{{ $dataValue }}"
                        required
                        max="2100-12-31"
                        style="width:100%; padding:14px 16px; border:2px solid #e5e7eb; border-radius:12px;"
                    >
                </div>

                <div class="form-group">
                    <label for="hora" style="font-weight:500;">Hora (início) *</label>
                    <input type="time" id="hora" name="hora" value="{{ $horaValue }}" required
                           style="width:100%; padding:14px 16px; border:2px solid #e5e7eb; border-radius:12px;">
                </div>

                <div class="form-group">
                    <label for="status" style="font-weight:500;">Status</label>
                    <select id="status" name="status"
                            style="width:100%; padding:14px 16px; border:2px solid #e5e7eb; border-radius:12px;">
                        <option value="agendado"   {{ $statusValue === 'agendado'   ? 'selected' : '' }}>Agendado</option>
                        <option value="confirmado" {{ $statusValue === 'confirmado' ? 'selected' : '' }}>Confirmado</option>
                        <option value="concluido"  {{ $statusValue === 'concluido'  ? 'selected' : '' }}>Concluído</option>
                        <option value="cancelado"  {{ $statusValue === 'cancelado'  ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>

                <div class="form-group" style="grid-column:1 / -1;">
                    <label for="observacoes" style="font-weight:500;">Observações (opcional)</label>
                    <textarea id="observacoes" name="observacoes" rows="3" placeholder="Observações sobre o agendamento"
                              style="width:100%; padding:14px 16px; border:2px solid #e5e7eb; border-radius:12px;">{{ $obsValue }}</textarea>
                </div>
            </div>

            <div class="form-actions" style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
                <a href="{{ $isFuncionario ? route('minha.agenda') : route('agenda.index') }}" class="btn btn-secondary"
                   style="background:#f3f4f6; padding:14px 24px; border-radius:12px; text-decoration:none; color:var(--text); font-weight:500;">
                    <i class="fas fa-times btn-icon" style="margin-right:8px;"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary"
                        style="background:linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color:white; padding:14px 24px; border-radius:12px; font-weight:500; border:none; cursor:pointer;">
                    <i class="fas fa-save btn-icon" style="margin-right:8px;"></i>
                    {{ $isEdit ? 'Salvar Alterações' : 'Salvar Agendamento' }}
                </button>
            </div>
        </form>

        {{-- BOTÃO + FORM EXCLUIR (apenas ADMIN e somente em edição) --}}
        @if($isEdit && $isAdmin)
            <div style="margin-top:20px; display:flex; justify-content:flex-start;">
                <button type="button"
                        onclick="document.getElementById('modalExcluir').style.display='flex'"
                        style="
                            background:#ef4444;
                            color:white;
                            padding:12px 20px;
                            border-radius:12px;
                            border:none;
                            font-weight:600;
                            cursor:pointer;
                            display:inline-flex;
                            align-items:center;
                        ">
                    <i class="fas fa-trash-alt btn-icon" style="margin-right:8px;"></i>
                    Excluir Agendamento
                </button>
            </div>

            {{-- Form oculto que será enviado ao confirmar --}}
            <form id="formExcluirAgenda" method="POST" action="{{ route('agenda.destroy', $agenda->id) }}" style="display:none;">
                @csrf
                @method('DELETE')
            </form>

            {{-- MODAL DE CONFIRMAÇÃO --}}
            <div id="modalExcluir"
                 style="
                    display:none;
                    position:fixed;
                    inset:0;
                    background:rgba(0,0,0,0.55);
                    backdrop-filter:blur(3px);
                    justify-content:center;
                    align-items:center;
                    z-index:9999;
                 ">

                <div style="
                    background:white;
                    padding:32px;
                    border-radius:18px;
                    width:100%;
                    max-width:420px;
                    text-align:center;
                    box-shadow:0 6px 30px rgba(0,0,0,0.18);
                    animation:fadeIn .25s ease-out;
                ">
                    <h2 style="font-size:22px; font-weight:700; color:#1f2937; margin-bottom:10px;">
                        Confirmar exclusão?
                    </h2>
                    <p style="font-size:15px; color:#6b7280; margin-bottom:25px;">
                        Tem certeza que deseja excluir este agendamento? <br>
                        <strong>Esta ação não poderá ser desfeita.</strong>
                    </p>

                    <div style="display:flex; justify-content:center; gap:12px;">
                        <button type="button"
                                onclick="document.getElementById('modalExcluir').style.display='none'"
                                style="
                                    padding:10px 20px;
                                    background:#f3f4f6;
                                    border-radius:10px;
                                    font-weight:600;
                                    border:none;
                                    cursor:pointer;
                                    color:#374151;
                                ">
                            Cancelar
                        </button>

                        <button type="button"
                                onclick="document.getElementById('formExcluirAgenda').submit();"
                                style="
                                    padding:10px 20px;
                                    background:#ef4444;
                                    border-radius:10px;
                                    font-weight:600;
                                    border:none;
                                    cursor:pointer;
                                    color:white;
                                ">
                            Excluir
                        </button>
                    </div>
                </div>
            </div>

            <style>
                @keyframes fadeIn {
                    from { opacity:0; transform:scale(.95); }
                    to   { opacity:1; transform:scale(1); }
                }
            </style>
        @endif
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>

    <style>
        .select2-container--default .select2-selection--single{
            height: 48px; border:2px solid #f3d1e5; border-radius:12px;
            display:flex; align-items:center; padding:6px 12px; background:#fff;
            transition: all .25s ease;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered{
            line-height:32px; font-family:'Poppins', sans-serif; color:var(--text);
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow{ height:100%; right:10px; }
        .select2-container--default .select2-selection--single:focus,
        .select2-container--default.select2-container--open .select2-selection--single{
            outline:none; border-color:var(--primary); box-shadow:0 0 0 4px rgba(236,72,153,.15);
        }
        .select2-dropdown{ border:2px solid #f3d1e5; border-radius:12px; overflow:hidden; box-shadow:0 10px 24px rgba(0,0,0,.08); }
        .select2-results__option--highlighted{ background:#fde7f3 !important; color:var(--text) !important; }
        .select2-results__option[aria-selected=true]{ background:#f9e0ef !important; color:var(--text) !important; }
    </style>

    <script>
        $(function () {
            // ---- Select2 AJAX para CLIENTES ----
            $('.js-clientes-ajax').select2({
                placeholder: 'Digite para buscar...',
                allowClear: true,
                width: '100%',
                minimumInputLength: 2,
                ajax: {
                    url: '{{ route('clientes.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({ q: params.term, page: params.page || 1 }),
                    processResults: (data, params) => {
                        params.page = params.page || 1;
                        return {
                            results: data.data,
                            pagination: { more: data.more }
                        };
                    },
                    cache: true
                },
                language: {
                    inputTooShort: () => 'Digite pelo menos 2 caracteres',
                    noResults: () => 'Nenhum cliente encontrado',
                    searching: () => 'Buscando...'
                }
            });

            // ---------- FUNCIONÁRIOS x SERVIÇO via AJAX (APENAS PARA ADMIN) ----------
            @if(!$isFuncionario)
                const selectServico         = $('#servico_id');
                const selectFuncionario     = $('#funcionario_id');
                const selFuncionarioInicial = @json((string)$selFuncionario);
                const selServicoInicial     = @json((string)$selServico);

                function carregarFuncionarios(servicoId, callback) {
                    selectFuncionario.prop('disabled', true);
                    selectFuncionario.html('<option value="">Carregando...</option>');

                    if (!servicoId) {
                        selectFuncionario.html('<option value="">Selecione...</option>');
                        selectFuncionario.prop('disabled', false);
                        if (typeof callback === 'function') callback();
                        return;
                    }

                    $.ajax({
                        url: '{{ route('agenda.funcionarios_por_servico') }}',
                        data: { servico_id: servicoId },
                        dataType: 'json',
                        success: function (data) {
                            selectFuncionario.empty();
                            selectFuncionario.append('<option value="">Selecione...</option>');

                            if (!data || !data.length) {
                                selectFuncionario.append('<option value="">Nenhum profissional cadastrado para este serviço</option>');
                            } else {
                                data.forEach(function (f) {
                                    const selected = (String(f.id) === selFuncionarioInicial) ? 'selected' : '';
                                    selectFuncionario.append(
                                        '<option value="' + f.id + '" ' + selected + '>' + f.nome + '</option>'
                                    );
                                });
                            }

                            selectFuncionario.prop('disabled', false);

                            if (typeof callback === 'function') {
                                callback();
                            }
                        },
                        error: function () {
                            selectFuncionario.html('<option value="">Erro ao carregar profissionais</option>');
                            selectFuncionario.prop('disabled', false);
                        }
                    });
                }

                // Quando mudar o serviço, recarregar funcionários (APENAS ADMIN)
                selectServico.on('change', function () {
                    const servicoId = $(this).val();
                    // ao trocar o serviço manualmente, limpa seleção de funcionário
                    selectFuncionario.val('');
                    carregarFuncionarios(servicoId);
                });

                // Preencher na carga inicial (edição ou quando volta com erro de validação) - APENAS ADMIN
                if (selServicoInicial) {
                    carregarFuncionarios(selServicoInicial, function () {
                        if (selFuncionarioInicial) {
                            selectFuncionario.val(selFuncionarioInicial);
                        }
                    });
                }
            @endif

            // ---------- Validações de data/hora ----------
            const dataInput = document.getElementById('data');
            const horaInput = document.getElementById('hora');
            const form      = document.getElementById('agendaForm');

            form.addEventListener('submit', function(e) {
                // ano válido
                if (dataInput.value) {
                    const partes = dataInput.value.split('-');
                    if (partes.length === 3) {
                        const anoStr = partes[0];
                        const ano    = parseInt(anoStr, 10);
                        if (!anoStr || anoStr.length !== 4 || isNaN(ano) || ano < 1900 || ano > 2100) {
                            e.preventDefault();
                            alert('Ano inválido. Use um ano com 4 dígitos entre 1900 e 2100.');
                            dataInput.focus();
                            return;
                        }
                    }
                }

                const agora = new Date();

                const hora = horaInput.value;
                if (hora && !/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/.test(hora)) {
                    e.preventDefault();
                    alert('Formato de hora inválido. Use HH:MM (24 horas).');
                    horaInput.focus();
                    return;
                }

                @if(!$isEdit)
                    if (dataInput.value && horaInput.value) {
                        const dataHoraAgendamento = new Date(dataInput.value + 'T' + horaInput.value);
                        const limitePassado = new Date(agora.getTime() - 24 * 60 * 60 * 1000);

                        if (dataHoraAgendamento < limitePassado) {
                            e.preventDefault();
                            alert('Não é possível agendar para horários com mais de 24 horas no passado.');
                            horaInput.focus();
                            return;
                        }
                    }
                @endif
            });

            const observacoes = document.getElementById('observacoes');
            if (observacoes) {
                observacoes.addEventListener('input', function() {
                    if (this.value.length > 500) {
                        this.value = this.value.substring(0, 500);
                    }
                });
            }
        });
    </script>
@endsection
