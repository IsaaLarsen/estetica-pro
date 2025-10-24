<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\AgendaBloqueio;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\ComissaoService;

class AgendaController extends Controller
{
    /**
     * Tela do calendário com filtro por funcionário
     */
    public function index(Request $request)
    {
        $min = Setting::get('expediente_inicio', '08:00');
        $max = Setting::get('expediente_fim', '18:00');

        $funcionarios = DB::table('funcionarios')
            ->select('id','nome')
            ->where('ativo', 1)
            ->orderBy('nome')
            ->get();

        $selectedFuncionarioId = $request->query('funcionario_id');
        $nomeUsuario = auth()->user()->name ?? 'Usuário';

        return view('agenda.index', [
            'slotMinTime'           => $min,
            'slotMaxTime'           => $max,
            'funcionarios'          => $funcionarios,
            'selectedFuncionarioId' => $selectedFuncionarioId,
            'nomeUsuario'           => $nomeUsuario,
        ]);
    }

    /**
     * Feed JSON do FullCalendar (com filtro por funcionario_id)
     * Retorna eventos + bloqueios (quando filtrado por funcionário)
     */
    public function events(Request $request)
    {
        $tz = config('app.timezone', 'America/Sao_Paulo');

        // Normaliza janelas recebidas do FC para o timezone da aplicação
        $start = Carbon::parse($request->get('start'))->setTimezone($tz);
        $end   = Carbon::parse($request->get('end'))->setTimezone($tz);

        $funcionarioId = $request->get('funcionario_id'); // pode ser null/vazio (Todos)

        // Agendamentos dentro do range (comparando em horário local da app)
        $query = DB::table('agendas')
            ->join('funcionarios','funcionarios.id','=','agendas.funcionario_id')
            ->join('clientes','clientes.id','=','agendas.cliente_id')
            ->join('servicos','servicos.id','=','agendas.servico_id')
            ->whereBetween('agendas.inicio', [$start->toDateTimeString(), $end->toDateTimeString()]);

        if (!empty($funcionarioId)) {
            $query->where('agendas.funcionario_id', $funcionarioId);
        }

        $eventosRaw = $query->select([
                'agendas.id',
                'agendas.inicio as start',
                'agendas.fim as end',
                'agendas.status',
                'agendas.observacoes',
                'funcionarios.nome as funcionario',
                'clientes.nome as cliente',
                'servicos.nome as servico',
            ])
            ->get();

        $statusColors = [
            'agendado'   => ['#3b82f6', '#1d4ed8'],
            'confirmado' => ['#10b981', '#059669'],
            'concluido'  => ['#7e22ce', '#6b21a8'],
            'cancelado'  => ['#ef4444', '#dc2626'],
        ];

        $eventos = $eventosRaw->map(function ($e) use ($statusColors, $tz) {
            $status = strtolower($e->status ?? 'agendado');
            $colors = $statusColors[$status] ?? ['#6366f1','#4f46e5'];

            // Interpreta o que está no banco como horário local da app e devolve ISO 8601 com offset
            $startIso = Carbon::parse($e->start, $tz)->toIso8601String();
            $endIso   = Carbon::parse($e->end,   $tz)->toIso8601String();

            return [
                'id'    => (string)$e->id,
                'title' => "{$e->cliente} — {$e->servico} ({$e->funcionario})",
                'start' => $startIso,
                'end'   => $endIso,

                'className' => ['st-' . $status],

                'extendedProps' => [
                    'cliente_nome'     => $e->cliente,
                    'servico_nome'     => $e->servico,
                    'funcionario_nome' => $e->funcionario,
                    'observacoes'      => $e->observacoes,
                    'status'           => $status,
                ],

                'backgroundColor' => $colors[0],
                'borderColor'     => $colors[1],
            ];
        });

        // Bloqueios — somente quando um funcionário específico está filtrado
        $bloqueios = collect();
        if (!empty($funcionarioId)) {
            $bloqueios = AgendaBloqueio::where('funcionario_id', $funcionarioId)
                ->where(function($q) use ($start,$end){
                    $q->whereBetween('inicio', [$start->toDateTimeString(), $end->toDateTimeString()])
                      ->orWhereBetween('fim',   [$start->toDateTimeString(), $end->toDateTimeString()])
                      ->orWhere(function($q2) use ($start,$end){
                          $q2->where('inicio','<=',$start->toDateTimeString())
                             ->where('fim','>=',$end->toDateTimeString());
                      });
                })
                ->get()
                ->map(function($b) use ($tz){
                    $iniIso = ($b->inicio instanceof Carbon)
                                ? $b->inicio->copy()->setTimezone($tz)->toIso8601String()
                                : Carbon::parse($b->inicio, $tz)->toIso8601String();

                    $fimIso = ($b->fim instanceof Carbon)
                                ? $b->fim->copy()->setTimezone($tz)->toIso8601String()
                                : Carbon::parse($b->fim, $tz)->toIso8601String();

                    return [
                        'title'   => 'Bloqueio',
                        'start'   => $iniIso,
                        'end'     => $fimIso,
                        'display' => 'background',
                        'overlap' => false,
                        'backgroundColor' => '#fca5a5',
                        'borderColor'     => '#ef4444',
                    ];
                });
        }

        return response()->json($eventos->concat($bloqueios)->values());
    }

