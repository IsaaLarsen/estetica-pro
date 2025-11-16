<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\AgendaBloqueio;
use App\Models\Setting;
use App\Models\AgendaExpedienteExcecao;
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
        // Horário padrão de expediente
        $min = Setting::get('expediente_inicio', '08:00'); // ex: 08:00
        $max = Setting::get('expediente_fim', '18:00');    // ex: 18:00

        // Ajusta limites globais com base nas exceções (se existir algum dia abrindo mais cedo ou fechando mais tarde)
        $minEx = AgendaExpedienteExcecao::min('inicio'); // ex: "05:00:00"
        $maxEx = AgendaExpedienteExcecao::max('fim');    // ex: "21:00:00"

        if ($minEx) {
            $minExStr = substr($minEx, 0, 5); // 05:00
            if ($minExStr < $min) {
                $min = $minExStr;
            }
        }

        if ($maxEx) {
            $maxExStr = substr($maxEx, 0, 5); // 21:00
            if ($maxExStr > $max) {
                $max = $maxExStr;
            }
        }

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
     * Retorna eventos + bloqueios
     */
    public function events(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end'   => 'required|date',
            'funcionario_id' => 'nullable|integer|exists:funcionarios,id'
        ]);

        $tz = config('app.timezone', 'America/Sao_Paulo');

        // Normaliza janelas recebidas do FC para o timezone da aplicação
        $start = Carbon::parse($request->get('start'))->setTimezone($tz);
        $end   = Carbon::parse($request->get('end'))->setTimezone($tz);

        $funcionarioId = $request->get('funcionario_id'); // pode ser null/vazio (Todos)

        // ============================
        // 1) AGENDA NORMAL
        // ============================
        $query = DB::table('agendas')
            ->join('funcionarios','funcionarios.id','=','agendas.funcionario_id')
            ->join('clientes','clientes.id','=','agendas.cliente_id')
            ->join('servicos','servicos.id','=','agendas.servico_id')
            ->whereBetween('agendas.inicio', [
                $start->toDateTimeString(),
                $end->toDateTimeString()
            ]);

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

            $startIso = Carbon::parse($e->start, $tz)->toIso8601String();
            $endIso   = Carbon::parse($e->end,   $tz)->toIso8601String();

            return [
                'id'    => (string)$e->id,
                'title' => "{$e->cliente} — {$e->servico} ({$e->funcionario})",
                'start' => $startIso,
                'end'   => $endIso,

                'className' => ['st-' . $status],

                'extendedProps' => [
                    'tipo'            => 'agendamento',
                    'cliente_nome'    => $e->cliente,
                    'servico_nome'    => $e->servico,
                    'funcionario_nome'=> $e->funcionario,
                    'observacoes'     => $e->observacoes,
                    'status'          => $status,
                ],

                'backgroundColor' => $colors[0],
                'borderColor'     => $colors[1],
            ];
        });

        // ============================
        // 2) BLOQUEIOS
        // ============================

        $bloqueiosQuery = AgendaBloqueio::query()
            ->when($funcionarioId, function ($q) use ($funcionarioId) {
                // Quando um funcionário específico está filtrado:
                // - bloqueios gerais (aplicar_todos = 1)
                // - bloqueios que contenham esse funcionário na pivot
                $q->where(function ($q2) use ($funcionarioId) {
                    $q2->where('aplicar_todos', true)
                       ->orWhereHas('funcionarios', function ($q3) use ($funcionarioId) {
                           $q3->where('funcionario_id', $funcionarioId);
                       });
                });
            }, function ($q) {
                // Quando NÃO há funcionário filtrado, mostram-se apenas bloqueios gerais
                $q->where('aplicar_todos', true);
            })
            ->where(function($q) use ($start,$end){
                $inicioStr = $start->toDateTimeString();
                $fimStr    = $end->toDateTimeString();

                $q->whereBetween('inicio', [$inicioStr, $fimStr])
                  ->orWhereBetween('fim',   [$inicioStr, $fimStr])
                  ->orWhere(function($q2) use ($inicioStr,$fimStr){
                      $q2->where('inicio','<=',$inicioStr)
                         ->where('fim','>=',$fimStr);
                  });
            });

        $bloqueios = $bloqueiosQuery->get()->map(function($b) use ($tz){
            $iniIso = ($b->inicio instanceof Carbon)
                        ? $b->inicio->copy()->setTimezone($tz)->toIso8601String()
                        : Carbon::parse($b->inicio, $tz)->toIso8601String();

            $fimIso = ($b->fim instanceof Carbon)
                        ? $b->fim->copy()->setTimezone($tz)->toIso8601String()
                        : Carbon::parse($b->fim, $tz)->toIso8601String();

            return [
                'id'    => 'bloqueio_'.$b->id,
                'title' => $b->motivo ?: 'Bloqueio de agenda',
                'start' => $iniIso,
                'end'   => $fimIso,

                'display' => 'background',
                'overlap' => false,
                'backgroundColor' => '#e5e7eb', // cinza clarinho
                'borderColor'     => '#9ca3af',

                'extendedProps' => [
                    'tipo'   => 'bloqueio',
                    'motivo' => $b->motivo,
                ],
            ];
        });

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
            'funcionario_id' => 'required|integer|exists:funcionarios,id',
            'cliente_id'     => 'required|integer|exists:clientes,id',
            'servico_id'     => 'required|integer|exists:servicos,id',
            'data'           => 'required|date|after_or_equal:today',
            'hora'           => 'required|date_format:H:i',
            'status'         => 'required|in:agendado,confirmado,concluido,cancelado',
            'observacoes'    => 'nullable|string|max:500',
        ], [
            'funcionario_id.required' => 'Selecione um funcionário.',
            'funcionario_id.exists'   => 'Funcionário selecionado não existe.',
            'cliente_id.required'     => 'Selecione um cliente.',
            'cliente_id.exists'       => 'Cliente selecionado não existe.',
            'servico_id.required'     => 'Selecione um serviço.',
            'servico_id.exists'       => 'Serviço selecionado não existe.',
            'data.required'           => 'Informe a data do agendamento.',
            'data.after_or_equal'     => 'A data não pode ser anterior a hoje.',
            'hora.required'           => 'Informe o horário do agendamento.',
            'hora.date_format'        => 'Formato de hora inválido (use HH:MM).',
            'status.required'         => 'Selecione o status do agendamento.',
            'status.in'               => 'Status selecionado é inválido.',
            'observacoes.max'         => 'As observações não podem ter mais de 500 caracteres.',
        ]);

        $tz = config('app.timezone', 'America/Sao_Paulo');

        // duração do serviço
        $servico = DB::table('servicos')->where('id',$request->servico_id)->first();
        $duracao = max(1, (int)($servico->duracao_minutos ?? 30));

        // Interpreta data/hora informadas como horário local da app
        $inicio = Carbon::parse($request->data.' '.$request->hora, $tz);
        $fim    = (clone $inicio)->addMinutes($duracao);

        // Verifica se a data/hora não é no passado
        if ($inicio->lt(now())) {
            return back()
                ->withErrors(['hora' => 'Não é possível agendar para horários no passado.'])
                ->withInput();
        }

        // horários do expediente (considerando exceções por dia)
        [$limiteInicio, $limiteFim, $min, $max] = $this->getLimitesExpedienteParaData($inicio);

        if ($inicio->lt($limiteInicio) || $fim->gt($limiteFim)) {
            return back()
                ->withErrors(['hora' => "Horário fora do expediente configurado para este dia ({$min}–{$max})."])
                ->withInput();
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

        // respeitar bloqueios (gerais + específicos)
        if ($this->existeBloqueioNoPeriodo($request->funcionario_id, $inicio, $fim)) {
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
        if (!is_numeric($id) || $id <= 0) {
            abort(404, 'Agendamento não encontrado.');
        }

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
        if (!is_numeric($id) || $id <= 0) {
            abort(404, 'Agendamento não encontrado.');
        }

        $request->validate([
            'funcionario_id' => 'required|integer|exists:funcionarios,id',
            'cliente_id'     => 'required|integer|exists:clientes,id',
            'servico_id'     => 'required|integer|exists:servicos,id',
            'data'           => 'required|date',
            'hora'           => 'required|date_format:H:i',
            'status'         => 'required|in:agendado,confirmado,concluido,cancelado',
            'observacoes'    => 'nullable|string|max:500',
        ], [
            'funcionario_id.required' => 'Selecione um funcionário.',
            'funcionario_id.exists'   => 'Funcionário selecionado não existe.',
            'cliente_id.required'     => 'Selecione um cliente.',
            'cliente_id.exists'       => 'Cliente selecionado não existe.',
            'servico_id.required'     => 'Selecione um serviço.',
            'servico_id.exists'       => 'Serviço selecionado não existe.',
            'data.required'           => 'Informe a data do agendamento.',
            'hora.required'           => 'Informe o horário do agendamento.',
            'hora.date_format'        => 'Formato de hora inválido (use HH:MM).',
            'status.required'         => 'Selecione o status do agendamento.',
            'status.in'               => 'Status selecionado é inválido.',
            'observacoes.max'         => 'As observações não podem ter mais de 500 caracteres.',
        ]);

        $tz = config('app.timezone', 'America/Sao_Paulo');

        $agenda = Agenda::findOrFail($id);

        // duração do serviço
        $servico = DB::table('servicos')->where('id',$request->servico_id)->first();
        $duracao = max(1, (int)($servico->duracao_minutos ?? 30));

        $inicio = Carbon::parse($request->data.' '.$request->hora, $tz);
        $fim    = (clone $inicio)->addMinutes($duracao);

        // Para edição, permitir datas passadas (já que pode ser um agendamento antigo)
        // Mas verificar se não está tentando reagendar para o passado
        if ($inicio->lt(now()) && $inicio->format('Y-m-d H:i') !== $agenda->inicio->format('Y-m-d H:i')) {
            return back()
                ->withErrors(['hora' => 'Não é possível reagendar para horários no passado.'])
                ->withInput();
        }

        // horários do expediente (considerando exceções por dia)
        [$limiteInicio, $limiteFim, $min, $max] = $this->getLimitesExpedienteParaData($inicio);

        if ($inicio->lt($limiteInicio) || $fim->gt($limiteFim)) {
            return back()
                ->withErrors(['hora' => "Horário fora do expediente configurado para este dia ({$min}–{$max})."])
                ->withInput();
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

        // respeitar bloqueios (gerais + específicos)
        if ($this->existeBloqueioNoPeriodo($request->funcionario_id, $inicio, $fim)) {
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
        if ($request->status === 'concluido' && $antigoStatus !== 'concluido') {
            ComissaoService::gerarParaAgenda($agenda);
        }

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
        if (!is_numeric($id) || $id <= 0) {
            return response()->json(['error' => 'Agendamento não encontrado.'], 404);
        }

        $request->validate([
            'status' => 'required|in:agendado,confirmado,concluido,cancelado',
        ]);

        $agenda = Agenda::findOrFail($id);

        $antigoStatus = $agenda->status;

        $agenda->update(['status' => $request->status]);

        // === HOOKS DE COMISSÃO ===
        if ($request->status === 'concluido' && $antigoStatus !== 'concluido') {
            ComissaoService::gerarParaAgenda($agenda);
        }

        if ($request->status === 'cancelado' && $antigoStatus === 'concluido') {
            ComissaoService::estornarPorAgendaId($agenda->id);
        }

        if ($request->wantsJson()) {
            return response()->json(['ok'=>true, 'status'=>$agenda->status]);
        }

        return back()->with('success','Status atualizado com sucesso!');
    }

    /**
     * Verifica se existe bloqueio (geral ou específico) no período informado
     */
    private function existeBloqueioNoPeriodo(int $funcionarioId, Carbon $inicio, Carbon $fim): bool
    {
        return AgendaBloqueio::where(function ($q) use ($funcionarioId) {
                $q->where('aplicar_todos', true)
                  ->orWhereHas('funcionarios', function ($q2) use ($funcionarioId) {
                      $q2->where('funcionario_id', $funcionarioId);
                  });
            })
            ->where(function ($q) use ($inicio, $fim) {
                $q->whereBetween('inicio', [$inicio, $fim])
                  ->orWhereBetween('fim',    [$inicio, $fim])
                  ->orWhere(function ($q2) use ($inicio, $fim) {
                      $q2->where('inicio', '<=', $inicio)
                         ->where('fim', '>=', $fim);
                  });
            })
            ->exists();
    }

    /**
     * Retorna limites de expediente (início/fim) para a data informada.
     * Se houver exceção cadastrada para o dia, usa ela; senão, usa o padrão do Setting.
     *
     * @return array [Carbon $limiteInicio, Carbon $limiteFim, string $horaMin, string $horaMax]
     */
    private function getLimitesExpedienteParaData(Carbon $data): array
    {
        $tz = config('app.timezone', 'America/Sao_Paulo');

        // Procura exceção de expediente para este dia
        $excecao = AgendaExpedienteExcecao::whereDate('data', $data->toDateString())->first();

        if ($excecao) {
            $horaMin = substr($excecao->inicio, 0, 5); // "05:00"
            $horaMax = substr($excecao->fim, 0, 5);    // "21:00"
        } else {
            // Sem exceção -> usa o padrão global
            $horaMin = Setting::get('expediente_inicio', '08:00');
            $horaMax = Setting::get('expediente_fim',    '18:00');
        }

        $limiteInicio = Carbon::parse($data->format('Y-m-d').' '.$horaMin, $tz);
        $limiteFim    = Carbon::parse($data->format('Y-m-d').' '.$horaMax, $tz);

        return [$limiteInicio, $limiteFim, $horaMin, $horaMax];
    }
}