@extends('layouts.app')

@section('title', 'Relatório de Serviços - Estética PRO')

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

        .btn-link {
            background: none;
            border: none;
            color: var(--primary);
            text-decoration: underline;
            padding: 0;
            font-size: 13px;
            cursor: pointer;
        }

        .btn-icon {
            margin-right: 6px;
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

        .filters-grid input {
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            transition: all .2s ease;
        }

        .filters-grid input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(236,72,153,.2);
        }

        .filters-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 4px;
            flex-wrap: wrap;
        }

        .text-muted {
            color: var(--text-light);
            font-size: 12px;
        }

        .cards-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 18px;
        }

        .card-kpi {
            background: #fff;
            border-radius: 16px;
            padding: 14px 16px;
            box-shadow: 0 4px 18px rgba(0,0,0,.06);
        }

        .card-kpi-label {
            font-size: 13px;
            color: var(--text-light);
            margin-bottom: 6px;
        }

        .card-kpi-value {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary-dark);
        }

        .card-kpi-sub {
            margin-top: 4px;
            font-size: 11px;
            color: var(--text-light);
        }

        .card-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 6px;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }

        .card-subtitle {
            font-size: 12px;
            color: var(--text-light);
            margin-bottom: 10px;
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

        tbody tr:nth-child(even) {
            background: #fdf2f8;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 500;
        }

        .badge-servico {
            background: #eef2ff;
            color: #3730a3;
        }

        .empty {
            padding: 16px 0;
            text-align: center;
            font-size: 13px;
            color: var(--text-light);
            font-style: italic;
        }

        @media (max-width: 992px) {
            .filters-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .cards-row {
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
            .cards-row {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="content">
        <div class="page-header">
            <h1 class="page-title">Relatório de Serviços</h1>
            <a href="{{ route('relatorios.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left btn-icon"></i> Voltar aos relatórios
            </a>
        </div>

        {{-- FILTROS --}}
        <div class="filters-card">
            <form method="GET" action="{{ route('relatorios.servicos') }}">
                <div class="filters-grid">
                    <div>
                        <label for="data_inicio">Data inicial</label>
                        <input
                            type="date"
                            id="data_inicio"
                            name="data_inicio"
                            value="{{ request('data_inicio', optional($inicio)->format('Y-m-d')) }}"
                        >
                    </div>

                    <div>
                        <label for="data_fim">Data final</label>
                        <input
                            type="date"
                            id="data_fim"
                            name="data_fim"
                            value="{{ request('data_fim', optional($fim)->format('Y-m-d')) }}"
                        >
                    </div>

                    <div>
                        <label>Período selecionado</label>
                        <div class="text-muted" style="margin-top: 9px;">
                            {{ optional($inicio)->format('d/m/Y') }} até {{ optional($fim)->format('d/m/Y') }}
                        </div>
                    </div>
                </div>

                <div class="filters-actions">
                    <button type="submit" class="btn btn-outline">
                        <i class="fas fa-filter btn-icon"></i> Aplicar filtros
                    </button>

                    <button type="submit"
                            class="btn btn-primary"
                            formaction="{{ route('relatorios.servicos.pdf') }}"
                            formtarget="_blank">
                        <i class="fas fa-file-pdf btn-icon"></i> Exportar PDF
                    </button>
                </div>
            </form>
        </div>

        {{-- CARDS DE RESUMO --}}
        @if($valorCol && $totaisGerais)
            <div class="cards-row">
                <div class="card-kpi">
                    <div class="card-kpi-label">Total de atendimentos concluídos</div>
                    <div class="card-kpi-value">
                        {{ number_format($totaisGerais['total_atendimentos'] ?? 0, 0, ',', '.') }}
                    </div>
                    <div class="card-kpi-sub">
                        Período de {{ optional($inicio)->format('d/m/Y') }} a {{ optional($fim)->format('d/m/Y') }}.
                    </div>
                </div>

                <div class="card-kpi">
                    <div class="card-kpi-label">Faturamento total (serviços)</div>
                    <div class="card-kpi-value">
                        R$ {{ number_format($totaisGerais['total_faturamento'] ?? 0, 2, ',', '.') }}
                    </div>
                    <div class="card-kpi-sub">
                        Soma dos valores dos serviços em atendimentos concluídos.
                    </div>
                </div>

                <div class="card-kpi">
                    <div class="card-kpi-label">Ticket médio por atendimento</div>
                    <div class="card-kpi-value">
                        R$ {{ number_format($totaisGerais['ticket_medio'] ?? 0, 2, ',', '.') }}
                    </div>
                    <div class="card-kpi-sub">
                        Faturamento total ÷ nº de atendimentos.
                    </div>
                </div>
            </div>
        @endif

        {{-- RANKING DE SERVIÇOS --}}
        <div class="table-card">
            <div class="card-title">
                <span>Ranking de serviços</span>
                <span class="text-muted">
                    {{ $servicosResumo->count() }} serviço(s)
                </span>
            </div>
            <div class="card-subtitle">
                Serviços ordenados pelo faturamento no período selecionado.
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Serviço</th>
                            <th class="text-center">Atendimentos</th>
                            <th class="text-right">Faturamento (R$)</th>
                            <th class="text-right">Ticket médio (R$)</th>
                            <th class="text-right">Participação (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($servicosResumo as $s)
                            <tr>
                                <td>
                                    <span class="badge badge-servico">{{ $s->servico_nome }}</span>
                                </td>
                                <td class="text-center">
                                    {{ number_format($s->total_atendimentos ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-right">
                                    {{ number_format($s->faturamento ?? 0, 2, ',', '.') }}
                                </td>
                                <td class="text-right">
                                    {{ number_format($s->ticket_medio ?? 0, 2, ',', '.') }}
                                </td>
                                <td class="text-right">
                                    {{ number_format($s->participacao ?? 0, 1, ',', '.') }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty">
                                    Nenhum serviço encontrado para o período informado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- DETALHES DOS ATENDIMENTOS --}}
        <div class="table-card">
            <div class="card-title">
                <span>Detalhamento dos atendimentos</span>
                <span class="text-muted">
                    {{ $detalhes->count() }} registro(s)
                </span>
            </div>
            <div class="card-subtitle">
                Lista de todos os atendimentos concluídos com seus respectivos serviços.
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Cliente</th>
                            <th>Serviço</th>
                            <th>Profissional</th>
                            @if($valorCol)
                                <th class="text-right">Valor (R$)</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detalhes as $d)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($d->inicio)->format('d/m/Y H:i') }}</td>
                                <td>{{ $d->cliente_nome ?? '-' }}</td>
                                <td>{{ $d->servico_nome ?? '-' }}</td>
                                <td>{{ $d->funcionario_nome ?? '-' }}</td>
                                @if($valorCol)
                                    <td class="text-right">
                                        {{ number_format($d->valor ?? 0, 2, ',', '.') }}
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 4 + ($valorCol ? 1 : 0) }}" class="empty">
                                    Nenhum atendimento encontrado para o período informado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('partials.toast')
@endsection
