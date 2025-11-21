@extends('layouts.app')

@section('title', 'Detalhes do Log - Estética PRO')

@section('content')
@php
    use Illuminate\Support\Facades\DB;

    // details já é array por causa do casts no Model,
    // mas tratamos também o caso de logs antigos que possam estar como string
    if (is_string($log->details ?? null)) {
        $detalhes = json_decode($log->details, true) ?: [];
    } else {
        $detalhes = $log->details ?? [];
    }

    $alteracoes     = $detalhes['alteracoes']      ?? [];
    $dadosNovos     = $detalhes['dados_novos']     ?? [];
    $dadosAntigos   = $detalhes['dados_antigos']   ?? [];
    $dadosExcluidos = $detalhes['dados_excluidos'] ?? [];

    $rotaName  = $detalhes['rota_name'] ?? null;
    $rotaPath  = $detalhes['rota_path'] ?? null;
    $timestamp = $detalhes['timestamp'] ?? $log->created_at->format('Y-m-d H:i:s');

    // Labels mais amigáveis pra alguns campos comuns
    $labels = [
        'funcionario_id'   => 'Funcionário',
        'cliente_id'       => 'Cliente',
        'servico_id'       => 'Serviço',
        'agenda_id'        => 'Agendamento',
        'inicio'           => 'Início',
        'fim'              => 'Fim',
        'status'           => 'Status',
        'observacoes'      => 'Observações',
        'valor'            => 'Valor',
        'valor_servico'    => 'Valor do Serviço',
        'valor_comissao'   => 'Valor da Comissão',
        'comissao_percent' => 'Percentual de Comissão',
        'percentual'       => 'Percentual',
        'created_at'       => 'Criado em',
        'updated_at'       => 'Atualizado em',
    ];

    // Carrega os "dicionários" de nomes pra substituir IDs (sem usar Models)
    // Ajuste o nome das tabelas/colunas se for diferente no seu banco
    $funcionarios = DB::table('funcionarios')->pluck('nome', 'id')->toArray();
    $clientes     = DB::table('clientes')->pluck('nome', 'id')->toArray();
    $servicos     = DB::table('servicos')->pluck('nome', 'id')->toArray();

    // Formatação básica de qualquer valor
    $formatarValorBase = function ($v) {
        if (is_null($v)) return '—';
        if (is_bool($v)) return $v ? 'Sim' : 'Não';
        if ($v === '') return '(vazio)';
        if (is_array($v)) return json_encode($v, JSON_UNESCAPED_UNICODE);
        return (string) $v;
    };

    // Formatação levando em conta o TIPO DE CAMPO
    $formatarPorCampo = function ($campo, $v) use ($formatarValorBase, $funcionarios, $clientes, $servicos) {
        if (is_null($v)) {
            return '—';
        }

        // Normaliza número vindo como string
        if (is_numeric($v)) {
            $vNum = $v + 0;
        } else {
            $vNum = null;
        }

        switch ($campo) {
            case 'funcionario_id':
                return $funcionarios[$v] ?? ('#' . $v);

            case 'cliente_id':
                return $clientes[$v] ?? ('#' . $v);

            case 'servico_id':
                return $servicos[$v] ?? ('#' . $v);

            case 'agenda_id':
                return 'Agendamento #' . $v;

            case 'valor':
            case 'valor_servico':
            case 'valor_comissao':
                if ($vNum !== null) {
                    return 'R$ ' . number_format($vNum, 2, ',', '.');
                }
                return $formatarValorBase($v);

            case 'comissao_percent':
            case 'comissao_percentual':
            case 'percentual':
                if ($vNum !== null) {
                    return number_format($vNum, 2, ',', '.') . ' %';
                }
                return $formatarValorBase($v);

            default:
                return $formatarValorBase($v);
        }
    };
@endphp

