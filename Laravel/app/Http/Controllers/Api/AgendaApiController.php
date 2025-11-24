<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Agenda;
use App\Models\AgendaBloqueio;
use App\Models\Servico;
use App\Models\Funcionario;
use App\Models\Cliente;
use App\Models\Setting;

use App\Services\SlotService;

class AgendaApiController extends Controller
{
    /**
     * GET /api/servicos
     * Lista serviços ativos com valor e duração.
     */
    public function servicos()
    {
        $rows = DB::table('servicos')
            ->where('ativo', 1)
            ->orderBy('nome')
            ->get(['id','nome','duracao_minutos','valor','descricao','ativo']);

        return response()->json($rows);
    }

    /**
     * GET /api/funcionarios?servico_id=
     * Se informado servico_id, filtra quem executa (pivot funcionario_servico).
     */
    public function funcionarios(Request $r)
    {
        $q = DB::table('funcionarios')->where('ativo', 1);

        if ($r->filled('servico_id')) {
            $q->join('funcionario_servico', 'funcionario_servico.funcionario_id', '=', 'funcionarios.id')
              ->where('funcionario_servico.servico_id', (int)$r->servico_id);
        }

        $funcs = $q->orderBy('nome')->get(['funcionarios.id','funcionarios.nome']);
        return response()->json($funcs);
    }

    /**
     * GET /api/agenda/slots?funcionario_id=&servico_id=&data=YYYY-MM-DD
     * Retorna slots livres (HH:MM) considerando expediente, agendados e bloqueios.
     */
    public function slots(Request $r)
{
    // Validação flexível: exige funcionario_id + data
    // e pelo menos UM entre servico_id OU (duracao/duracao_minutos)
    $r->validate([
        'funcionario_id'  => 'required|integer|exists:funcionarios,id',
        'data'            => 'required|date_format:Y-m-d',
        'servico_id'      => 'nullable|integer|exists:servicos,id',
        'duracao'         => 'nullable|integer|min:10|max:480',
        'duracao_minutos' => 'nullable|integer|min:10|max:480',
    ]);

    if (!$r->filled('servico_id') && !$r->filled('duracao') && !$r->filled('duracao_minutos')) {
        return response()->json([
            'errors' => ['duracao' => ['Informe servico_id ou a duração (duracao/duracao_minutos).']]
        ], 422);
    }

    $tz = config('app.timezone','America/Sao_Paulo');

    // Resolve duração: prioriza servico_id; senão usa duracao/duracao_minutos; default 30
    if ($r->filled('servico_id')) {
        $servico = Servico::findOrFail($r->servico_id);
        $dur = max(1, (int)($servico->duracao_minutos ?? 30));
    } else {
        $dur = (int)($r->input('duracao') ?? $r->input('duracao_minutos') ?? 30);
        $dur = max(10, min(480, $dur));
    }

    // Janela de expediente
    $min = Setting::get('expediente_inicio', '08:00');
    $max = Setting::get('expediente_fim',    '18:00');

    $dia     = Carbon::parse($r->data, $tz);
    $inicioD = Carbon::parse($dia->format('Y-m-d').' '.$min, $tz);
    $fimD    = Carbon::parse($dia->format('Y-m-d').' '.$max, $tz);

    // Bloqueios do dia
    $bloqueios = AgendaBloqueio::where('funcionario_id', $r->funcionario_id)
        ->where(function($q) use ($inicioD,$fimD){
            $q->whereBetween('inicio', [$inicioD, $fimD])
              ->orWhereBetween('fim',   [$inicioD, $fimD])
              ->orWhere(function($q2) use ($inicioD,$fimD){
                  $q2->where('inicio','<=',$inicioD)->where('fim','>=',$fimD);
              });
        })->get(['inicio','fim']);

    // Agendados do dia
    $agendados = Agenda::where('funcionario_id', $r->funcionario_id)
        ->whereDate('inicio', $dia->format('Y-m-d'))
        ->whereIn('status', ['agendado','confirmado'])
        ->get(['inicio','fim']);

    // Geração dos slots (reaproveitando seu service)
    $slots = SlotService::gerar(
        inicioDia: $inicioD,
        fimDia:    $fimD,
        duracao:   $dur,
        ocupados:  $agendados,
        bloqueios: $bloqueios
    );

    // ⚠️ Padrão esperado pelo app: { "data": [ "09:00", ... ] }
    return response()->json(['data' => $slots]);
}
    /**
     * POST /api/agendamentos
     * Cria agendamento (TEMP: cliente_id vem no body para facilitar teste no Flutter).
     * TODO: depois trocar por auth Sanctum e usar $request->user()->id / guard de clientes.
     */
    public function agendar(Request $r)
    {
        $r->validate([
            'cliente_id'     => 'required|integer|exists:clientes,id', // TEMP p/ testes no Flutter
            'funcionario_id' => 'required|integer|exists:funcionarios,id',
            'servico_id'     => 'required|integer|exists:servicos,id',
            'data'           => 'required|date_format:Y-m-d',
            'hora_inicio'    => 'required|date_format:H:i',
            'observacoes'    => 'nullable|string|max:1000',
        ]);

        $tz = config('app.timezone','America/Sao_Paulo');

        $servico = Servico::findOrFail($r->servico_id);
        $dur     = max(1, (int)($servico->duracao_minutos ?? 30));

        $inicio = Carbon::parse($r->data.' '.$r->hora_inicio, $tz);
        $fim    = (clone $inicio)->addMinutes($dur);

        // expediente
        $min = Setting::get('expediente_inicio','08:00');
        $max = Setting::get('expediente_fim',   '18:00');
        $limiteInicio = Carbon::parse($inicio->format('Y-m-d').' '.$min, $tz);
        $limiteFim    = Carbon::parse($inicio->format('Y-m-d').' '.$max, $tz);

        if ($inicio->lt($limiteInicio) || $fim->gt($limiteFim)) {
            return response()->json(['message'=>"Horário fora do expediente ({$min}–{$max})."], 422);
        }

        // bloqueios
        $bloqueado = \App\Models\AgendaBloqueio::where('funcionario_id',$r->funcionario_id)
            ->where(function($q) use ($inicio,$fim){
                $q->whereBetween('inicio', [$inicio, $fim])
                  ->orWhereBetween('fim',   [$inicio, $fim])
                  ->orWhere(function($q2) use ($inicio,$fim){
                      $q2->where('inicio','<=',$inicio)->where('fim','>=',$fim);
                  });
            })->exists();
        if ($bloqueado) {
            return response()->json(['message'=>'Período bloqueado para este funcionário.'], 409);
        }

        // conflito
        $conflito = Agenda::where('funcionario_id',$r->funcionario_id)
            ->whereIn('status',['agendado','confirmado'])
            ->where(function($q) use ($inicio,$fim){
                $q->whereBetween('inicio', [$inicio, $fim->copy()->subMinute()])
                  ->orWhereBetween('fim',   [$inicio->copy()->addMinute(), $fim])
                  ->orWhere(function($q2) use ($inicio,$fim){
                      $q2->where('inicio','<=',$inicio)->where('fim','>=',$fim);
                  });
            })->exists();
        if ($conflito) {
            return response()->json(['message'=>'Horário indisponível.'], 409);
        }

        // cria
        $ag = Agenda::create([
            'funcionario_id' => (int)$r->funcionario_id,
            'cliente_id'     => (int)$r->cliente_id,   // TEMP p/ Flutter
            'servico_id'     => (int)$r->servico_id,
            'inicio'         => $inicio,
            'fim'            => $fim,
            'status'         => 'agendado',
            'observacoes'    => $r->observacoes,
        ]);

        return response()->json($ag, 201);
    }