    /**
     * Formulário de criação de agendamento
     */
    public function create()
    {
        $funcionarios = DB::table('funcionarios')
            ->where('ativo', 1)
            ->orderBy('nome')
            ->get();

        $clientes = DB::table('clientes')->orderBy('nome')->get();
        $servicos = DB::table('servicos')->orderBy('nome')->get();

        $statusOptions = ['agendado','confirmado','concluido','cancelado'];

        return view('agenda.create', compact('funcionarios','clientes','servicos','statusOptions'));
    }

    /**
     * Salva novo agendamento com validações
     */
    public function store(Request $request)
    {
        $request->validate([
            'funcionario_id' => 'required|exists:funcionarios,id',
            'cliente_id'     => 'required|exists:clientes,id',
            'servico_id'     => 'required|exists:servicos,id',
            'data'           => 'required|date',
            'hora'           => 'required',
            'status'         => 'nullable|in:agendado,confirmado,concluido,cancelado',
            'observacoes'    => 'nullable|string',
        ]);

        $tz = config('app.timezone', 'America/Sao_Paulo');

        // duração do serviço
        $servico = DB::table('servicos')->where('id',$request->servico_id)->first();
        $duracao = max(1, (int)($servico->duracao_minutos ?? 30));

        // Interpreta data/hora informadas como horário local da app
        $inicio = Carbon::parse($request->data.' '.$request->hora, $tz);
        $fim    = (clone $inicio)->addMinutes($duracao);

        // horários do expediente
        $min = Setting::get('expediente_inicio', '08:00');
        $max = Setting::get('expediente_fim',    '18:00');

        $limiteInicio = Carbon::parse($inicio->format('Y-m-d').' '.$min, $tz);
        $limiteFim    = Carbon::parse($inicio->format('Y-m-d').' '.$max, $tz);

        if ($inicio->lt($limiteInicio) || $fim->gt($limiteFim)) {
            return back()->withErrors(['hora' => "Horário fora do expediente configurado ({$min}–{$max})."])->withInput();
        }

        // sem sobreposição para o mesmo funcionário
        $conflito = Agenda::where('funcionario_id',$request->funcionario_id)
            ->where(function($q) use ($inicio,$fim){
                $q->whereBetween('inicio', [$inicio->copy(), $fim->copy()->subMinute()])
                  ->orWhereBetween('fim',    [$inicio->copy()->addMinute(), $fim->copy()])
                  ->orWhere(function($q2) use ($inicio,$fim){
                      $q2->where('inicio','<=',$inicio)->where('fim','>=',$fim);
                  });
            })->exists();
        if ($conflito) {
            return back()->withErrors(['hora' => 'Já existe atendimento neste horário para o funcionário.'])->withInput();
        }

        // respeitar bloqueios
        $bloqueado = AgendaBloqueio::where('funcionario_id',$request->funcionario_id)
            ->where(function($q) use ($inicio,$fim){
                $q->whereBetween('inicio', [$inicio->copy(), $fim->copy()])
                  ->orWhereBetween('fim',    [$inicio->copy(), $fim->copy()])
                  ->orWhere(function($q2) use ($inicio,$fim){
                      $q2->where('inicio','<=',$inicio)->where('fim','>=',$fim);
                  });
            })->exists();
        if ($bloqueado) {
            return back()->withErrors(['hora' => 'Período bloqueado para este funcionário.'])->withInput();
        }

        Agenda::create([
            'funcionario_id' => $request->funcionario_id,
            'cliente_id'     => $request->cliente_id,
            'servico_id'     => $request->servico_id,
            'inicio'         => $inicio,  // gravado no fuso local da app
            'fim'            => $fim,
            'status'         => $request->input('status','agendado'),
            'observacoes'    => $request->observacoes,
        ]);

        return redirect()->route('agenda.index', ['funcionario_id' => $request->funcionario_id])
            ->with('success','Agendamento criado com sucesso!');
    }