<div style="background:white; padding:24px; border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,0.08);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 style="font-size:24px; font-weight:700;
            background:linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;">
            Detalhes do Log
        </h1>

        <a href="{{ route('logs.index') }}"
           style="padding:8px 16px; border-radius:999px; text-decoration:none; border:1px solid #e5e7eb; font-size:13px; color:#374151;">
            ← Voltar para lista
        </a>
    </div>

    {{-- Informações gerais --}}
    <div style="display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap:12px; margin-bottom:24px; font-size:14px;">
        <div>
            <div><strong>Usuário:</strong> {{ $log->usuario_nome ?? '—' }}</div>
            <div><strong>Perfil:</strong> {{ $log->usuario_role ?? '—' }}</div>
            <div><strong>Ação:</strong> {{ $log->action }}</div>
        </div>
        <div>
            <div><strong>Model:</strong> {{ $log->model }} (#{{ $log->model_id }})</div>
            <div><strong>Data/Hora do log:</strong> {{ $log->created_at->format('d/m/Y H:i:s') }}</div>
            <div><strong>Horário da requisição:</strong> {{ \Carbon\Carbon::parse($timestamp)->format('d/m/Y H:i:s') }}</div>
        </div>
        <div>
            <div><strong>Rota (nome):</strong> {{ $rotaName ?? '—' }}</div>
            <div><strong>Rota (path):</strong> /{{ $rotaPath ?? '' }}</div>
        </div>
    </div>

    {{-- Caso seja DELETE (dados_excluidos) --}}
    @if(!empty($dadosExcluidos))
        <h2 style="font-size:18px; font-weight:600; margin-bottom:8px;">Registro excluído</h2>
        <div style="overflow-x:auto; margin-bottom:24px;">
            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="background:#f9fafb;">
                        <th style="padding:8px; text-align:left;">Campo</th>
                        <th style="padding:8px; text-align:left;">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dadosExcluidos as $campo => $valor)
                        @php
                            $label = $labels[$campo] ?? ucfirst(str_replace('_', ' ', $campo));
                        @endphp
                        <tr style="border-bottom:1px solid #e5e7eb;">
                            <td style="padding:8px; font-weight:500;">{{ $label }}</td>
                            <td style="padding:8px;">{{ $formatarPorCampo($campo, $valor) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Caso seja CREATE sem alteracoes (só dados_novos) --}}
    @if(empty($alteracoes) && !empty($dadosNovos) && empty($dadosExcluidos))
        <h2 style="font-size:18px; font-weight:600; margin-bottom:8px;">Dados cadastrados</h2>
        <div style="overflow-x:auto; margin-bottom:24px;">
            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="background:#f9fafb;">
                        <th style="padding:8px; text-align:left;">Campo</th>
                        <th style="padding:8px; text-align:left;">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dadosNovos as $campo => $valor)
                        @php
                            $label = $labels[$campo] ?? ucfirst(str_replace('_', ' ', $campo));
                        @endphp
                        <tr style="border-bottom:1px solid #e5e7eb;">
                            <td style="padding:8px; font-weight:500;">{{ $label }}</td>
                            <td style="padding:8px;">{{ $formatarPorCampo($campo, $valor) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- UPDATE / UPDATE_STATUS: mostrar Campo | Valor antigo | Valor novo --}}
    @if(!empty($alteracoes))
        <h2 style="font-size:18px; font-weight:600; margin-bottom:8px;">Campos alterados</h2>
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="background:#f9fafb;">
                        <th style="padding:8px; text-align:left; width:25%;">Campo</th>
                        <th style="padding:8px; text-align:left; width:37.5%;">Valor antigo</th>
                        <th style="padding:8px; text-align:left; width:37.5%;">Valor novo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($alteracoes as $campo => $valores)
                        @php
                            $label = $labels[$campo] ?? ucfirst(str_replace('_', ' ', $campo));
                            $old   = $valores['old'] ?? null;
                            $new   = $valores['new'] ?? null;
                        @endphp
                        <tr style="border-bottom:1px solid #e5e7eb;">
                            <td style="padding:8px; font-weight:500;">{{ $label }}</td>
                            <td style="padding:8px;">{{ $formatarPorCampo($campo, $old) }}</td>
                            <td style="padding:8px;">{{ $formatarPorCampo($campo, $new) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Se por algum motivo não tiver nada interpretável --}}
    @if(empty($alteracoes) && empty($dadosNovos) && empty($dadosExcluidos))
        <p style="font-size:13px; color:#6b7280; margin-top:16px;">
            Não há detalhes estruturados para este log. Conteúdo bruto:
        </p>
        <pre style="font-size:12px; background:#f9fafb; padding:12px; border-radius:8px; margin-top:8px; overflow-x:auto;">
{{ json_encode($detalhes, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}
        </pre>
    @endif
</div>
@endsection