    /**
     * GET /api/agendamentos?cliente_id=&status=
     * Lista agendamentos do cliente (TEMP: cliente_id via query/body).
     */
    public function meusAgendamentos(Request $request)
    {
        // cliente autenticado via Sanctum
        $cliente = $request->user();

        if (!$cliente) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $status = $request->query('status');

        $query = Agenda::with(['servico', 'funcionario'])
            ->where('cliente_id', $cliente->id)
            ->orderByDesc('inicio');

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $agendamentos = $query->get()->map(function (Agenda $a) {
            return [
                'id'               => $a->id,
                'status'           => $a->status,
                'inicio'           => $a->inicio,
                'inicio_formatado' => $a->inicio
                    ? Carbon::parse($a->inicio)->format('d/m/Y H:i')
                    : null,

                'servico_id'       => $a->servico_id,
                'servico_nome'     => optional($a->servico)->nome,
                'servico_valor'    => optional($a->servico)->valor,

                'funcionario_id'   => $a->funcionario_id,
                'funcionario_nome' => optional($a->funcionario)->nome,
            ];
        });

        return response()->json($agendamentos);
    }


    /**
     * DELETE /api/agendamentos/{id}?cliente_id=
     * Cancela um agendamento do cliente (TEMP: cliente_id na query).
     */
    public function cancelar(Request $request, $id)
    {
        // cliente autenticado via Sanctum
        $cliente = $request->user();

        if (!$cliente) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // busca o agendamento que pertence a ESTE cliente
        $agenda = Agenda::where('id', $id)
            ->where('cliente_id', $cliente->id)
            ->first();

        if (!$agenda) {
            return response()->json(['message' => 'Agendamento não encontrado.'], 404);
        }

        // só permite cancelar se estiver agendado ou confirmado
        if (!in_array($agenda->status, ['agendado', 'confirmado'])) {
            return response()->json([
                'message' => 'Este agendamento não pode mais ser cancelado.'
            ], 422);
        }

        $agenda->status = 'cancelado';
        $agenda->save();

        return response()->json([
            'ok'      => true,
            'message' => 'Agendamento cancelado com sucesso.',
        ]);
    }

}
