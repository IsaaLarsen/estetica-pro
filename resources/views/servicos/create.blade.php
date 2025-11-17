@extends('layouts.app')

@section('title', ($servico ? 'Editar Serviço' : 'Novo Serviço') . ' - Estética PRO')

@section('content')
    {{-- Estilos específicos desta página (mantidos) --}}
    <style>
        :root {
            --primary:#ec4899; --primary-dark:#db2777; --primary-light:#fbcfe8;
            --secondary:#7e22ce; --text:#1f2937; --text-light:#6b7280;
            --success:#10b981; --danger:#ef4444; --warning:#f59e0b;
        }

        /* IMPORTANTE: manter 11px aqui como você pediu */
        .content {
            padding: 11px;
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .form-container{background:#fff;padding:30px;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.08);width:100%;max-width:700px}
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px}
        .page-title{font-size:24px;font-weight:700;background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .back-link{display:flex;align-items:center;text-decoration:none;color:var(--text-light);font-weight:500;transition:.3s}
        .back-link:hover{color:var(--primary)}
        .back-link i{margin-right:8px}

        .form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:20px}
        .form-group{margin-bottom:20px}
        .form-group.full-width{grid-column:1 / -1}
        .form-group.centered{display:flex;justify-content:center;align-items:center;margin:10px 0 30px}

        label{display:block;font-weight:500;margin-bottom:8px;color:var(--text)}
        input,select,textarea{width:100%;padding:14px 16px;border:2px solid #e5e7eb;border-radius:12px;font-size:14px;transition:.3s;font-family:'Poppins',sans-serif}
        input:focus,select:focus,textarea:focus{border-color:var(--primary);outline:none;box-shadow:0 0 0 3px rgba(236,72,153,.2)}
        textarea{min-height:120px;resize:vertical}

        /* Switch */
        .form-switch{display:flex;align-items:center;gap:10px;background:#f9fafb;padding:12px 20px;border-radius:12px;border:2px solid #e5e7eb}
        .switch{position:relative;display:inline-block;width:50px;height:24px}
        .switch input{opacity:0;width:0;height:0}
        .slider{position:absolute;cursor:pointer;inset:0;background:#ccc;transition:.4s;border-radius:24px}
        .slider:before{content:"";position:absolute;height:18px;width:18px;left:3px;bottom:3px;background:#fff;transition:.4s;border-radius:50%}
        input:checked + .slider{background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%)}
        input:checked + .slider:before{transform:translateX(26px)}

        .form-section{border:2px solid #f3f4f6;border-radius:12px;padding:20px;margin-top:20px}
        .section-title{font-size:18px;font-weight:600;margin-bottom:16px;color:var(--text)}
        .search-container{margin-bottom:15px;position:relative}
        .search-input{padding-left:40px}
        .search-icon{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:var(--text-light)}

        /* ==== CHIPS: 2 por linha, ocupando a largura inteira ==== */
        .chips-container{
            display:grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap:16px;
            margin-top:12px;
            max-height:260px;
            overflow-y:auto;
            padding:4px;
        }
        .chip{
            display:flex;
            align-items:center;
            gap:10px;
            padding:14px 16px;
            background:#f9fafb;
            border:2px solid #e5e7eb;
            border-radius:12px;
            cursor:pointer;
            transition:.2s;
            width:100%;              /* ocupa toda a coluna */
            min-height:60px;         /* altura agradável */
            box-sizing:border-box;
        }
        .chip:hover{border-color:var(--primary)}
        .chip input{flex-shrink:0}
        .chip.selected{background:var(--primary-light);border-color:var(--primary);color:var(--primary)}
        .chip-label{
            display:block;
            flex:1;
            color:var(--text);       /* força cor do texto */
            font-weight:600;
            font-size:14px;
            line-height:1.3;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
            text-align:center;       /* fica bonito e consistente */
        }

        .no-results{text-align:center;color:var(--text-light);padding:20px;font-style:italic}

        .form-actions{display:flex;justify-content:flex-end;margin-top:20px}
        .btn{display:inline-flex;align-items:center;justify-content:center;padding:14px 24px;border:none;border-radius:12px;font-weight:500;cursor:pointer;transition:.3s;font-size:16px}
        .btn-secondary{background:#f3f4f6;color:var(--text);text-decoration:none;margin-right:12px}
        .btn-secondary:hover{background:#e5e7eb}
        .btn-primary{background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);color:#fff;box-shadow:0 4px 14px rgba(236,72,153,.4)}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(236,72,153,.5)}
        .btn-icon{margin-right:8px}

        .error-message{color:var(--danger);font-size:14px;margin-top:5px;display:flex;align-items:center;gap:5px}
        .error-container{background:#fef2f2;color:var(--danger);padding:16px;border:1px solid #fecaca;border-radius:12px;margin-bottom:20px}

        @media (max-width:768px){
            .form-grid{grid-template-columns:1fr}
            .page-header{flex-direction:column;align-items:flex-start;gap:15px}
            .back-link{align-self:flex-end}
            .form-actions{flex-direction:column;gap:12px}
            .btn{width:100%;justify-content:center}
            .chips-container{grid-template-columns: 1fr;} /* 1 por linha no mobile */
        }
    </style>

    @php
        // Mostra "PrimeiroNome ÚltimoSobrenome"
        function exibicao_nome_curta(?string $nomeCompleto): string {
            $nomeCompleto = trim((string)$nomeCompleto);
            if ($nomeCompleto === '') return '';
            $parts = preg_split('/\s+/u', $nomeCompleto, -1, PREG_SPLIT_NO_EMPTY);
            if (!$parts || count($parts) === 0) return '';
            $first = $parts[0];
            // encontra o último pedaço "válido" (>=2 chars)
            $last = $first;
            for ($i = count($parts)-1; $i >= 0; $i--) {
                if (mb_strlen($parts[$i], 'UTF-8') > 1) { $last = $parts[$i]; break; }
            }
            if (mb_strtolower($first,'UTF-8') === mb_strtolower($last,'UTF-8')) {
                return $first;
            }
            return $first.' '.$last;
        }
    @endphp

    <div class="content">
        <div class="form-container">
            <div class="page-header">
                <h1 class="page-title">{{ $servico ? 'Editar Serviço' : 'Novo Serviço' }}</h1>
                <a href="{{ route('servicos.index') }}" class="back-link">
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

            <form method="POST" action="{{ $servico ? route('servicos.update', $servico->id) : route('servicos.store') }}">
                @csrf
                @if($servico) @method('PUT') @endif

                <div class="form-grid">
                    <div class="form-group">
                        <label for="nome">Nome do serviço</label>
                        <input type="text" id="nome" name="nome" value="{{ old('nome', $servico->nome ?? '') }}" required placeholder="Ex: Corte de Cabelo">
                    </div>

                    <div class="form-group">
                        <label for="valor">Valor (R$)</label>
                        <input type="text" id="valor" name="valor" placeholder="Ex: 120,00" value="{{ old('valor', isset($servico) ? number_format($servico->valor,2,',','.') : '') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="comissao_percent">% da comissão</label>
                        <input type="text" id="comissao_percent" name="comissao_percent" placeholder="Ex: 40,00" value="{{ old('comissao_percent', isset($servico) ? number_format($servico->comissao_percent,2,',','.') : '') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="duracao_minutos">Duração (minutos)</label>
                        <input type="number" id="duracao_minutos" min="5" max="1440" name="duracao_minutos" value="{{ old('duracao_minutos', $servico->duracao_minutos ?? 30) }}" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="descricao">Descrição</label>
                        <textarea id="descricao" name="descricao" placeholder="Detalhes do serviço...">{{ old('descricao', $servico->descricao ?? '') }}</textarea>
                    </div>

                    {{-- Switch de serviço ativo --}}
                    <div class="form-group full-width centered">
                        <div class="form-switch">
                            <input type="hidden" name="ativo" value="0">
                            <label class="switch" for="ativo">
                                <input type="checkbox" id="ativo" name="ativo" value="1" {{ old('ativo', ($servico->ativo ?? 1)) ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                            <label for="ativo" style="margin-bottom:0;font-weight:600;">Serviço ativo</label>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <div class="form-section">
                            <h3 class="section-title">Quem realiza este serviço?</h3>

                            <div class="search-container">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" class="form-input search-input" id="searchFuncionarios" placeholder="Pesquisar funcionários...">
                            </div>

                            <div class="chips-container" id="chipsContainer">
                                @foreach($funcionarios as $f)
                                    @php
                                        $nomeExibicao = exibicao_nome_curta($f->nome ?? '');
                                        $nomeData = mb_strtolower($nomeExibicao, 'UTF-8');
                                        $checked = in_array($f->id, old('funcionarios', $vinculados ?? []));
                                    @endphp
                                    <div class="chip funcionario-chip {{ $checked ? 'selected' : '' }}" data-nome="{{ $nomeData }}">
                                        <input
                                            type="checkbox"
                                            name="funcionarios[]"
                                            value="{{ $f->id }}"
                                            id="funcionario-{{ $f->id }}"
                                            {{ $checked ? 'checked' : '' }}
                                        >
                                        <label for="funcionario-{{ $f->id }}" class="chip-label">
                                            {{ $nomeExibicao }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            <div id="noResults" class="no-results" style="display:none;">Nenhum funcionário encontrado.</div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('servicos.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times btn-icon"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save btn-icon"></i>
                        {{ $servico ? 'Atualizar Serviço' : 'Salvar Serviço' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Scripts específicos (mantidos) --}}
    <script>
        // Chips: toggle por clique
        document.querySelectorAll('.chip').forEach(chip => {
            chip.addEventListener('click', function(e){
                if (e.target.type !== 'checkbox') {
                    const cb = this.querySelector('input[type="checkbox"]');
                    cb.checked = !cb.checked;
                    this.classList.toggle('selected', cb.checked);
                    cb.dispatchEvent(new Event('change'));
                }
            });
        });
        // Sincroniza classe quando marcar/desmarcar direto no checkbox
        document.querySelectorAll('.chip input[type="checkbox"]').forEach(cb => {
            cb.addEventListener('change', function(){
                this.closest('.chip').classList.toggle('selected', this.checked);
            });
        });

        // Máscara/formatador de moeda (R$)
        const inputValor = document.querySelector('input[name="valor"]');
        if (inputValor){
            inputValor.addEventListener('input', e => {
                let v = e.target.value.replace(/\D/g,'');
                v = (v/100).toFixed(2)+'';
                v = v.replace('.',',').replace(/(\d)(?=(\d{3})+(?!\d))/g,'$1.');
                e.target.value = v;
            });
        }

        // Formatação de porcentagem
        const inputComissao = document.querySelector('input[name="comissao_percent"]');
        if (inputComissao){
            inputComissao.addEventListener('input', e => {
                let v = e.target.value.replace(/\D/g,'');
                v = (v/100).toFixed(2)+'';
                e.target.value = v.replace('.',',');
            });
        }

        // Filtro de funcionários (usa o atributo data-nome com "Primeiro Último")
        (function(){
            const searchInput = document.getElementById('searchFuncionarios');
            const chipsContainer = document.getElementById('chipsContainer');
            const chips = document.querySelectorAll('.funcionario-chip');
            const noResults = document.getElementById('noResults');

            if (!searchInput) return;
            searchInput.addEventListener('input', function(){
                const term = this.value.toLowerCase().trim();
                let any = false;
                chips.forEach(chip => {
                    const nome = (chip.getAttribute('data-nome') || '').toLowerCase();
                    const show = nome.includes(term);
                    chip.style.display = show ? 'flex' : 'none';
                    if (show) any = true;
                });
                noResults.style.display = any ? 'none' : 'block';
                chipsContainer.style.display = any ? 'grid' : 'none';
            });
        })();
    </script>

    @include('partials.change_password_modal')
    @include('partials.toast')
@endsection
