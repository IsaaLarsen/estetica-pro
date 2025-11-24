@extends('layouts.app')

@section('title', 'Relatório de Comissões - Estética PRO')

@section('content')
    <style>
        .content {
            padding: 11px;
            flex: 1;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .page-title {
            font-size: 26px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .filters-card,
        .table-card {
            background: #fff;
            border-radius: 16px;
            padding: 18px 20px;
            box-shadow: 0 4px 18px rgba(0,0,0,.06);
            margin-bottom: 18px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 12px;
        }

        .filters-grid label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--text);
            margin-bottom: 4px;
        }

        .filters-grid input,
        .filters-grid select {
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            transition: all .2s ease;
        }

        .filters-grid input:focus,
        .filters-grid select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(236,72,153,.2);
        }

        .filters-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 4px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            padding: 10px 16px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all .2s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: #fff;
            box-shadow: 0 4px 14px rgba(236,72,153,.4);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(236,72,153,.5);
        }

        .btn-outline {
            background: #fff;
            border: 1px solid #e5e7eb;
            color: var(--text);
        }

        .btn-outline:hover {
            background: #f9fafb;
        }

        .btn-icon {
            margin-right: 6px;
        }

        .total-box {
            margin-top: 8px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
        }

        .total-box span {
            color: var(--primary-dark);
        }

        .table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 720px;
        }

        thead {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: #fff;
        }

        th, td {
            padding: 10px 12px;
            font-size: 13px;
            text-align: left;
        }

        tbody tr {
            border-bottom: 1px solid #f3f4f6;
            transition: background .2s ease;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }

        .st-pendente {
            background: #fef3c7;
            color: #b45309;
        }

        .st-pago {
            background: #ecfdf5;
            color: #047857;
        }

        .st-estornado {
            background: #fee2e2;
            color: #b91c1c;
        }

        .text-muted {
            color: var(--text-light);
            font-size: 12px;
        }

        @media (max-width: 992px) {
            .filters-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .filters-grid {
                grid-template-columns: 1fr;
            }
            .filters-actions {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>

    <div class="content">
        <div class="page-header">
            <h1 class="page-title">Relatório de Comissões</h1>
            <a href="{{ route('relatorios.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left btn-icon"></i> Voltar aos relatórios
            </a>
        </div>

        {{-- FILTROS --}}
        <div class="filters-card">
            <form method="GET" action="{{ route('relatorios.comissoes') }}" id="filtroComissoesForm">
                <div class="filters-grid">
                    <div>
                        <label for="data_inicio">Data inicial</label>
                        <input type="date" id="data_inicio" name="data_inicio"
                               value="{{ request('data_inicio', $inicio->format('Y-m-d')) }}">
                    </div>
                    <div>
                        <label for="data_fim">Data final</label>
                        <input type="date" id="data_fim" name="data_fim"
                               value="{{ request('data_fim', $fim->format('Y-m-d')) }}">
                    </div>
                    <div>
                        <label for="funcionario_id">Funcionário</label>
                        <select id="funcionario_id" name="funcionario_id">
                            <option value="">Todos</option>
                            @foreach($funcionarios as $f)
                                <option value="{{ $f->id }}"
                                    {{ (string)request('funcionario_id') === (string)$f->id ? 'selected' : '' }}>
                                    {{ $f->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Período selecionado</label>
                        <div class="text-muted" style="margin-top: 9px;">
                            {{ $inicio->format('d/m/Y') }} até {{ $fim->format('d/m/Y') }}
                        </div>
                    </div>
                </div>

                <div class="filters-actions">
                    <button type="submit" class="btn btn-outline">
                        <i class="fas fa-filter btn-icon"></i> Aplicar filtros
                    </button>

                    {{-- MESMO FORM, OUTRA ROTA: ENVIA FILTROS PRO PDF --}}
                    <button type="submit"
                            class="btn btn-primary"
                            formaction="{{ route('relatorios.comissoes.pdf') }}"
                            formtarget="_blank">
                        <i class="fas fa-file-pdf btn-icon"></i> Exportar PDF
                    </button>
                </div>
            </form>

            <div class="total-box">
                Total no período:
                <span>R$ {{ number_format($total ?? 0, 2, ',', '.') }}</span>
                @if($valorCol === null)
                    <span class="text-muted"> (obs: tabela comissoes não possui coluna de valor detectável)</span>
                @endif
            </div>
        </div>

        {{-- TABELA --}}
        <div class="table-card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <h3 style="font-size:16px; font-weight:700;">Lançamentos de comissões</h3>
                <span class="text-muted">
                    {{ count($registros) }} registro(s)
                </span>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Funcionário</th>
                            <th>Serviço</th>
                            <th>Valor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registros as $r)
                            @php
                                $st = strtolower($r->status ?? '');
                                $classe = match($st) {
                                    'pago'      => 'st-pago',
                                    'estornado' => 'st-estornado',
                                    default     => 'st-pendente',
                                };
                                $label = $st ? ucfirst($st) : 'Pendente';
                            @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($r->data)->format('d/m/Y H:i') }}</td>
                                <td>{{ $r->funcionario_nome ?? '—' }}</td>
                                <td>{{ $r->servico_nome ?? '—' }}</td>
                                <td>
                                    @if(property_exists($r, 'valor') && $r->valor !== null)
                                        R$ {{ number_format($r->valor, 2, ',', '.') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge {{ $classe }}">{{ $label }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding:16px; text-align:center;" class="text-muted">
                                    Nenhuma comissão encontrada para os filtros selecionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
