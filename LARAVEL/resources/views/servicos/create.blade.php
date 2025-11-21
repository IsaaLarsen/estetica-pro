@extends('layouts.app')

@section('title', ($servico ? 'Editar Serviço' : 'Novo Serviço') . ' - Estética PRO')

@section('content')
    <style>
        :root {
            --primary:#ec4899; --primary-dark:#db2777; --primary-light:#fbcfe8;
            --secondary:#7e22ce; --text:#1f2937; --text-light:#6b7280;
            --success:#10b981; --danger:#ef4444; --warning:#f59e0b;
        }

        /* Mantém os 11px que você pediu */
        .content {
            padding: 11px;
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .form-container{
            background:#fff;
            padding:30px;
            border-radius:16px;
            box-shadow:0 4px 20px rgba(0,0,0,.08);
            width:100%;
            max-width:700px
        }
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px}
        .page-title{
            font-size:24px;
            font-weight:700;
            background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);
            -webkit-background-clip:text;
            -webkit-text-fill-color:transparent;
            background-clip:text
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

        .form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:20px}
        .form-group{margin-bottom:20px}
        .form-group.full-width{grid-column:1 / -1}
        .form-group.centered{display:flex;justify-content:center;align-items:center;margin:10px 0 30px}

        label{display:block;font-weight:500;margin-bottom:8px;color:var(--text)}
        input,select,textarea{
            width:100%;
            padding:14px 16px;
            border:2px solid #e5e7eb;
            border-radius:12px;
            font-size:14px;
            transition:.3s;
            font-family:'Poppins',sans-serif
        }
        input:focus,select:focus,textarea:focus{
            border-color:var(--primary);
            outline:none;
            box-shadow:0 0 0 3px rgba(236,72,153,.2)
        }
        textarea{min-height:120px;resize:vertical}

        /* Switch */
        .form-switch{
            display:flex;
            align-items:center;
            gap:10px;
            background:#f9fafb;
            padding:12px 20px;
            border-radius:12px;
            border:2px solid #e5e7eb
        }
        .switch{position:relative;display:inline-block;width:50px;height:24px}
        .switch input{opacity:0;width:0;height:0}
        .slider{
            position:absolute;
            cursor:pointer;
            inset:0;
            background:#ccc;
            transition:.4s;
            border-radius:24px
        }
        .slider:before{
            content:"";
            position:absolute;
            height:18px;
            width:18px;
            left:3px;
            bottom:3px;
            background:#fff;
            transition:.4s;
            border-radius:50%
        }
        input:checked + .slider{background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%)}
        input:checked + .slider:before{transform:translateX(26px)}

        .form-section{
            border:2px solid #f3f4f6;
            border-radius:12px;
            padding:20px;
            margin-top:20px
        }
        .section-title{font-size:18px;font-weight:600;margin-bottom:16px;color:var(--text)}

        /* LISTA DE FUNCIONÁRIOS - ESTILO MELHORADO */
        .func-list{
            list-style:none;
            margin:0;
            padding:0;
            max-height:260px;
            overflow-y:auto;
            overflow-x:hidden;
            display:flex;
            flex-direction:column;
            gap:8px;
        }
        .func-item{
            display:block;
            width:100%;
            transition: all 0.3s ease;
        }
        .func-item.selected {
            order: -1; /* Coloca os selecionados no topo */
        }
        .func-label{
            display:flex;
            align-items:center;
            padding:16px;
            border-radius:12px;
            border:2px solid #e5e7eb;
            background:#f9fafb;
            cursor:pointer;
            width:100%;
            box-sizing:border-box;
            transition: all 0.3s ease;
            gap:12px;
        }
        .func-label:hover{
            border-color:var(--primary);
            background:#fdf2f8;
        }
        .func-label.checked{
            border-color:var(--primary);
            background:linear-gradient(135deg, rgba(236,72,153,0.1) 0%, rgba(126,34,206,0.1) 100%);
        }
        .func-label input[type="checkbox"]{
            width:18px;
            height:18px;
            margin:0;
            accent-color:var(--primary);
        }
        .func-name{
            font-weight:600;
            color:var(--text);
            white-space:normal;
            word-break:break-word;
            text-align:left;
            flex:1;
        }
        .func-checkmark{
            width:20px;
            height:20px;
            border:2px solid #d1d5db;
            border-radius:6px;
            display:flex;
            align-items:center;
            justify-content:center;
            transition: all 0.3s ease;
            flex-shrink:0;
        }
        .func-label.checked .func-checkmark{
            background:var(--primary);
            border-color:var(--primary);
        }
        .func-label.checked .func-checkmark::after{
            content:"✓";
            color:white;
            font-size:12px;
            font-weight:bold;
        }

        .search-container{
            margin-bottom:15px;
            position:relative
        }
        .search-input{
            padding-left:40px;
            background:#fff;
        }
        .search-icon{
            position:absolute;
            left:16px;
            top:50%;
            transform:translateY(-50%);
            color:var(--text-light)
        }
        .no-results{
            text-align:center;
            color:var(--text-light);
            padding:20px;
            font-style:italic;
            background:#f9fafb;
            border-radius:12px;
            border:2px dashed #e5e7eb;
        }

        .form-actions{
            display:flex;
            justify-content:flex-end;
            margin-top:20px
        }
        .btn{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:14px 24px;
            border:none;
            border-radius:12px;
            font-weight:500;
            cursor:pointer;
            transition:.3s;
            font-size:16px
        }
        .btn-secondary{
            background:#f3f4f6;
            color:var(--text);
            text-decoration:none;
            margin-right:12px
        }
        .btn-secondary:hover{
            background:#e5e7eb
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
        .btn-icon{
            margin-right:8px
        }

        .error-container{
            background:#fef2f2;
            color:var(--danger);
            padding:16px;
            border:1px solid #fecaca;
            border-radius:12px;
            margin-bottom:20px
        }

        @media (max-width:768px){
            .form-grid{grid-template-columns:1fr}
            .page-header{flex-direction:column;align-items:flex-start;gap:15px}
            .back-link{align-self:flex-end}
            .form-actions{flex-direction:column;gap:12px}
            .btn{width:100%;justify-content:center}
        }
    </style>

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
                        <input type="text" id="nome" name="nome"
                               value="{{ old('nome', $servico->nome ?? '') }}"
                               required placeholder="Ex: Corte de Cabelo">
                    </div>

                    <div class="form-group">
                        <label for="valor">Valor (R$)</label>
                        <input type="text" id="valor" name="valor"
                               placeholder="Ex: 120,00"
                               value="{{ old('valor', isset($servico) ? number_format($servico->valor,2,',','.') : '') }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="comissao_percent">% da comissão</label>
                        <input type="text" id="comissao_percent" name="comissao_percent"
                               placeholder="Ex: 40,00"
                               value="{{ old('comissao_percent', isset($servico) ? number_format($servico->comissao_percent,2,',','.') : '') }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="duracao_minutos">Duração (minutos)</label>
                        <input type="number" id="duracao_minutos" min="5" max="1440" name="duracao_minutos"
                               value="{{ old('duracao_minutos', $servico->duracao_minutos ?? 30) }}"
                               required>
                    </div>

                    <div class="form-group full-width">
                        <label for="descricao">Descrição</label>
                        <textarea id="descricao" name="descricao"
                                  placeholder="Detalhes do serviço...">{{ old('descricao', $servico->descricao ?? '') }}</textarea>
                    </div>

                    {{-- Switch de serviço ativo --}}
                    <div class="form-group full-width centered">
                        <div class="form-switch">
                            <input type="hidden" name="ativo" value="0">
                            <label class="switch" for="ativo">
                                <input type="checkbox" id="ativo" name="ativo" value="1"
                                       {{ old('ativo', ($servico->ativo ?? 1)) ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                            <label for="ativo" style="margin-bottom:0;font-weight:600;">Serviço ativo</label>
                        </div>
                    </div>

                    {{-- FUNCIONÁRIOS --}}
                    <div class="form-group full-width">
                        <div class="form-section">
                            <h3 class="section-title">Quem realiza este serviço?</h3>

                            <div class="search-container">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text"
                                       class="form-input search-input"
                                       id="searchFuncionarios"
                                       placeholder="Pesquisar funcionários...">
                            </div>

                            <ul class="func-list" id="funcList">
                                @php
                                    $vinculadosIds = old('funcionarios', $vinculados ?? []);
                                @endphp
                                
                                {{-- Primeiro os selecionados --}}
                                @foreach($funcionarios as $f)
                                    @if(in_array($f->id, $vinculadosIds))
                                        @php
                                            $nomeData = mb_strtolower($f->nome ?? '', 'UTF-8');
                                        @endphp
                                        <li class="func-item selected" data-nome="{{ $nomeData }}">
                                            <label class="func-label checked" for="funcionario-{{ $f->id }}">
                                                <input
                                                    type="checkbox"
                                                    name="funcionarios[]"
                                                    value="{{ $f->id }}"
                                                    id="funcionario-{{ $f->id }}"
                                                    checked>
                                                <div class="func-checkmark"></div>
                                                <span class="func-name">
                                                    {{ $f->nome ?? 'SEM NOME' }}
                                                </span>
                                            </label>
                                        </li>
                                    @endif
                                @endforeach
                                
                                {{-- Depois os não selecionados --}}
                                @foreach($funcionarios as $f)
                                    @if(!in_array($f->id, $vinculadosIds))
                                        @php
                                            $nomeData = mb_strtolower($f->nome ?? '', 'UTF-8');
                                        @endphp
                                        <li class="func-item" data-nome="{{ $nomeData }}">
                                            <label class="func-label" for="funcionario-{{ $f->id }}">
                                                <input
                                                    type="checkbox"
                                                    name="funcionarios[]"
                                                    value="{{ $f->id }}"
                                                    id="funcionario-{{ $f->id }}">
                                                <div class="func-checkmark"></div>
                                                <span class="func-name">
                                                    {{ $f->nome ?? 'SEM NOME' }}
                                                </span>
                                            </label>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>

                            <div id="noResults" class="no-results" style="display:none;">
                                Nenhum funcionário encontrado.
                            </div>
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

    <script>
        // Filtro de funcionários com ordenação
        (function(){
            const searchInput = document.getElementById('searchFuncionarios');
            const items = document.querySelectorAll('#funcList .func-item');
            const noResults = document.getElementById('noResults');
            const list = document.getElementById('funcList');

            if (!searchInput) return;

            searchInput.addEventListener('input', function(){
                const term = this.value.toLowerCase().trim();
                let any = false;

                items.forEach(item => {
                    const nome = (item.getAttribute('data-nome') || '').toLowerCase();
                    const show = nome.includes(term);
                    item.style.display = show ? 'block' : 'none';
                    if (show) any = true;
                });

                noResults.style.display = any ? 'none' : 'block';
                list.style.display = any ? 'block' : 'none';
            });

            // Atualizar visualização quando checkbox é clicado
            document.querySelectorAll('.func-label input[type="checkbox"]').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const label = this.closest('.func-label');
                    const item = this.closest('.func-item');
                    
                    if (this.checked) {
                        label.classList.add('checked');
                        item.classList.add('selected');
                        // Move para o topo
                        list.prepend(item);
                    } else {
                        label.classList.remove('checked');
                        item.classList.remove('selected');
                        // Move para o final
                        list.appendChild(item);
                    }
                });
            });
        })();

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
    </script>

    @include('partials.change_password_modal')
    @include('partials.toast')
@endsection