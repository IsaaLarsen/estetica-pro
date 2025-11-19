@extends('layouts.app')

@section('title', (isset($cargo) ? 'Editar Cargo' : 'Novo Cargo') . ' - Estética PRO')

@section('content')
    <style>
        :root{
            --primary:#ec4899; --primary-dark:#db2777; --primary-light:#fbcfe8;
            --secondary:#7e22ce; --text:#1f2937; --text-light:#6b7280;
            --success:#10b981; --warning:#f59e0b; --danger:#ef4444;
            --sidebar-width:260px;
        }
        *{margin:0;padding:0;box-sizing:border-box}

        /* IMPORTANTE: 11px como pedido */
        .content{
            padding:11px;
            flex:1;
            display:flex;
            justify-content:center;
            align-items:flex-start;
        }

        .form-container{background:#fff;padding:30px;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.08);width:100%;max-width:700px}
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px}
        .page-title{font-size:24px;font-weight:700;background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
        .back-link{display:flex;align-items:center;text-decoration:none;color:var(--text-light);font-weight:500;transition:.3s}
        .back-link:hover{color:var(--primary)}
        .back-link i{margin-right:8px}

        .form-grid{display:grid;grid-template-columns:1fr;gap:20px}
        label{display:block;font-weight:500;margin-bottom:8px;color:var(--text)}
        input,textarea{width:100%;padding:14px 16px;border:2px solid #e5e7eb;border-radius:12px;font-size:14px;transition:.2s;font-family:'Poppins',sans-serif}
        input:focus,textarea:focus{border-color:var(--primary);outline:none;box-shadow:0 0 0 3px rgba(236,72,153,.2)}

        /* Caixa que imita input para o Status - REDUZIDA */
        .field-box{
            display:flex;
            justify-content:center;
            align-items: center;
            gap:12px;
            padding:8px 16px; /* REDUZIDO o padding vertical */
            background:#f9fafb;
            border:2px solid #e5e7eb;
            border-radius:12px;
            min-height:48px; /* REDUZIDO a altura mínima */
            width:100%;
            max-width:200px; /* LARGURA MÁXIMA REDUZIDA */
            margin: 0 auto; /* Centraliza horizontalmente */
        }

        /* Switch */
        .form-switch{display:flex;align-items:center;gap:12px}
        .switch{position:relative;display:inline-block;width:50px;height:24px;line-height:0;margin:0}
        .switch input{opacity:0;width:0;height:0}
        .slider{position:absolute;cursor:pointer;inset:0;background:#ccc;transition:.4s;border-radius:24px}
        .slider:before{content:"";position:absolute;height:18px;width:18px;left:3px;bottom:3px;background:#fff;transition:.4s;border-radius:50%}
        input:checked + .slider{background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%)}
        input:checked + .slider:before{transform:translateX(26px)}

        /* Texto alterado para "Cargo ativo" */
        .status-text{
            display:flex;
            align-items:center;
            font-weight:600;
            line-height:1;
            color:var(--text);
            font-size:14px; /* Tamanho de fonte ajustado */
        }

        .form-actions{display:flex;justify-content:flex-end;margin-top:20px;gap:10px}
        .btn{display:inline-flex;align-items:center;justify-content:center;padding:14px 24px;border:none;border-radius:12px;font-weight:500;cursor:pointer;transition:.3s;font-size:16px;text-decoration:none}
        .btn-primary{background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);color:#fff;box-shadow:0 4px 14px rgba(236,72,153,.4)}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(236,72,153,.5)}
        .btn-light{background:#f3f4f6;color:var(--text)}
        .btn-icon{margin-right:8px}

        .alert{padding:12px 14px;border-radius:10px;border:1px solid #f59e0b33;background:#fffbeb;color:#92400e;font-size:14px}
        .text-danger{color:#dc2626;font-size:12px;margin-top:6px;display:block}
    </style>

    <div class="content">
        <div class="form-container">
            <div class="page-header">
                <h1 class="page-title">{{ isset($cargo) ? 'Editar Cargo' : 'Novo Cargo' }}</h1>
                <a href="{{ route('cargos.index') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i> Voltar à lista
                </a>
            </div>

            @if(session('success'))
                <div class="alert"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ isset($cargo) ? route('cargos.update', $cargo) : route('cargos.store') }}">
                @csrf
                @if(isset($cargo)) @method('PUT') @endif

                <div class="form-grid">
                    <div class="form-group">
                        <label for="nome">Nome *</label>
                        <input type="text" id="nome" name="nome" required placeholder="Ex.: Esteticista"
                               value="{{ old('nome', $cargo->nome ?? '') }}">
                        @error('nome') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea id="descricao" name="descricao" rows="3" placeholder="Breve descrição do cargo">{{ old('descricao', $cargo->descricao ?? '') }}</textarea>
                        @error('descricao') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <!-- Label removida conforme solicitado -->
                        
                        <!-- Caixa centralizada com switch + texto alterado - AGORA REDUZIDA -->
                        <div class="field-box">
                            <input type="hidden" name="ativo" value="0">
                            <div class="form-switch">
                                <label class="switch" for="ativo">
                                    <input type="checkbox" id="ativo" name="ativo" value="1"
                                           {{ old('ativo', $cargo->ativo ?? 1) ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                                <span class="status-text">Cargo ativo</span>
                            </div>
                        </div>

                        @error('ativo') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('cargos.index') }}" class="btn btn-light">
                        <i class="fas fa-times btn-icon"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save btn-icon"></i>
                        {{ isset($cargo) ? 'Atualizar Cargo' : 'Salvar Cargo' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const settingsBtn = document.getElementById('settingsBtn');
        const settingsMenu = document.getElementById('settingsMenu');
        if (settingsBtn && settingsMenu) {
            settingsBtn.addEventListener('click', e => {
                e.stopPropagation();
                settingsMenu.classList.toggle('active');
            });
            document.addEventListener('click', e => {
                if (!settingsMenu.contains(e.target) && e.target !== settingsBtn) {
                    settingsMenu.classList.remove('active');
                }
            });
        }
    </script>

    @include('partials.change_password_modal')
    @include('partials.toast')
@endsection