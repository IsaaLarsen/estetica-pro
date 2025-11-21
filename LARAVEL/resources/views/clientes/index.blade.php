@extends('layouts.app')

@section('title', 'Clientes - Estética PRO')

@section('content')
    {{-- ===== ESTILOS ESPECÍFICOS (mantidos) ===== --}}
    <style>
        :root {
            --primary:#ec4899; --primary-dark:#db2777; --primary-light:#fbcfe8;
            --secondary:#7e22ce; --text:#1f2937; --text-light:#6b7280;
            --success:#10b981; --warning:#f59e0b; --danger:#ef4444; --sidebar-width:260px;
        }
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Poppins',sans-serif;background-color:#f9fafb;color:var(--text)}

        .content{padding:11px}
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;gap:16px;flex-wrap:wrap}
        .page-title{font-size:28px;font-weight:700;background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
        .header-actions{display:flex;gap:16px}

        .search-box{position:relative}
        .search-input{padding:12px 16px 12px 40px;border:2px solid #e5e7eb;border-radius:12px;font-size:14px;width:250px;transition:.3s}
        .search-input:focus{border-color:var(--primary);outline:none;box-shadow:0 0 0 3px rgba(236,72,153,.2)}
        .search-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-light)}

        .btn{display:flex;align-items:center;padding:12px 20px;border-radius:12px;font-weight:500;cursor:pointer;transition:.3s;border:none;text-decoration:none}
        .btn-primary{background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);color:#fff;box-shadow:0 4px 14px rgba(236,72,153,.4)}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(236,72,153,.5)}
        .btn-icon{margin-right:8px}

        .table-container{background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.05);margin-bottom:30px;overflow-x:auto}
        table{width:100%;border-collapse:collapse;min-width:800px}
        thead{background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);color:#fff}
        th{padding:16px;text-align:left;font-weight:500}
        td{padding:16px}
        tbody tr{border-bottom:1px solid #f3f4f6;transition:background .3s}
        tbody tr:last-child{border-bottom:none}
        tbody tr:hover{background:#f9fafb}

        .employee-info{display:flex;align-items:center}
        .employee-avatar{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--primary) 0%,var(--secondary) 100%);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;margin-right:12px}
        .employee-details h3{font-size:16px;font-weight:500;margin-bottom:4px}
        .employee-details p{font-size:14px;color:var(--text-light)}

        .status-badge{padding:6px 12px;border-radius:20px;font-size:12px;font-weight:500;display:inline-block}
        .status-active{background:#ecfdf5;color:var(--success)}
        .status-inactive{background:#fef3c7;color:var(--warning)}

        .actions{display:flex;gap:8px}
        .action-btn-table{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:.3s;background:none;border:none}
        .action-edit{color:var(--primary);background:var(--primary-light)}
        .action-edit:hover{background:var(--primary);color:#fff}
        .action-delete{color:var(--danger);background:#fee2e2}
        .action-delete:hover{background:var(--danger);color:#fff}

        .pagination{display:flex;justify-content:center;gap:8px;margin-top:30px}
        .pagination-item{width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:.3s;background:#fff;color:var(--text);box-shadow:0 2px 10px rgba(0,0,0,.05)}
        .pagination-item:hover,.pagination-item.active{background:var(--primary);color:#fff}

        @media (max-width:768px){
            .page-header{flex-direction:column;align-items:flex-start}
            .header-actions{width:100%;justify-content:space-between}
            .search-input{width:100%}
        }
    </style>

    <div class="content">
        <div class="page-header">
            <h1 class="page-title">Clientes</h1>
            <div class="header-actions">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Buscar cliente...">
                </div>
                <a href="{{ route('clientes.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus btn-icon"></i> Novo Cliente
                </a>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>CPF</th>
                        <th>Telefone</th>
                        <th>Email</th>
                        <th>Endereço</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clientes as $c)
                        <tr>
                            <td>
                                <div class="employee-info">
                                    <div class="employee-avatar">
                                        {{ strtoupper(mb_substr(explode(' ', $c->nome)[0] ?? '', 0, 1)) }}{{ strtoupper(mb_substr(explode(' ', $c->nome)[1] ?? '', 0, 1)) }}
                                    </div>
                                    <div class="employee-details">
                                        <h3>{{ $c->nome }}</h3>
                                        <p>{{ $c->email ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $c->cpf ?? '—' }}</td>
                            <td>{{ $c->telefone ?? '—' }}</td>
                            <td>{{ $c->email ?? '—' }}</td>
                            <td>{{ $c->endereco ?? '—' }}</td>
                            <td>
                                @php $ativo = $c->ativo ?? 1; @endphp
                                <span class="status-badge {{ $ativo ? 'status-active' : 'status-inactive' }}">
                                    {{ $ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('clientes.edit', $c->id) }}" class="action-btn-table action-edit" title="Editar">
                                        <i class="fas fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('clientes.destroy', $c->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn-table action-delete" title="Excluir"
                                                onclick="return confirm('Tem certeza que deseja excluir este cliente?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:32px; color:var(--text-light);">
                                <i class="fas fa-user" style="font-size:28px; color:#e5e7eb; display:block; margin-bottom:10px;"></i>
                                Nenhum cliente cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($clientes, 'links'))
            <div style="display:flex;justify-content:center;margin-top:16px;">
                {{ $clientes->onEachSide(1)->links() }}
            </div>
        @else
            <div class="pagination">
                <div class="pagination-item"><i class="fas fa-chevron-left"></i></div>
                <div class="pagination-item active">1</div>
                <div class="pagination-item">2</div>
                <div class="pagination-item">3</div>
                <div class="pagination-item"><i class="fas fa-chevron-right"></i></div>
            </div>
        @endif
    </div>

    {{-- ===== SCRIPTS ESPECÍFICOS (mantidos) ===== --}}
    <script>
        // Busca local na tabela
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
    </script>

    {{-- Partials padrão do app --}}
    @include('partials.change_password_modal')
    @include('partials.toast')
@endsection