    /**
     * Formulário de edição
     */
    public function edit($id)
    {
        $tz = config('app.timezone', 'America/Sao_Paulo');

        $agenda = Agenda::findOrFail($id);

        $funcionarios = DB::table('funcionarios')->where('ativo', 1)->orderBy('nome')->get();
        $clientes     = DB::table('clientes')->orderBy('nome')->get();
        $servicos     = DB::table('servicos')->orderBy('nome')->get();

        // Interpreta como horário local da app para exibir no form
        $data = Carbon::parse($agenda->inicio, $tz)->format('Y-m-d');
        $hora = Carbon::parse($agenda->inicio, $tz)->format('H:i');

        $statusOptions = ['agendado','confirmado','concluido','cancelado'];

        return view('agenda.create', [
            'agenda'        => $agenda,
            'funcionarios'  => $funcionarios,
            'clientes'      => $clientes,
            'servicos'      => $servicos,
            'data'          => $data,
            'hora'          => $hora,
            'statusOptions' => $statusOptions,
        ]);
    }

    /**
     * Atualiza um agendamento existente (com as mesmas regras de negócio)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'funcionario_id' => 'required|exists:funcionarios,id',
            'cliente_id'     => 'required|exists:clientes,id',
            'servico_id'     => 'required|exists:servicos,id',
            'data'           => 'required|date',
            'hora'           => 'required',
            'status'         => 'required|in:agendado,confirmado,concluido,cancelado',
            'observacoes'    => 'nullable|string',
        ]);

        $tz = config('app.timezone', 'America/Sao_Paulo');

        $agenda = Agenda::findOrFail($id);

        // duração do serviço
        $servico = DB::table('servicos')->where('id',$request->servico_id)->first();
        $duracao = max(1, (int)($servico->duracao_minutos ?? 30));

        $inicio = Carbon::parse($request->data.' '.$request->hora, $tz);
        $fim    = (clone $inicio)->addMinutes($duracao);

        // horários do expediente
        $min = Setting::get('expediente_inicio', '08:00');
        $max = Setting::get('expediente_fim',    '18:00');

        $limiteInicio = Carbon::parse($inicio->format('Y-m-d').' '.$min, $tz);
        $limiteFim    = Carbon::parse($inicio->format('Y-m-d').' '.$max, $tz);

        if ($inicio->lt($limiteInicio) || $fim->gt($limiteFim)) {
            return back()->withErrors(['hora' => "Horário fora do expediente configurado ({$min}–{$max})."])->withInput();
        }

        // sem sobreposição para o mesmo funcionário (ignorando o próprio evento)
        $conflito = Agenda::where('funcionario_id',$request->funcionario_id)
            ->where('id','<>',$agenda->id)
            ->where(function($q) use ($inicio,$fim){
                $q->whereBetween('inicio', [$inicio->copy(), $fim->copy()->subMinute()])
                  ->orWhereBetween('fim',    [$inicio->copy()->addMinute(), $fim->copy()])
                  ->orWhere(function($q2) use ($inicio,$fim){
                      $q2->where('inicio','<=',$inicio)->where('fim','>=',$fim);
                  });
            })->exists();
        if ($conflito) {
            return back()->withErrors(['hora' => 'Já existe atendimento neste horário para o funcionário.'])->withInput();
        }

        // respeitar bloqueios
        $bloqueado = AgendaBloqueio::where('funcionario_id',$request->funcionario_id)
            ->where(function($q) use ($inicio,$fim){
                $q->whereBetween('inicio', [$inicio->copy(), $fim->copy()])
                  ->orWhereBetween('fim',    [$inicio->copy(), $fim->copy()])
                  ->orWhere(function($q2) use ($inicio,$fim){
                      $q2->where('inicio','<=',$inicio)->where('fim','>=',$fim);
                  });
            })->exists();
        if ($bloqueado) {
            return back()->withErrors(['hora' => 'Período bloqueado para este funcionário.'])->withInput();
        }

        $antigoStatus = $agenda->status;

        $agenda->update([
            'funcionario_id' => $request->funcionario_id,
            'cliente_id'     => $request->cliente_id,
            'servico_id'     => $request->servico_id,
            'inicio'         => $inicio,
            'fim'            => $fim,
            'status'         => $request->status,
            'observacoes'    => $request->observacoes,
        ]);

        // === HOOKS DE COMISSÃO ===
        // se mudou para concluído (e não era concluído), gera comissão
        if ($request->status === 'concluido' && $antigoStatus !== 'concluido') {
            ComissaoService::gerarParaAgenda($agenda);
        }

        // se estava concluído e mudou para cancelado, estorna
        if ($request->status === 'cancelado' && $antigoStatus === 'concluido') {
            ComissaoService::estornarPorAgendaId($agenda->id);
        }


        return redirect()->route('agenda.index', ['funcionario_id' => $request->funcionario_id])
            ->with('success','Agendamento atualizado com sucesso!');
    }

    /**
     * Atualização rápida de status (ex.: via AJAX no modal)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:agendado,confirmado,concluido,cancelado',
        ]);

        $agenda = Agenda::findOrFail($id);

        // guarda o status antigo ANTES de atualizar
        $antigoStatus = $agenda->status;

        // aplica novo status
        $agenda->update(['status' => $request->status]);

        // === HOOKS DE COMISSÃO ===
        // se mudou para concluído (e não era concluído), gera comissão
        if ($request->status === 'concluido' && $antigoStatus !== 'concluido') {
            ComissaoService::gerarParaAgenda($agenda);
        }

        // se estava concluído e mudou para cancelado, estorna
        if ($request->status === 'cancelado' && $antigoStatus === 'concluido') {
            ComissaoService::estornarPorAgendaId($agenda->id);
        }
        // Gera ou estorna comissão conforme o novo status


        if ($request->wantsJson()) {
            return response()->json(['ok'=>true, 'status'=>$agenda->status]);
        }

        return back()->with('success','Status atualizado com sucesso!');
    }

      public function slots(Request $request)
    {
        $request->validate([
            'funcionario_id' => 'required|integer|exists:funcionarios,id',
            'data'           => 'required|date_format:Y-m-d',
            'duracao'        => 'required|integer|min:10|max:480',
        ]);

        $funcionarioId = (int) $request->funcionario_id;
        $data          = Carbon::createFromFormat('Y-m-d', $request->data);
        $duracaoMin    = (int) $request->duracao;

        // Janela de expediente (usa seu Setting)
        $iniExp = Setting::get('expediente_inicio', '08:00');
        $fimExp = Setting::get('expediente_fim',    '18:00');

        $tz = config('app.timezone', 'America/Sao_Paulo');
        $inicioDia = Carbon::parse($data->toDateString().' '.$iniExp, $tz);
        $fimDia    = Carbon::parse($data->toDateString().' '.$fimExp, $tz);

        // Carrega compromissos e bloqueios do dia (usando seus Models)
        $agendas = Agenda::where('funcionario_id', $funcionarioId)
            ->whereDate('inicio', $data)
            ->get(['inicio','fim']);

        $bloqueios = AgendaBloqueio::where('funcionario_id', $funcionarioId)
            ->whereDate('inicio', $data)
            ->get(['inicio','fim']);

        // Normaliza intervalos ocupados
        $ocupados = [];
        foreach ($agendas as $a) {
            $ocupados[] = [
                'ini' => $a->inicio instanceof Carbon ? $a->inicio->copy() : Carbon::parse($a->inicio, $tz),
                'fim' => $a->fim    instanceof Carbon ? $a->fim->copy()    : Carbon::parse($a->fim, $tz),
            ];
        }
        foreach ($bloqueios as $b) {
            $ocupados[] = [
                'ini' => $b->inicio instanceof Carbon ? $b->inicio->copy() : Carbon::parse($b->inicio, $tz),
                'fim' => $b->fim    instanceof Carbon ? $b->fim->copy()    : Carbon::parse($b->fim, $tz),
            ];
        }

        // Gera slots livres com passo de 15 min (ajuste se quiser 30)
        $slots = [];
        $cursor = $inicioDia->copy();

        while ($cursor->copy()->addMinutes($duracaoMin)->lte($fimDia)) {
            $slotIni = $cursor->copy();
            $slotFim = $cursor->copy()->addMinutes($duracaoMin);

            $conflita = false;
            foreach ($ocupados as $o) {
                if ($slotIni < $o['fim'] && $slotFim > $o['ini']) {
                    $conflita = true;
                    break;
                }
            }

            if (!$conflita) {
                $slots[] = $slotIni->format('H:i');
            }

            $cursor->addMinutes(15);
        }

        return response()->json(['data' => $slots]);
    }
}
