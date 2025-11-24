@extends('layouts.app')

@section('title', 'Funcionários - Estética PRO')

@section('content')
    <style>
        :root {
            --primary: #ec4899;
            --primary-dark: #db2777;
            --primary-light: #fbcfe8;
            --secondary: #7e22ce;
            --text: #1f2937;
            --text-light: #6b7280;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        .content {
            padding: 11px;
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
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
        }

        .search-input {
            padding: 12px 16px 12px 40px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 14px;
            width: 250px;
            transition: .3s;
        }

        .search-input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(236, 72, 153, .2);
        }

        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }

        .btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
            box-shadow: 0 4px 14px rgba(236, 72, 153, .4);
            transition: .2s;
        }

        .btn-primary:hover {
            /* sem translate pra não “andar” */
            box-shadow: 0 6px 20px rgba(236, 72, 153, .5);
        }

        .btn-secondary {
            background: #e5e7eb;
            color: var(--text);
            transition: .2s;
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        .table-container {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .05);
            margin-bottom: 30px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 950px;
            table-layout: fixed;
        }

        thead {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
        }

        th {
            padding: 16px;
            text-align: left;
            font-weight: 500;
            white-space: nowrap;
        }

        th:nth-child(1) { width: 280px; }
        th:nth-child(2) { width: 140px; }
        th:nth-child(3) { width: 140px; }
        th:nth-child(4) { width: 170px; }
        th:nth-child(5) { width: 120px; }
        th:nth-child(6) { width: 120px; text-align: center; }

        td {
            padding: 16px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .employee-info {
            display: flex;
            align-items: center;
        }

        .employee-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
            margin-right: 12px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .status-active {
            background: #ecfdf5;
            color: var(--success);
        }

        .status-inactive {
            background: #fef3c7;
            color: var(--warning);
        }

        .actions {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .action-btn-table {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: .2s;
            background: none;
            border: none;
        }

        .action-edit {
            color: var(--primary);
            background: var(--primary-light);
        }

        .action-edit:hover {
            background: var(--primary);
            color: #fff;
        }

        .action-delete {
            color: var(--danger);
            background: #fee2e2;
        }

        .action-delete:hover {
            background: var(--danger);
            color: #fff;
        }

        /* MENU DE CONTEXTO (BOTÃO DIREITO) */
        .context-menu {
            position: absolute;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, .18);
            padding: 4px 0;
            min-width: 210px;
            z-index: 9999;
            display: none;
            border: 1px solid #e5e7eb;
        }

        .context-menu.show {
            display: block;
            animation: fadeInScale .12s ease-out;
        }

        .context-menu-item {
            padding: 8px 14px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            color: var(--text);
            white-space: nowrap;
        }

        .context-menu-item i {
            width: 18px;
            text-align: center;
        }

        .context-menu-item:hover {
            background: rgba(236, 72, 153, .06);
        }

        .context-menu-item[data-hidden="true"] {
            display: none;
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(.96);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* MODAIS */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }

        .modal-backdrop.show {
            display: flex;
        }

        .modal {
            background: #fff;
            border-radius: 16px;
            padding: 24px 24px 20px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 20px 45px rgba(15, 23, 42, .3);
            animation: fadeInScale .14s ease-out;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text);
        }

        .modal-close {
            border: none;
            background: none;
            font-size: 18px;
            cursor: pointer;
            color: var(--text-light);
        }

        .modal-body {
            margin-bottom: 18px;
            font-size: 14px;
            color: var(--text-light);
        }

        .form-group-modal {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group-modal label {
            font-size: 13px;
            font-weight: 500;
            color: var(--text);
        }

        .form-control-modal,
        .select-modal {
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            padding: 10px 12px;
            font-size: 14px;
            outline: none;
        }

        .form-control-modal:focus,
        .select-modal:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(236, 72, 153, .18);
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 6px;
        }
    </style>

    <div class="content">
        <div class="page-header">
            <h1 class="page-title">Funcionários</h1>

            <div class="header-actions">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Buscar funcionário...">
                </div>

                <a href="{{ route('funcionarios.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Novo Funcionário
                </a>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Funcionário</th>
                        <th>Data Nasc.</th>
                        <th>CPF</th>
                        <th>Cargo</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($funcionarios as $f)
                        <tr
                            data-funcionario-row="1"
                            data-cpf="{{ $f->cpf }}"
                            data-nome="{{ $f->nome }}"
                            data-tipo="{{ $f->tipo_usuario ?? '' }}"
                            data-bloqueado="{{ ($f->usuario_bloqueado ?? 0) ? '1' : '0' }}"
                        >
                            <td>
                                <div class="employee-info">
                                    <div class="employee-avatar">
                                        {{ strtoupper(mb_substr(explode(' ', $f->nome)[0], 0, 1)) }}{{ strtoupper(mb_substr(explode(' ', $f->nome)[1] ?? '', 0, 1)) }}
                                    </div>
                                    <div>
                                        <h3 style="font-size:16px; margin:0 0 4px;">{{ $f->nome }}</h3>
                                        <p style="color:var(--text-light); font-size:14px;">
                                            {{ $f->email ?? '—' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td>
                                @if($f->data_nascimento)
                                    {{ \Carbon\Carbon::parse($f->data_nascimento)->format('d/m/Y') }}
                                @else
                                    —
                                @endif
                            </td>

                            <td>{{ $f->cpf }}</td>
                            <td>{{ $f->cargo ?? '—' }}</td>

                            <td>
                                <span class="status-badge {{ ($f->ativo ?? 1) ? 'status-active' : 'status-inactive' }}">
                                    {{ ($f->ativo ?? 1) ? 'Ativo' : 'Inativo' }}
                                </span>
                                @if(($f->usuario_bloqueado ?? 0) == 1)
                                    <span class="status-badge" style="background:#fee2e2; color:#b91c1c; margin-left:6px;">
                                        Bloqueado
                                    </span>
                                @endif
                            </td>

                            <td style="width:120px;">
                                <div class="actions">
                                    <a href="{{ route('funcionarios.edit', $f->id) }}" class="action-btn-table action-edit">
                                        <i class="fas fa-pen-to-square"></i>
                                    </a>

                                    <form method="POST" action="{{ route('funcionarios.destroy', $f->id) }}"
                                          onsubmit="return confirm('Tem certeza que deseja excluir?');">
                                        @csrf @method('DELETE')
                                        <button class="action-btn-table action-delete" type="submit">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding:32px; color:var(--text-light);">
                                <i class="fas fa-users" style="font-size:28px; color:#e5e7eb;"></i>
                                Nenhum funcionário cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginação padrão --}}
        @if($funcionarios instanceof \Illuminate\Contracts\Pagination\Paginator)
            @include('partials.pagination', ['paginator' => $funcionarios])
        @endif
    </div>

    {{-- MENU DE CONTEXTO (GLOBAL) --}}
    <div id="employee-context-menu" class="context-menu">
        <div class="context-menu-item" id="ctx-redefinir-senha">
            <i class="fas fa-key"></i>
            Redefinir senha
        </div>
        <div class="context-menu-item" id="ctx-alterar-tipo">
            <i class="fas fa-user-tag"></i>
            Alterar tipo
        </div>
        <div class="context-menu-item" id="ctx-desbloquear-conta" data-hidden="true">
            <i class="fas fa-unlock"></i>
            Desbloquear conta
        </div>
    </div>

    {{-- MODAL: REDEFINIR SENHA --}}
    <div id="modal-senha-backdrop" class="modal-backdrop">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Redefinir senha</span>
                <button type="button" class="modal-close" data-close-modal="senha">&times;</button>
            </div>
            <div class="modal-body">
                <p id="modal-senha-texto">Informe a nova senha para o usuário vinculado.</p>

                <form id="form-modal-senha" method="POST" action="{{ route('funcionarios.reset-senha-por-cpf') }}">
                    @csrf
                    <input type="hidden" name="cpf" value="">
                    <div class="form-group-modal">
                        <label for="nova_senha_modal">Nova senha</label>
                        <input type="password" id="nova_senha_modal" name="nova_senha"
                               class="form-control-modal" required minlength="6">
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-close-modal="senha">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: ALTERAR TIPO --}}
    <div id="modal-tipo-backdrop" class="modal-backdrop">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Alterar tipo de usuário</span>
                <button type="button" class="modal-close" data-close-modal="tipo">&times;</button>
            </div>
            <div class="modal-body">
                <p id="modal-tipo-texto">Selecione o novo tipo para o usuário vinculado.</p>

                <form id="form-modal-tipo" method="POST" action="{{ route('funcionarios.alterar-tipo-por-cpf') }}">
                    @csrf
                    <input type="hidden" name="cpf" value="">
                    <div class="form-group-modal">
                        <label for="tipo_modal">Tipo</label>
                        <select id="tipo_modal" name="tipo" class="select-modal" required>
                            <option value="admin">Admin</option>
                            <option value="funcionario">Funcionário</option>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-close-modal="tipo">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- FORMULÁRIO ESCONDIDO: DESBLOQUEAR CONTA --}}
    <form id="form-desbloquear-conta" method="POST"
          action="{{ route('funcionarios.desbloquear-conta') }}"
          style="display:none;">
        @csrf
        <input type="hidden" name="cpf" value="">
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const menu = document.getElementById('employee-context-menu');

            const modalSenhaBackdrop = document.getElementById('modal-senha-backdrop');
            const modalTipoBackdrop = document.getElementById('modal-tipo-backdrop');

            const formModalSenha = document.getElementById('form-modal-senha');
            const formModalTipo = document.getElementById('form-modal-tipo');
            const formDesbloquear = document.getElementById('form-desbloquear-conta');

            const btnRedefinir   = document.getElementById('ctx-redefinir-senha');
            const btnAlterarTipo = document.getElementById('ctx-alterar-tipo');
            const btnDesbloquear = document.getElementById('ctx-desbloquear-conta');

            const modalSenhaTexto = document.getElementById('modal-senha-texto');
            const modalTipoTexto  = document.getElementById('modal-tipo-texto');

            // ELEMENTOS DO MODAL GLOBAL DE CONFIRMAÇÃO (layouts.app)
            const confirmModal   = document.getElementById('confirmModal');
            const confirmMessage = document.getElementById('confirmMessage');
            const confirmOk      = document.getElementById('confirmOk');
            const confirmCancel  = document.getElementById('confirmCancel');

            let currentCpf   = null;
            let currentNome  = null;
            let currentTipo  = null;
            let currentBloq  = '0';

            /**
             * Usa o popup padrão do layout para confirmar uma ação.
             * Se por algum motivo o modal global não existir, cai no window.confirm().
             */
            function openConfirm(message, onConfirm) {
                if (!confirmModal || !confirmMessage || !confirmOk || !confirmCancel) {
                    if (window.confirm(message)) {
                        onConfirm();
                    }
                    return;
                }

                confirmMessage.textContent = message;
                confirmModal.style.display = 'flex';

                const okHandler = () => {
                    cleanup();
                    onConfirm();
                };

                const cancelHandler = () => {
                    cleanup();
                };

                function cleanup() {
                    confirmModal.style.display = 'none';
                    confirmOk.removeEventListener('click', okHandler);
                    confirmCancel.removeEventListener('click', cancelHandler);
                }

                confirmOk.addEventListener('click', okHandler);
                confirmCancel.addEventListener('click', cancelHandler);
            }

            // Mostra menu ao clicar com botão direito na linha
            document.querySelectorAll('tr[data-funcionario-row]').forEach(row => {
                row.addEventListener('contextmenu', function (e) {
                    e.preventDefault();

                    currentCpf  = this.dataset.cpf;
                    currentNome = this.dataset.nome;
                    currentTipo = this.dataset.tipo || '';
                    currentBloq = this.dataset.bloqueado || '0';

                    // controla visibilidade do item "Desbloquear conta"
                    if (currentBloq === '1') {
                        btnDesbloquear.setAttribute('data-hidden', 'false');
                        btnDesbloquear.style.display = 'flex';
                    } else {
                        btnDesbloquear.setAttribute('data-hidden', 'true');
                        btnDesbloquear.style.display = 'none';
                    }

                    menu.style.top  = e.pageY + 'px';
                    menu.style.left = e.pageX + 'px';
                    menu.classList.add('show');
                });
            });

            // Esconde menu ao clicar em qualquer lugar
            document.addEventListener('click', function () {
                menu.classList.remove('show');
            });

            // Esconde ao rolar a página
            window.addEventListener('scroll', () => {
                menu.classList.remove('show');
            });

            function openModalSenha() {
                if (!currentCpf) return;
                formModalSenha.querySelector('input[name="cpf"]').value = currentCpf;
                formModalSenha.querySelector('#nova_senha_modal').value = '';
                modalSenhaTexto.textContent =
                    'Informe a nova senha para o usuário vinculado a "' + currentNome + '".';
                modalSenhaBackdrop.classList.add('show');
            }

            function openModalTipo() {
                if (!currentCpf) return;

                formModalTipo.querySelector('input[name="cpf"]').value = currentCpf;

                const select = formModalTipo.querySelector('#tipo_modal');

                if (currentTipo === 'admin' || currentTipo === 'funcionario') {
                    select.value = currentTipo;
                } else {
                    select.value = 'funcionario';
                }

                modalTipoTexto.textContent =
                    'Selecione o novo tipo para o usuário vinculado a "' + currentNome + '".';

                modalTipoBackdrop.classList.add('show');
            }

            function closeModal(tipo) {
                if (tipo === 'senha') {
                    modalSenhaBackdrop.classList.remove('show');
                }
                if (tipo === 'tipo') {
                    modalTipoBackdrop.classList.remove('show');
                }
            }

            // Botões do menu de contexto
            btnRedefinir.addEventListener('click', function (e) {
                e.stopPropagation();
                menu.classList.remove('show');
                openModalSenha();
            });

            btnAlterarTipo.addEventListener('click', function (e) {
                e.stopPropagation();
                menu.classList.remove('show');
                openModalTipo();
            });

            // DESBLOQUEAR CONTA — usa popup global de confirmação
            btnDesbloquear.addEventListener('click', function (e) {
                e.stopPropagation();
                menu.classList.remove('show');

                if (!currentCpf) return;

                const mensagem = 'Deseja realmente desbloquear a conta do usuário "' + currentNome + '"?';

                openConfirm(mensagem, function () {
                    // preenche CPF e envia o form escondido
                    formDesbloquear.querySelector('input[name="cpf"]').value = currentCpf;
                    formDesbloquear.submit();
                });
            });

            // Botões de fechar dos modais internos
            document.querySelectorAll('[data-close-modal="senha"]').forEach(el => {
                el.addEventListener('click', function () {
                    closeModal('senha');
                });
            });

            document.querySelectorAll('[data-close-modal="tipo"]').forEach(el => {
                el.addEventListener('click', function () {
                    closeModal('tipo');
                });
            });

            // Fechar modal clicando fora
            modalSenhaBackdrop.addEventListener('click', function (e) {
                if (e.target === modalSenhaBackdrop) {
                    closeModal('senha');
                }
            });
            modalTipoBackdrop.addEventListener('click', function (e) {
                if (e.target === modalTipoBackdrop) {
                    closeModal('tipo');
                }
            });
        });
    </script>
@endsection
