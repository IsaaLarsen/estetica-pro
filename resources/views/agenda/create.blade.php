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
            <a href="{{ route('agenda.index') }}" class="back-link"
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
            $isEdit = isset($agenda);
            $action = $isEdit ? route('agenda.update', $agenda->id) : route('agenda.store');
            $selFuncionario = old('funcionario_id', $isEdit ? $agenda->funcionario_id : '');
            $selCliente = old('cliente_id', $isEdit ? $agenda->cliente_id : '');
            $selServico = old('servico_id', $isEdit ? $agenda->servico_id : '');
            $dataValue = old('data', $isEdit ? Carbon::parse($agenda->inicio)->format('Y-m-d') : '');
            $horaValue = old('hora', $isEdit ? Carbon::parse($agenda->inicio)->format('H:i') : '');
            $obsValue = old('observacoes', $isEdit ? ($agenda->observacoes ?? '') : '');
            $statusValue = old('status', $isEdit ? ($agenda->status ?? 'agendado') : 'agendado');
        @endphp

        <form method="POST" action="{{ $action }}" id="agendaForm">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                <div class="form-group">
                    <label for="funcionario_id" style="font-weight:500;">Funcionário *</label>
                    <select id="funcionario_id" name="funcionario_id" required
                        style="width:100%; padding:14px 16px; border:2px solid #e5e7eb; border-radius:12px;">
                        <option value="">Selecione...</option>
                        @foreach($funcionarios as $f)
                            <option value="{{ $f->id }}" {{ (string) $selFuncionario === (string) $f->id ? 'selected' : '' }}>
                                {{ $f->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="cliente_id" style="font-weight:500;">Cliente *</label>
                    {{-- Select pesquisável (AJAX) --}}
                    <select id="cliente_id" name="cliente_id" required class="js-clientes-ajax" style="width:100%;">
                        @if($selCliente)
                            <option value="{{ $selCliente }}" selected>
                                {{ $clientes->firstWhere('id', $selCliente)->nome ?? 'Selecionado' }}
                            </option>
                        @endif
                    </select>
                </div>

                <div class="form-group">
                    <label for="servico_id" style="font-weight:500;">Serviço *</label>
                    <select id="servico_id" name="servico_id" required
                        style="width:100%; padding:14px 16px; border:2px solid #e5e7eb; border-radius:12px;">
                        <option value="">Selecione...</option>
                        @foreach($servicos as $s)
                            <option value="{{ $s->id }}" {{ (string) $selServico === (string) $s->id ? 'selected' : '' }}>
                                {{ $s->nome }} ({{ $s->duracao_minutos ?? 30 }} min)
                            </option>
                        @endforeach
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
                        min="{{ $isEdit ? '' : date('Y-m-d') }}"
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
                        <option value="agendado"  {{ $statusValue === 'agendado'  ? 'selected' : '' }}>Agendado</option>
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
                <a href="{{ route('agenda.index') }}" class="btn btn-secondary"
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
    </div>
@endsection

{{-- scripts da página (como seu layout usa @yield('scripts')) --}}
@section('scripts')
    {{-- CDN (caso ainda não tenha no layout) --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>

    <style>
      /* Tema rosa do Select2 – remove azul feio e deixa moderno */
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
        // Select2 AJAX para CLIENTES
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
                results: data.data,      // [{ id, text }]
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

        const dataInput = document.getElementById('data');
        const horaInput = document.getElementById('hora');
        const form = document.getElementById('agendaForm');

        form.addEventListener('submit', function(e) {
          // ---------- VALIDAR ANO (4 dígitos, faixa aceitável) ----------
          if (dataInput.value) {
            // type="date" => formato padrão: YYYY-MM-DD
            const partes = dataInput.value.split('-');
            if (partes.length === 3) {
              const anoStr = partes[0];
              const ano = parseInt(anoStr, 10);

              if (!anoStr || anoStr.length !== 4 || isNaN(ano) || ano < 1900 || ano > 2100) {
                e.preventDefault();
                alert('Ano inválido. Use um ano com 4 dígitos entre 1900 e 2100.');
                dataInput.focus();
                return;
              }
            }
          }

          const agora = new Date();

          // Para novos agendamentos, não permitir datas passadas
          @if(!$isEdit)
            if (dataInput.value && new Date(dataInput.value) < new Date().setHours(0,0,0,0)) {
              e.preventDefault();
              alert('Não é possível agendar para datas passadas.');
              dataInput.focus();
              return;
            }
          @endif

          // Validar formato da hora
          const hora = horaInput.value;
          if (hora && !/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/.test(hora)) {
            e.preventDefault();
            alert('Formato de hora inválido. Use HH:MM (24 horas).');
            horaInput.focus();
            return;
          }

          // Validar se data+hora não é no passado (para novos agendamentos)
          @if(!$isEdit)
            if (dataInput.value && horaInput.value) {
              const dataHoraAgendamento = new Date(dataInput.value + 'T' + horaInput.value);
              if (dataHoraAgendamento < agora) {
                e.preventDefault();
                alert('Não é possível agendar para horários no passado.');
                horaInput.focus();
                return;
              }
            }
          @endif
        });

        // Validação em tempo real para observações
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
