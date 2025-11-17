@extends('layouts.app')

@section('title', 'Serviços - Estética PRO')

@section('content')
    {{-- Estilos específicos da página --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root{
            --primary:#ec4899; --primary-dark:#db2777; --primary-light:#fbcfe8;
            --secondary:#7e22ce; --text:#1f2937; --text-light:#6b7280;
            --success:#10b981; --warning:#f59e0b; --danger:#ef4444;
        }

        .content{ padding:11px; }
        .page-header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; flex-wrap:wrap; gap:16px; }
        .page-title{
            font-size:28px; font-weight:700;
            background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        }
        .header-actions{ display:flex; gap:16px; flex-wrap:wrap; }
        .search-box{ position:relative; }
        .search-input{
            padding:12px 16px 12px 40px; border:2px solid #e5e7eb; border-radius:12px;
            font-size:14px; width:250px; transition:.3s;
        }
        .search-input:focus{ border-color:var(--primary); outline:none; box-shadow:0 0 0 3px rgba(236,72,153,.2); }
        .search-icon{ position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--text-light); }

        .btn{ display:flex; align-items:center; gap:8px; padding:12px 20px; border-radius:12px; font-weight:500; text-decoration:none; border:none; cursor:pointer; }
        .btn-primary{ background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%); color:#fff; box-shadow:0 4px 14px rgba(236,72,153,.4); }
        .btn-primary:hover{ transform:translateY(-2px); box-shadow:0 6px 20px rgba(236,72,153,.5); }

        .table-container{ background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.05); margin-bottom:30px; overflow-x:auto; }
        table{ width:100%; border-collapse:collapse; min-width:800px; }
        thead{ background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%); color:#fff; }
        th{ padding:16px; text-align:left; font-weight:500; }
        td{ padding:16px; }
        tbody tr{ border-bottom:1px solid #f3f4f6; transition:.2s; }
        tbody tr:hover{ background:#f9fafb; transform:translateY(-2px); box-shadow:0 4px 8px rgba(0,0,0,.05); }

        .service-info{ display:flex; align-items:center; }
        .service-details h3{ font-size:16px; font-weight:500; margin-bottom:4px; }
        .service-details p{ font-size:14px; color:var(--text-light); }

        .status-badge{ padding:6px 12px; border-radius:20px; font-size:12px; font-weight:500; display:inline-block; }
        .status-active{ background:#ecfdf5; color:#10b981; }
        .status-inactive{ background:#fef3c7; color:#f59e0b; }

        .actions{ display:flex; gap:8px; }
        .action-btn-table{ width:34px; height:34px; border-radius:8px; display:flex; align-items:center; justify-content:center; border:none; background:none; cursor:pointer; transition:.2s; }
        .action-edit{ color:var(--primary); background:var(--primary-light); }
        .action-edit:hover{ background:var(--primary); color:#fff; }
        .action-delete{ color:var(--danger); background:#fee2e2; }
        .action-delete:hover{ background:var(--danger); color:#fff; }

        .pagination{ display:flex; justify-content:center; gap:8px; margin-top:30px; }
        .pagination-item{ width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; background:#fff; color:var(--text); box-shadow:0 2px 10px rgba(0,0,0,.05); }
        .pagination-item.active, .pagination-item:hover{ background:var(--primary); color:#fff; }

        .alert{ padding:16px; border-radius:12px; margin-bottom:20px; display:flex; align-items:center; gap:12px; }
        .alert-success{ background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
        .alert-error{ background:#fef3c7; color:#7c2d12; border:1px solid #fde68a; }
    </style>

    <div class="content">
        <div class="page-header">
            <h1 class="page-title">Serviços</h1>

            <div class="header-actions">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="searchInput" class="search-input" placeholder="Buscar serviço...">
                </div>
                <a href="{{ route('servicos.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Novo Serviço
                </a>
            </div>
        </div>

        {{-- Mensagens --}}
        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Serviço</th>
                        <th>Valor (R$)</th>
                        <th>% Comissão</th>
                        <th>Duração (min)</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($servicos as $s)
                    <tr>
                        <td>
                            <div class="service-info">
                                <div class="service-details">
                                    <h3>{{ $s->nome }}</h3>
                                    <p>{{ Str::limit($s->descricao, 30) ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td>R$ {{ number_format($s->valor, 2, ',', '.') }}</td>
                        <td>{{ number_format($s->comissao_percent, 2, ',', '.') }}%</td>
                        <td>{{ $s->duracao_minutos }}</td>
                        <td>
                            <span class="status-badge {{ $s->ativo ? 'status-active' : 'status-inactive' }}">
                                {{ $s->ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('servicos.edit', $s->id) }}" class="action-btn-table action-edit" title="Editar">
                                    <i class="fas fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('servicos.destroy', $s->id) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn-table action-delete" title="Excluir"
                                            onclick="return confirm('Tem certeza que deseja excluir este serviço?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding:32px; color:var(--text-light);">
                            <i class="fas fa-scissors" style="font-size:28px; color:#e5e7eb; display:block; margin-bottom:10px;"></i>
                            Nenhum serviço cadastrado.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginação (se for paginator) --}}
        @if (method_exists($servicos, 'hasPages') && $servicos->hasPages())
            <div class="pagination">
                @if($servicos->onFirstPage())
                    <div class="pagination-item"><i class="fas fa-chevron-left"></i></div>
                @else
                    <a href="{{ $servicos->previousPageUrl() }}" class="pagination-item"><i class="fas fa-chevron-left"></i></a>
                @endif

                @for ($i = 1; $i <= $servicos->lastPage(); $i++)
                    @if ($i == $servicos->currentPage())
                        <div class="pagination-item active">{{ $i }}</div>
                    @else
                        <a href="{{ $servicos->url($i) }}" class="pagination-item">{{ $i }}</a>
                    @endif
                @endfor

                @if($servicos->hasMorePages())
                    <a href="{{ $servicos->nextPageUrl() }}" class="pagination-item"><i class="fas fa-chevron-right"></i></a>
                @else
                    <div class="pagination-item"><i class="fas fa-chevron-right"></i></div>
                @endif
            </div>
        @endif
    </div>

    <script>
        // dropdown do layout (se existir)
        const settingsBtn = document.getElementById('settingsBtn');
        const settingsMenu = document.getElementById('settingsMenu');
        if (settingsBtn && settingsMenu) {
            settingsBtn.addEventListener('click', e => { e.stopPropagation(); settingsMenu.classList.toggle('active'); });
            document.addEventListener('click', e => { if (!settingsMenu.contains(e.target) && e.target !== settingsBtn) settingsMenu.classList.remove('active'); });
        }

        // busca local
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const term = this.value.toLowerCase();
                document.querySelectorAll('tbody tr').forEach(row => {
                    const text = row.innerText.toLowerCase();
                    row.style.display = text.includes(term) ? '' : 'none';
                });
            });
        }

        const editPasswordBtn = document.getElementById('editPasswordBtn');
        if (editPasswordBtn) {
            editPasswordBtn.addEventListener('click', () => {
                alert('Funcionalidade de editar senha será implementada aqui.');
                settingsMenu?.classList.remove('active');
            });
        }
    </script>

    @include('partials.change_password_modal')
    @include('partials.toast')
@endsection
