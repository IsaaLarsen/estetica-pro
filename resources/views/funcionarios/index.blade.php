@extends('layouts.app')

@section('title', 'Funcionários - Estética PRO')

@section('content')
    {{-- Estilos ESPECÍFICOS desta página (somente conteúdo) --}}
    <style>
        :root{
            --primary:#ec4899; --primary-dark:#db2777; --primary-light:#fbcfe8;
            --secondary:#7e22ce; --text:#1f2937; --text-light:#6b7280;
            --success:#10b981; --warning:#f59e0b; --danger:#ef4444;
        }

        .content{ padding:11px; }

        .page-header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; gap:16px; flex-wrap:wrap; }
        .page-title{
            font-size:28px; font-weight:700;
            background:linear-gradient(135deg,var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
            margin:0;
        }
        .header-actions{ display:flex; gap:16px; align-items:center; flex-wrap:wrap; }

        /* Busca */
        .search-box{ position:relative; }
        .search-input{
            padding:12px 16px 12px 40px; border:2px solid #e5e7eb; border-radius:12px;
            font-size:14px; width:250px; transition:.3s;
        }
        .search-input:focus{ border-color:var(--primary); outline:none; box-shadow:0 0 0 3px rgba(236,72,153,.2); }
        .search-icon{ position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--text-light); }

        /* Botões */
        .btn{ display:flex; align-items:center; gap:8px; padding:12px 20px; border-radius:12px; font-weight:500; border:none; cursor:pointer; text-decoration:none; }
        .btn-primary{ background:linear-gradient(135deg,var(--primary),var(--secondary)); color:#fff; box-shadow:0 4px 14px rgba(236,72,153,.4); }
        .btn-primary:hover{ transform:translateY(-2px); box-shadow:0 6px 20px rgba(236,72,153,.5); }

        /* Tabela */
        .table-container{ background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.05); margin-bottom:30px; overflow-x:auto; }
        table{ width:100%; border-collapse:collapse; min-width:800px; }
        thead{ background:linear-gradient(135deg,var(--primary),var(--secondary)); color:#fff; }
        th{ padding:16px; text-align:left; font-weight:500; }
        tbody tr{ border-bottom:1px solid #f3f4f6; transition:background .3s; }
        tbody tr:last-child{ border-bottom:none; }
        tbody tr:hover{ background:#f9fafb; }
        td{ padding:16px; }

        .employee-info{ display:flex; align-items:center; }
        .employee-avatar{
            width:40px; height:40px; border-radius:50%;
            background:linear-gradient(135deg,var(--primary),var(--secondary));
            display:flex; align-items:center; justify-content:center; color:#fff; font-weight:600; margin-right:12px;
        }
        .employee-details h3{ font-size:16px; font-weight:500; margin-bottom:4px; }
        .employee-details p{ font-size:14px; color:var(--text-light); }

        .status-badge{ padding:6px 12px; border-radius:20px; font-size:12px; font-weight:500; display:inline-block; }
        .status-active{ background:#ecfdf5; color:var(--success); }
        .status-inactive{ background:#fef3c7; color:var(--warning); }

        .actions{ display:flex; gap:8px; }
        .action-btn-table{
            width:34px; height:34px; border-radius:8px; display:flex; align-items:center; justify-content:center;
            cursor:pointer; transition:.2s; background:none; border:none;
        }
        .action-edit{ color:var(--primary); background:var(--primary-light); }
        .action-edit:hover{ background:var(--primary); color:#fff; }
        .action-delete{ color:var(--danger); background:#fee2e2; }
        .action-delete:hover{ background:var(--danger); color:#fff; }

        /* Paginação visual */
        .pagination{ display:flex; justify-content:center; gap:8px; margin-top:30px; }
        .pagination-item{
            width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center;
            background:#fff; color:var(--text); box-shadow:0 2px 10px rgba(0,0,0,.05); cursor:pointer; transition:.2s;
        }
        .pagination-item:hover, .pagination-item.active{ background:var(--primary); color:#fff; }

        /* Context menu */
        #ctxMenu{
            position:fixed; top:0; left:0; z-index:1000; display:none;
            background:#fff; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,.15); min-width:200px;
        }
        #ctxMenu button{
            width:100%; padding:12px 16px; border:none; background:none; text-align:left; cursor:pointer;
            font-family:inherit; font-size:14px; display:flex; gap:10px; align-items:center;
        }

        /* Modal reset senha */
        #resetModal{
            position:fixed; inset:0; display:none; align-items:center; justify-content:center; z-index:1100; background:rgba(0,0,0,.35);
        }
        #resetModal .box{
            background:#fff; width:100%; max-width:420px; border-radius:16px; padding:22px; box-shadow:0 20px 40px rgba(0,0,0,.2);
        }
        #resetModal .box h3{ margin-bottom:12px; font-weight:700; font-size:18px; color:var(--text); }
        #resetModal .box p{ margin-bottom:16px; color:var(--text-light); font-size:14px; }
        #resetModal .box input{
            width:100%; padding:12px 14px; border:2px solid #e5e7eb; border-radius:12px; font-size:14px; margin-bottom:14px;
        }

        @media (max-width:768px){
            .search-input{ width:100%; }
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
                <a href="{{ route('funcionarios.create') }}" class="btn btn-primary" style="text-decoration:none;">
                    <i class="fas fa-plus"></i> Novo Funcionário
                </a>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Funcionário</th>
                        <th>CPF</th>
                        <th>Cargo</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($funcionarios as $f)
                        <tr class="employee-row"
                            data-funcionario-id="{{ $f->id }}"
                            data-usuario-id="{{ $f->usuario_id ?? '' }}"
                            data-email="{{ $f->email ?? '' }}">
                            <td>
                                <div class="employee-info">
                                    <div class="employee-avatar">
                                        {{ strtoupper(mb_substr(explode(' ', $f->nome)[0] ?? '', 0, 1)) }}{{ strtoupper(mb_substr(explode(' ', $f->nome)[1] ?? '', 0, 1)) }}
                                    </div>
                                    <div class="employee-details">
                                        <h3>{{ $f->nome }}</h3>
                                        <p>{{ $f->email ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $f->cpf }}</td>
                            <td>{{ $f->cargo ?? '—' }}</td>
                            <td>
                                @php $ativo = $f->ativo ?? 1; @endphp
                                <span class="status-badge {{ $ativo ? 'status-active' : 'status-inactive' }}">
                                    {{ $ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('funcionarios.edit', $f->id) }}"
                                       class="action-btn-table action-edit" title="Editar">
                                        <i class="fas fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('funcionarios.destroy', $f->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn-table action-delete" title="Excluir"
                                                onclick="return confirm('Tem certeza que deseja excluir este funcionário?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:32px; color:var(--text-light);">
                                <i class="fas fa-users" style="font-size:28px; color:#e5e7eb; display:block; margin-bottom:10px;"></i>
                                Nenhum funcionário cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginação visual (se usar paginação do Laravel, substitua por {{ $funcionarios->links() }}) --}}
        <div class="pagination">
            <div class="pagination-item"><i class="fas fa-chevron-left"></i></div>
            <div class="pagination-item active">1</div>
            <div class="pagination-item">2</div>
            <div class="pagination-item">3</div>
            <div class="pagination-item"><i class="fas fa-chevron-right"></i></div>
        </div>
    </div>

    {{-- ===== Context Menu (botão direito) ===== --}}
    <div id="ctxMenu">
        <button id="ctxResetSenha">
            <i class="fas fa-key" style="color:#6b7280;"></i> Redefinir senha
        </button>
    </div>

    {{-- ===== Modal Redefinir Senha ===== --}}
    <div id="resetModal">
        <div class="box">
            <h3>Redefinir senha</h3>
            <p>Defina a nova senha do usuário vinculado a este funcionário.</p>

            <form id="resetForm" method="POST">
                @csrf
                <input type="password" name="nova_senha" placeholder="Nova senha" required>
                <div style="display:flex; gap:10px; justify-content:flex-end;">
                    <button type="button" id="btnCancelReset" class="btn" style="background:#f3f4f6; color:#374151;">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Scripts ESPECÍFICOS da página --}}
    <script>
        // Busca local
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const term = this.value.toLowerCase();
                document.querySelectorAll('tbody tr').forEach(row => {
                    const text = row.innerText.toLowerCase();
                    row.style.display = text.includes(term) ? '' : 'none';
                });
            });
        }

        // ===== Right-click Context Menu =====
        const ctxMenu = document.getElementById('ctxMenu');
        const ctxResetSenha = document.getElementById('ctxResetSenha');
        let ctxTarget = null; // guarda a <tr> clicada

        document.querySelectorAll('.employee-row').forEach(row => {
            row.addEventListener('contextmenu', (e) => {
                e.preventDefault();
                ctxTarget = row;

                // posiciona menu
                ctxMenu.style.display = 'block';
                const w = ctxMenu.offsetWidth || 200, h = ctxMenu.offsetHeight || 48;
                const vw = window.innerWidth, vh = window.innerHeight;
                let x = e.clientX, y = e.clientY;
                if (x + w > vw) x = vw - w - 8;
                if (y + h > vh) y = vh - h - 8;
                ctxMenu.style.left = x + 'px';
                ctxMenu.style.top = y + 'px';
            });
        });

        // fechar menu ao clicar fora/rolar/esc
        const closeCtx = () => { ctxMenu.style.display = 'none'; };
        document.addEventListener('click', (e) => { if (!ctxMenu.contains(e.target)) closeCtx(); });
        document.addEventListener('scroll', closeCtx, true);
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeCtx(); });

        // ===== Modal Redefinir Senha =====
        const resetModal = document.getElementById('resetModal');
        const resetForm  = document.getElementById('resetForm');
        const btnCancelReset = document.getElementById('btnCancelReset');

        const openResetModal = () => { resetModal.style.display = 'flex'; };
        const closeResetModal = () => { resetModal.style.display = 'none'; resetForm.reset(); };

        btnCancelReset.addEventListener('click', closeResetModal);
        resetModal.addEventListener('click', (e) => { if (e.target === resetModal) closeResetModal(); });

        // Ao clicar em "Redefinir senha" no menu
        ctxResetSenha.addEventListener('click', () => {
            closeCtx();
            if (!ctxTarget) return;

            const funcionarioId = ctxTarget.dataset.funcionarioId;
            // Define a action para a rota POST /funcionarios/{id}/reset-senha
            resetForm.action = "{{ url('funcionarios') }}/" + funcionarioId + "/reset-senha";
            openResetModal();
        });
    </script>

    @include('partials.change_password_modal')
    @include('partials.toast')
@endsection
