@extends('layouts.app')

@section('title', (isset($cliente) ? 'Editar Cliente' : 'Novo Cliente') . ' - Estética PRO')

@section('content')
    {{-- Estilos específicos da página (apenas o que o formulário usa) --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #ec4899; --secondary: #7e22ce;
            --text: #1f2937; --text-light: #6b7280;
            --danger: #ef4444;
        }

        .content{ padding:24px; display:flex; justify-content:center; align-items:flex-start; }
        .form-container{ background:#fff; padding:30px; border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,.08); width:100%; max-width:800px; }

        .page-header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; gap:12px; flex-wrap:wrap; }
        .page-title{ font-size:24px; font-weight:700; background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
        .back-link{ display:flex; align-items:center; gap:8px; color:var(--text-light); text-decoration:none; font-weight:500; }
        .back-link:hover{ color:var(--primary); }

        .form-grid{ display:grid; grid-template-columns:repeat(2,1fr); gap:20px; }
        .form-group{ margin-bottom:20px; }
        .form-group.full-width{ grid-column:1 / -1; }
        .form-group.centered{ display:flex; justify-content:center; align-items:center; margin:10px 0 30px; }

        label{ display:block; font-weight:500; margin-bottom:8px; color:var(--text); }
        input, select, textarea{
            width:100%; padding:14px 16px; border:2px solid #e5e7eb; border-radius:12px; font-size:14px; transition:.2s; font-family:'Poppins',sans-serif;
        }
        input:focus, select:focus, textarea:focus{ border-color:var(--primary); outline:none; box-shadow:0 0 0 3px rgba(236,72,153,.2); }
        textarea{ min-height:120px; resize:vertical; }

        /* Switch - CORRIGIDO PARA CENTRALIZAR */
        .form-switch{
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: #f9fafb;
            padding: 16px 24px;
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            width: fit-content;
            margin: 0 auto;
        }
        .switch{
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
            margin: 0;
        }
        .switch input{ opacity:0; width:0; height:0; }
        .slider{
            position: absolute;
            inset: 0;
            cursor: pointer;
            background: #ccc;
            transition: .4s;
            border-radius: 24px;
        }
        .slider:before{
            content: "";
            position: absolute;
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background: #fff;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider{ background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%); }
        input:checked + .slider:before{ transform:translateX(26px); }

        /* Texto do switch alinhado verticalmente */
        .form-switch label[for="ativo"] {
            margin: 0;
            line-height: 1;
            font-weight: 600;
        }

        .form-actions{ display:flex; justify-content:flex-end; gap:12px; margin-top:20px; }
        .btn{ display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:14px 24px; border:none; border-radius:12px; font-weight:500; cursor:pointer; font-size:16px; }
        .btn-secondary{ background:#f3f4f6; color:var(--text); text-decoration:none; }
        .btn-secondary:hover{ background:#e5e7eb; }
        .btn-primary{ background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%); color:#fff; box-shadow:0 4px 14px rgba(236,72,153,.4); }
        .btn-primary:hover{ transform:translateY(-2px); box-shadow:0 6px 20px rgba(236,72,153,.5); }

        .error-container{ background:#fef2f2; color:var(--danger); padding:16px; border-radius:12px; margin-bottom:20px; border:1px solid #fecaca; }
        .error-container ul{ margin:8px 0 0 20px; }

        @media (max-width:768px){
            .form-grid{ grid-template-columns:1fr; }
        }
    </style>

    <div class="content">
        <div class="form-container">
            <div class="page-header">
                <h1 class="page-title">{{ isset($cliente) ? 'Editar Cliente' : 'Novo Cliente' }}</h1>
                <a href="{{ route('clientes.index') }}" class="back-link"><i class="fas fa-arrow-left"></i> Voltar à lista</a>
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

            <form id="clienteForm" method="POST" action="{{ isset($cliente) ? route('clientes.update',$cliente->id) : route('clientes.store') }}">
                @csrf
                @if(isset($cliente)) @method('PUT') @endif

                {{-- Apenas na criação --}}
                <input type="hidden" name="senha" id="senhaPadrao" value="EsteticaPRO123">

                <div class="form-grid">
                    <div class="form-group">
                        <label for="nome">Nome Completo</label>
                        <input id="nome" name="nome" type="text" required placeholder="Nome completo do cliente"
                               value="{{ old('nome', $cliente->nome ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label for="telefone">Telefone</label>
                        <input id="telefone" name="telefone" type="text" placeholder="(00) 00000-0000"
                               value="{{ old('telefone', $cliente->telefone ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label for="data_nascimento">Data de Nascimento</label>
                        <input id="data_nascimento" name="data_nascimento" type="date"
                               value="{{ old('data_nascimento', $cliente->data_nascimento ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label for="cpf">CPF</label>
                        <input id="cpf" name="cpf" type="text" placeholder="000.000.000-00"
                               value="{{ old('cpf', $cliente->cpf ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input id="email" name="email" type="email" placeholder="cliente@email.com"
                               value="{{ old('email', $cliente->email ?? '') }}">
                    </div>

                    {{-- CEP / Rua / Número / Bairro --}}
                    <div class="form-group">
                        <label for="cep">CEP</label>
                        <input id="cep" name="cep" type="text" placeholder="00000-000"
                               value="{{ old('cep', $cliente->cep ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label for="rua">Rua</label>
                        <input id="rua" name="rua" type="text" placeholder="Nome da rua"
                               value="{{ old('rua', $cliente->rua ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label for="numero">Número</label>
                        <input id="numero" name="numero" type="text" placeholder="Número"
                               value="{{ old('numero', $cliente->numero ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label for="bairro">Bairro</label>
                        <input id="bairro" name="bairro" type="text" placeholder="Bairro"
                               value="{{ old('bairro', $cliente->bairro ?? '') }}">
                    </div>

                    {{-- Switch: Cliente ativo (AGORA CORRETAMENTE CENTRALIZADO) --}}
                    <div class="form-group full-width centered">
                        <div class="form-switch">
                            <input type="hidden" name="ativo" value="0">
                            <label class="switch" for="ativo">
                                <input type="checkbox" id="ativo" name="ativo" value="1"
                                       {{ old('ativo', ($cliente->ativo ?? 1)) ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                            <label for="ativo">Cliente ativo</label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ isset($cliente) ? 'Atualizar Cliente' : 'Salvar Cliente' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Scripts (mantidos) --}}
    <script>
        // Dropdown de configurações (usa o que já existe no layout)
        const settingsBtn = document.getElementById('settingsBtn');
        const settingsMenu = document.getElementById('settingsMenu');
        if (settingsBtn && settingsMenu) {
            settingsBtn.addEventListener('click', e => { e.stopPropagation(); settingsMenu.classList.toggle('active'); });
            document.addEventListener('click', e => {
                if (!settingsMenu.contains(e.target) && e.target !== settingsBtn) settingsMenu.classList.remove('active');
            });
        }

        // Máscara telefone
        const tel = document.getElementById('telefone');
        if (tel) tel.addEventListener('input', e => {
            let v = e.target.value.replace(/\D/g,'');
            v = v.replace(/(\d{2})(\d)/,'($1) $2')
                 .replace(/(\d{5})(\d)/,'$1-$2')
                 .replace(/(-\d{4})\d+?$/,'$1');
            e.target.value = v;
        });

        // Máscara CPF
        const cpf = document.getElementById('cpf');
        if (cpf) cpf.addEventListener('input', e => {
            let v = e.target.value.replace(/\D/g,'');
            v = v.replace(/(\d{3})(\d)/,'$1.$2')
                 .replace(/(\d{3})(\d)/,'$1.$2')
                 .replace(/(\d{3})(\d{1,2})$/,'$1-$2');
            e.target.value = v;
        });

        // Máscara CEP
        const cepInput = document.getElementById('cep');
        if (cepInput) {
            cepInput.addEventListener('input', e => {
                let v = e.target.value.replace(/\D/g, '');
                if (v.length > 8) v = v.slice(0, 8);
                if (v.length > 5) {
                    v = v.replace(/(\d{5})(\d{1,3})/, '$1-$2');
                }
                e.target.value = v;
            });

            // ViaCEP - preenche Rua e Bairro automaticamente
            cepInput.addEventListener('blur', function () {
                const somenteNumeros = this.value.replace(/\D/g, '');

                if (somenteNumeros.length !== 8) {
                    return;
                }

                fetch(`https://viacep.com.br/ws/${somenteNumeros}/json/`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.erro) return;

                        const rua   = document.getElementById('rua');
                        const bairro = document.getElementById('bairro');

                        if (rua && !rua.value)   rua.value   = data.logradouro || '';
                        if (bairro && !bairro.value) bairro.value = data.bairro || '';
                    })
                    .catch(err => console.error('Erro ao buscar CEP:', err));
            });
        }

        // Remover senha padrão em edição
        document.getElementById('clienteForm').addEventListener('submit', () => {
            @if(isset($cliente))
                document.getElementById('senhaPadrao')?.remove();
            @endif
        });
    </script>

    @include('partials.change_password_modal')
    @include('partials.toast')
@endsection
