@extends('layouts.app')

@section('title', 'Comissões - Estética PRO')

@section('content')
    {{-- ESTILOS ESPECÍFICOS DA PÁGINA (somente conteúdo de Comissões) --}}
    <style>
        :root {
            --primary: #ec4899;
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

        /* Título */
        .page-title {
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Cards / controles */
        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .05);
            padding: 20px;
            margin-bottom: 24px;
        }

        .grid {
            display: grid;
            gap: 16px;
        }

        .grid-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .stat {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat h4 {
            font-size: 14px;
            color: var(--text-light);
            font-weight: 600;
        }

        .stat .v {
            font-size: 22px;
            font-weight: 700;
        }

        .filter {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .input,
        .select {
            padding: 10px 12px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            background: #fff;
            font-family: inherit;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
            box-shadow: 0 4px 14px rgba(236, 72, 153, .4);
        }

        .btn-secondary {
            background: #0EA5E9;
            color: #fff;
        }

        /* Tabela */
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .table th {
            font-size: 12px;
            text-transform: uppercase;
            color: var(--text-light);
            text-align: left;
            padding: 8px 12px;
        }

        .table td {
            background: #fff;
            padding: 14px 12px;
        }

        .row {
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .badge {
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            display: inline-block;
        }

        .badge-pendente {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-pago {
            background: #dcfce7;
            color: #065f46;
        }

        .badge-estornado {
            background: #fee2e2;
            color: #991b1b;
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        @media(max-width:768px) {
            .grid-3 {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="content">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
            <h1 class="page-title">Comissões</h1>
        </div>

        @if (session('success'))
            <div class="card" style="border-left:4px solid var(--success);color:#065f46;background:#ecfdf5">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="card" style="border-left:4px solid var(--danger);color:#7f1d1d;background:#fee2e2">
                {{ session('error') }}
            </div>
        @endif

        <!-- Totais -->
        <div class="grid grid-3">
            <div class="card stat">
                <h4>Pendentes</h4>
                <div class="v">R$ {{ number_format($totais->pendente ?? 0, 2, ',', '.') }}</div>
            </div>
            <div class="card stat">
                <h4>Pagas</h4>
                <div class="v">R$ {{ number_format($totais->pago ?? 0, 2, ',', '.') }}</div>
            </div>
            <div class="card stat">
                <h4>Total</h4>
                <div class="v">R$ {{ number_format($totais->total ?? 0, 2, ',', '.') }}</div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card">
            <form method="GET" class="filter">
                <select name="funcionario_id" class="select">
                    <option value="">Todos os funcionários</option>
                    @foreach($funcionarios as $f)
                        <option value="{{ $f->id }}" {{ request('funcionario_id') == $f->id ? 'selected' : '' }}>
                            {{ $f->nome }}
                        </option>
                    @endforeach
                </select>

                <select name="status" class="select">
                    <option value="">Todos status</option>
                    @foreach(['pendente', 'pago', 'estornado'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>

                <input type="date" name="de" value="{{ request('de') }}" class="input">
                <input type="date" name="ate" value="{{ request('ate') }}" class="input">

                <button class="btn btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
                <a href="{{ route('comissoes.index') }}" class="btn btn-secondary"><i class="fas fa-eraser"></i> Limpar</a>
            </form>
        </div>

        <!-- Tabela -->
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Funcionário</th>
                        <th>Cliente</th>
                        <th>Serviço</th>
                        <th>Preço</th>
                        <th>%</th>
                        <th>Comissão</th>
                        <th>Status</th>
                        <th style="width:220px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($comissoes as $c)
                        <tr class="row">
                            <td>{{ \Carbon\Carbon::parse($c->data_atendimento)->format('d/m/Y H:i') }}</td>
                            <td>{{ $c->funcionario_nome }}</td>
                            <td>{{ $c->cliente_nome }}</td>
                            <td>{{ $c->servico_nome }}</td>
                            <td>R$ {{ number_format($c->valor_servico, 2, ',', '.') }}</td>
                            <td>{{ number_format($c->percentual, 2, ',', '.') }}%</td>
                            <td><strong>R$ {{ number_format($c->valor_comissao, 2, ',', '.') }}</strong></td>
                            <td>
                                @php $map = ['pendente' => 'badge-pendente', 'pago' => 'badge-pago', 'estornado' => 'badge-estornado']; @endphp
                                <span class="badge {{ $map[$c->status] ?? 'badge-pendente' }}">{{ ucfirst($c->status) }}</span>
                                @if($c->pago_em)
                                    <small style="display:block;color:var(--text-light)">em
                                        {{ \Carbon\Carbon::parse($c->pago_em)->format('d/m/Y') }}</small>
                                @endif
                            </td>
                            <td class="actions">
                                {{-- PENDENTE ou ESTORNADO: Mostra botão PAGAR --}}
                                @if($c->status === 'pendente' || $c->status === 'estornado')
                                    <form method="POST" action="{{ route('comissoes.pagar', $c->id) }}">
                                        @csrf
                                        <button class="btn btn-primary" onclick="return confirm('Marcar como paga?')">
                                            <i class="fas fa-check"></i> Pagar
                                        </button>
                                    </form>
                                @endif

                                {{-- PAGO: Mostra botão ESTORNAR --}}
                                @if($c->status === 'pago')
                                    <form method="POST" action="{{ route('comissoes.estornar', $c->id) }}">
                                        @csrf
                                        <button class="btn" style="background:#fee2e2;color:#991b1b"
                                            onclick="return confirm('Estornar esta comissão?')">
                                            <i class="fas fa-undo-alt"></i> Estornar
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="color:var(--text-light)">Nenhuma comissão encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Paginação custom Estética PRO --}}
            @if($comissoes instanceof \Illuminate\Contracts\Pagination\Paginator)
                @include('partials.pagination', ['paginator' => $comissoes])
            @endif

        </div>
    </div>

    {{-- Partials opcionais --}}
    @include('partials.change_password_modal')
    @include('partials.toast')
@endsection