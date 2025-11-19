@extends('layouts.app')

@section('title', (isset($bloqueio) ? 'Editar Bloqueio' : 'Novo Bloqueio') . ' - Estética PRO')

@section('content')
    {{-- Estilos específicos desta página --}}
    <style>
        :root {
            --primary:#ec4899; --primary-dark:#db2777; --primary-light:#fbcfe8;
            --secondary:#7e22ce; --text:#1f2937; --text-light:#6b7280;
            --danger:#ef4444;
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
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
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

        select[multiple] {
            min-height: 120px;
            padding: 12px;
        }

        select[multiple] option {
            padding: 8px 12px;
            margin: 2px 0;
            border-radius: 6px;
            cursor: pointer;
        }

        select[multiple] option:hover {
            background: var(--primary-light);
        }

        select[multiple] option:checked {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }

        /* Checkbox personalizado */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0;
        }

        .checkbox-custom {
            width: 20px;
            height: 20px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            position: relative;
            cursor: pointer;
            transition: .2s;
        }

        .checkbox-custom.checked {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-color: var(--primary);
        }

        .checkbox-custom.checked::after {
            content: "✓";
            position: absolute;
            color: white;
            font-size: 14px;
            font-weight: bold;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .checkbox-label {
            margin: 0;
            cursor: pointer;
            font-weight: 500;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 30px;
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
            text-decoration: none;
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

        .btn-secondary {
            background: #f3f4f6;
            color: var(--text);
            border: 2px solid #e5e7eb;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .alert-error {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            background: #fef2f2;
            color: var(--danger);
            border: 1px solid #fecaca;
        }

        .alert-error ul {
            margin: 0;
            padding-left: 1.2rem;
        }

        .help-text {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 4px;
            line-height: 1.4;
        }

        @media (max-width:768px) {
            .page-title {
                font-size: 24px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>

    <div class="content">
        <div class="form-container">
            <div class="page-header">
                <h1 class="page-title">
                    {{ isset($bloqueio) ? 'Editar Bloqueio' : 'Novo Bloqueio' }}
                </h1>
                <a href="{{ route('agenda.bloqueios.index') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i> Voltar à lista
                </a>
            </div>

            @if ($errors->any())
                <div class="alert-error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ isset($bloqueio) ? route('agenda.bloqueios.update', $bloqueio->id) : route('agenda.bloqueios.store') }}">
                @csrf
                @if(isset($bloqueio))
                    @method('PUT')
                @endif

                <div class="form-grid">
                    {{-- Funcionários (multi-select) --}}
                    <div class="form-group">
                        <label for="funcionarios">Profissionais</label>
                        <select id="funcionarios" name="funcionarios[]" multiple required
                                @if(old('aplicar_todos', isset($bloqueio) ? $bloqueio->aplicar_todos : '')) disabled @endif>
                            @php
                                $oldFuncionarios = collect(old('funcionarios', isset($bloqueio) ? $bloqueio->funcionarios->pluck('id') : []));
                            @endphp

                            @foreach($funcionarios as $f)
                                <option value="{{ $f->id }}"
                                    @if($oldFuncionarios->contains($f->id)) selected @endif>
                                    {{ $f->nome }}
                                </option>
                            @endforeach
                        </select>
                        <div class="help-text">
                            Segure CTRL (ou CMD no Mac) para selecionar mais de um profissional.
                            Se marcar "Aplicar a todos", não precisa selecionar.
                        </div>
                    </div>

                    {{-- Aplicar a todos --}}
                    <div class="checkbox-group">
                        <div class="checkbox-custom {{ old('aplicar_todos', isset($bloqueio) ? $bloqueio->aplicar_todos : '') ? 'checked' : '' }}"
                             id="customCheckbox"></div>
                        <input type="checkbox" name="aplicar_todos" id="aplicar_todos" value="1"
                               {{ old('aplicar_todos', isset($bloqueio) ? $bloqueio->aplicar_todos : '') ? 'checked' : '' }}
                               style="display: none;">
                        <label for="aplicar_todos" class="checkbox-label">Aplicar a todos os profissionais</label>
                    </div>

                    {{-- Data e Hora Início --}}
                    <div class="form-row">
                        <div class="form-group">
                            <label for="data_inicio">Data início</label>
                            <input type="date" id="data_inicio" name="data_inicio"
                                   value="{{ old('data_inicio', isset($bloqueio) ? $bloqueio->inicio->format('Y-m-d') : '') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="hora_inicio">Hora início</label>
                            <input type="time" id="hora_inicio" name="hora_inicio"
                                   value="{{ old('hora_inicio', isset($bloqueio) ? $bloqueio->inicio->format('H:i') : '') }}" required>
                        </div>
                    </div>

                    {{-- Data e Hora Fim --}}
                    <div class="form-row">
                        <div class="form-group">
                            <label for="data_fim">Data fim</label>
                            <input type="date" id="data_fim" name="data_fim"
                                   value="{{ old('data_fim', isset($bloqueio) ? $bloqueio->fim->format('Y-m-d') : '') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="hora_fim">Hora fim</label>
                            <input type="time" id="hora_fim" name="hora_fim"
                                   value="{{ old('hora_fim', isset($bloqueio) ? $bloqueio->fim->format('H:i') : '') }}" required>
                        </div>
                    </div>

                    {{-- Motivo --}}
                    <div class="form-group">
                        <label for="motivo">Motivo (opcional)</label>
                        <input type="text" id="motivo" name="motivo"
                               value="{{ old('motivo', isset($bloqueio) ? $bloqueio->motivo : '') }}"
                               placeholder="Férias, treinamento, manutenção...">
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('agenda.bloqueios.index') }}" class="btn btn-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save" style="margin-right:8px;"></i>
                        {{ isset($bloqueio) ? 'Atualizar Bloqueio' : 'Salvar Bloqueio' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Script para funcionalidades --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chk = document.getElementById('aplicar_todos');
            const customCheckbox = document.getElementById('customCheckbox');
            const sel = document.getElementById('funcionarios');

            // Toggle do checkbox customizado
            customCheckbox.addEventListener('click', function() {
                chk.checked = !chk.checked;
                toggleSelect();
                updateCustomCheckbox();
            });

            // Toggle do checkbox original (para acessibilidade)
            chk.addEventListener('change', function() {
                toggleSelect();
                updateCustomCheckbox();
            });

            function toggleSelect() {
                sel.disabled = chk.checked;
                if (chk.checked) {
                    // limpa seleção quando marcar "todos"
                    for (let option of sel.options) {
                        option.selected = false;
                    }
                }
            }

            function updateCustomCheckbox() {
                if (chk.checked) {
                    customCheckbox.classList.add('checked');
                } else {
                    customCheckbox.classList.remove('checked');
                }
            }

            // Inicializar estado
            toggleSelect();
            updateCustomCheckbox();
        });
    </script>

    @include('partials.toast')
@endsection
