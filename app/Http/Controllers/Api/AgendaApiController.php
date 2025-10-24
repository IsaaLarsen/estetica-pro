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
        $r->validate([
            'funcionario_id' => 'required|integer|exists:funcionarios,id',
            'servico_id'     => 'required|integer|exists:servicos,id',
            'data'           => 'required|date_format:Y-m-d',
        ]);

        $tz = config('app.timezone','America/Sao_Paulo');

        $servico = Servico::findOrFail($r->servico_id);
        $dur     = max(1, (int)($servico->duracao_minutos ?? 30));

        $min = Setting::get('expediente_inicio', '08:00');
        $max = Setting::get('expediente_fim',    '18:00');

        $dia     = Carbon::parse($r->data, $tz);
        $inicioD = Carbon::parse($dia->format('Y-m-d').' '.$min, $tz);
        $fimD    = Carbon::parse($dia->format('Y-m-d').' '.$max, $tz);

        $bloqueios = AgendaBloqueio::where('funcionario_id', $r->funcionario_id)
            ->where(function($q) use ($inicioD,$fimD){
                $q->whereBetween('inicio', [$inicioD, $fimD])
                  ->orWhereBetween('fim',   [$inicioD, $fimD])
                  ->orWhere(function($q2) use ($inicioD,$fimD){
                      $q2->where('inicio','<=',$inicioD)->where('fim','>=',$fimD);
                  });
            })->get(['inicio','fim']);

        $agendados = Agenda::where('funcionario_id', $r->funcionario_id)
            ->whereDate('inicio', $dia->format('Y-m-d'))
            ->whereIn('status', ['agendado','confirmado'])
            ->get(['inicio','fim']);

        $slots = SlotService::gerar(
            inicioDia: $inicioD,
            fimDia:    $fimD,
            duracao:   $dur,
            ocupados:  $agendados,
            bloqueios: $bloqueios
        );

        return response()->json(['data'=>$r->data, 'slots'=>$slots]);
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
    public function meusAgendamentos(Request $r)
    {
        $r->validate([
            'cliente_id' => 'required|integer|exists:clientes,id', // TEMP p/ Flutter
            'status'     => 'nullable|in:agendado,confirmado,concluido,cancelado',
        ]);

        $q = DB::table('agendas')
            ->join('funcionarios','funcionarios.id','=','agendas.funcionario_id')
            ->join('servicos','servicos.id','=','agendas.servico_id')
            ->where('agendas.cliente_id', (int)$r->cliente_id)
            ->orderBy('agendas.inicio','desc')
            ->select([
                'agendas.id','agendas.inicio','agendas.fim','agendas.status','agendas.observacoes',
                'funcionarios.nome as funcionario',
                'servicos.nome as servico',
                'servicos.valor as valor'
            ]);

        if ($r->filled('status')) {
            $q->where('agendas.status', $r->get('status'));
        }

        return response()->json($q->get());
    }

    /**
     * DELETE /api/agendamentos/{id}?cliente_id=
     * Cancela um agendamento do cliente (TEMP: cliente_id na query).
     */
    public function cancelar(Request $r, $id)
    {
        $r->validate([
            'cliente_id' => 'required|integer|exists:clientes,id', // TEMP p/ Flutter
        ]);

        $ag = Agenda::where('id', (int)$id)
            ->where('cliente_id', (int)$r->cliente_id)
            ->firstOrFail();

        $tz = config('app.timezone','America/Sao_Paulo');
        if (Carbon::parse($ag->inicio,$tz)->isPast()) {
            return response()->json(['message'=>'Não é possível cancelar após o horário.'], 422);
        }

        $ag->update(['status'=>'cancelado']);
        return response()->json(['ok'=>true]);
    }
}
