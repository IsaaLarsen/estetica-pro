@extends('layouts.app')

@section('title', (isset($funcionario) ? 'Editar Funcionário' : 'Cadastrar Funcionário') . ' - Estética PRO')

@section('content')
    {{-- Estilos específicos desta página (mantidos e ajustados) --}}
    <style>
        :root {
            --primary:#ec4899; --primary-dark:#db2777; --primary-light:#fbcfe8;
            --secondary:#7e22ce; --text:#1f2937; --text-light:#6b7280;
        }

        .content {
            padding: 11px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .08);
            width: 100%;
            max-width: 700px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            gap: 16px;
            flex-wrap: wrap;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .back-link {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--text-light);
            font-weight: 500;
            transition: .3s;
        }

        .back-link:hover {
            color: var(--primary);
        }

        .back-link i {
            margin-right: 8px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group.full-width.centered {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 30px 0;
            width: 100%;
        }

        label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--text);
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 14px;
            transition: .2s;
            font-family: 'Poppins', sans-serif;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(236, 72, 153, .2);
        }

        /* Switch ativo */
        .form-switch {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: #f9fafb;
            padding: 16px 28px;
            border-radius: 14px;
            border: 2px solid #e5e7eb;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
            margin: 0 auto;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
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

        input:checked + .slider {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 24px;
            border: none;
            border-radius: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: .3s;
            font-size: 16px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: #fff;
            box-shadow: 0 4px 14px rgba(236, 72, 153, .4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(236, 72, 153, .5);
        }

        .btn-icon {
            margin-right: 8px;
        }

        .alert {
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #f59e0b33;
            background: #fffbeb;
            color: #92400e;
            font-size: 14px;
        }

        .alert a {
            color: #7e22ce;
            text-decoration: underline;
        }

        .text-danger {
            color: #dc2626;
            font-size: 12px;
            margin-top: 4px;
            display: block;
        }

        @media (max-width:768px) {
            .page-title {
                font-size: 24px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="content">
        <div class="form-container">
            <div class="page-header">
                <h1 class="page-title">
                    {{ isset($funcionario) ? 'Editar Funcionário' : 'Cadastrar Funcionário' }}
                </h1>
                <a href="{{ route('funcionarios.index') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i> Voltar à lista
                </a>
            </div>

            <form method="POST"
                  action="{{ isset($funcionario) ? route('funcionarios.update', $funcionario->id) : route('funcionarios.store') }}">
                @csrf
                @if(isset($funcionario))
                    @method('PUT')
                @endif

                <div class="form-grid">
                    <div class="form-group">
                        <label for="nome">Nome Completo</label>
                        <input type="text" id="nome" name="nome" required placeholder="Digite o nome completo"
                               value="{{ old('nome', $funcionario->nome ?? '') }}">
                        @error('nome') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label for="cpf">CPF</label>
                        <input type="text" id="cpf" name="cpf" required placeholder="000.000.000-00"
                               value="{{ old('cpf', $funcionario->cpf ?? '') }}">
                        @error('cpf') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" required placeholder="funcionario@esteticapro.com"
                               value="{{ old('email', $funcionario->email ?? '') }}">
                        @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label for="telefone">Telefone</label>
                        <input type="text" id="telefone" name="telefone" placeholder="(00) 00000-0000"
                               value="{{ old('telefone', $funcionario->telefone ?? '') }}">
                        @error('telefone') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- NOVO: Data de Nascimento --}}
                    <div class="form-group">
                        <label for="data_nascimento">Data de Nascimento</label>
                        <input
                            type="date"
                            id="data_nascimento"
                            name="data_nascimento"
                            value="{{ old('data_nascimento',
                                isset($funcionario->data_nascimento)
                                    ? \Carbon\Carbon::parse($funcionario->data_nascimento)->format('Y-m-d')
                                    : ''
                            ) }}"
                        >
                        @error('data_nascimento') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label for="cargo">Cargo</label>

                        @if(isset($cargos) && count($cargos))
                            <select id="cargo" name="cargo" required>
                                <option value="">Selecione um cargo</option>
                                @foreach($cargos as $nomeCargo)
                                    <option value="{{ $nomeCargo }}"
                                        {{ old('cargo', $funcionario->cargo ?? '') === $nomeCargo ? 'selected' : '' }}>
                                        {{ $nomeCargo }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <div class="alert" role="alert">
                                Nenhum cargo ativo encontrado.
                                Cadastre em <a href="{{ route('cargos.create') }}">Cargos → Novo Cargo</a> e volte aqui.
                            </div>
                            <select id="cargo" name="cargo" disabled>
                                <option value="">— sem cargos —</option>
                            </select>
                        @endif

                        @error('cargo') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group full-width">
                        <label for="endereco">Endereço</label>
                        <textarea id="endereco" name="endereco" rows="3"
                                  placeholder="Digite o endereço completo">{{ old('endereco', $funcionario->endereco ?? '') }}</textarea>
                        @error('endereco') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- Switch: Funcionário ativo --}}
                    <div class="form-group full-width centered">
                        <div class="form-switch">
                            <input type="hidden" name="ativo" value="0">
                            <label class="switch" for="ativo">
                                <input type="checkbox" id="ativo" name="ativo" value="1"
                                       {{ old('ativo', ($funcionario->ativo ?? 1)) ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                            <label for="ativo" style="margin-bottom:0; font-weight:600;">Funcionário ativo</label>
                        </div>
                        @error('ativo') <small class="text-danger d-block">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"
                            {{ (!isset($cargos) || !count($cargos)) ? 'disabled' : '' }}>
                        <i class="fas fa-save btn-icon"></i>
                        {{ isset($funcionario) ? 'Atualizar Funcionário' : 'Salvar Funcionário' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @include('partials.change_password_modal')
    @include('partials.toast')
@endsection
