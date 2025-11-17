<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\AgendaBloqueio;
use App\Models\Setting;
use App\Models\AgendaExpedienteExcecao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use App\Services\ComissaoService;

class AgendaController extends Controller
{
    /**
     * Tela do calendário com filtro por funcionário (APENAS ADMIN)
     */
    public function index(Request $request)
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $usuario = Session::get('usuario');
        $papel = strtolower($usuario->role ?? '');

        // Se for funcionário, redireciona para minha agenda
        if ($papel === 'funcionario') {
            return redirect()->route('minha.agenda');
        }

        // Horário padrão de expediente
        $min = Setting::get('expediente_inicio', '08:00');
        $max = Setting::get('expediente_fim', '18:00');

        // Ajusta limites globais com base nas exceções
        $minEx = AgendaExpedienteExcecao::min('inicio');
        $maxEx = AgendaExpedienteExcecao::max('fim');

        if ($minEx) {
            $minExStr = substr($minEx, 0, 5);
            if ($minExStr < $min) {
                $min = $minExStr;
            }
        }

        if ($maxEx) {
            $maxExStr = substr($maxEx, 0, 5);
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
        $nomeUsuario = $usuario->nome ?? 'Usuário';

        return view('agenda.index', [
            'slotMinTime'           => $min,
            'slotMaxTime'           => $max,
            'funcionarios'          => $funcionarios,
            'selectedFuncionarioId' => $selectedFuncionarioId,
            'nomeUsuario'           => $nomeUsuario,
        ]);
    }

    /**
     * Feed JSON do FullCalendar (ADMIN) com filtro por funcionario_id
     * Retorna eventos + bloqueios
     */
    public function events(Request $request)
    {
        if (!Session::has('usuario')) {
            return response()->json([], 401);
        }

        $request->validate([
            'start'         => 'required|date',
            'end'           => 'required|date',
            'funcionario_id'=> 'nullable|integer|exists:funcionarios,id'
        ]);

        $tz = config('app.timezone', 'America/Sao_Paulo');

        // Normaliza janelas recebidas do FC para o timezone da aplicação
        $start = Carbon::parse($request->get('start'))->setTimezone($tz);
        $end   = Carbon::parse($request->get('end'))->setTimezone($tz);

        $funcionarioId = $request->filled('funcionario_id')
            ? $request->get('funcionario_id')
            : null;

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
                'backgroundColor' => '#e5e7eb',
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
     * Tela do calendário para FUNCIONÁRIOS (só veem sua própria agenda)
     */
    public function minhaAgenda()
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $usuario = Session::get('usuario');
        
        // Busca funcionário pelo CPF (que é o que temos na sessão)
        $funcionario = DB::table('funcionarios')->where('cpf', $usuario->cpf)->first();
        
        if (!$funcionario) {
            return redirect()->route('dashboard')->with('error', 'Funcionário não encontrado.');
        }

        $funcionarioId = $funcionario->id;
        $nomeFuncionario = $funcionario->nome;

        // Horário padrão de expediente
        $min = Setting::get('expediente_inicio', '08:00');
        $max = Setting::get('expediente_fim', '18:00');

        // Ajusta limites globais com base nas exceções
        $minEx = AgendaExpedienteExcecao::min('inicio');
        $maxEx = AgendaExpedienteExcecao::max('fim');

        if ($minEx) {
            $minExStr = substr($minEx, 0, 5);
            if ($minExStr < $min) {
                $min = $minExStr;
            }
        }

        if ($maxEx) {
            $maxExStr = substr($maxEx, 0, 5);
            if ($maxExStr > $max) {
                $max = $maxExStr;
            }
        }

        return view('agenda.minha-agenda', [
            'slotMinTime'     => $min,
            'slotMaxTime'     => $max,
            'funcionarioId'   => $funcionarioId,
            'nomeFuncionario' => $nomeFuncionario,
        ]);
    }

    /**
     * Feed JSON do FullCalendar para FUNCIONÁRIOS (só retorna seus próprios agendamentos)
     */
    public function meusEvents(Request $request)
    {
        if (!Session::has('usuario')) {
            return response()->json([], 401);
        }

        $request->validate([
            'start' => 'required|date',
            'end'   => 'required|date',
        ]);

        $tz = config('app.timezone', 'America/Sao_Paulo');

        $usuario = Session::get('usuario');
        $funcionario = DB::table('funcionarios')->where('cpf', $usuario->cpf)->first();
        
        if (!$funcionario) {
            return response()->json([], 403);
        }

        $funcionarioId = $funcionario->id;

        // Normaliza janelas recebidas do FC para o timezone da aplicação
        $start = Carbon::parse($request->get('start'))->setTimezone($tz);
        $end   = Carbon::parse($request->get('end'))->setTimezone($tz);

        // ============================
        // 1) AGENDA NORMAL (APENAS DO FUNCIONÁRIO LOGADO)
        // ============================
        $query = DB::table('agendas')
            ->join('funcionarios','funcionarios.id','=','agendas.funcionario_id')
            ->join('clientes','clientes.id','=','agendas.cliente_id')
            ->join('servicos','servicos.id','=','agendas.servico_id')
            ->where('agendas.funcionario_id', $funcionarioId)
            ->whereBetween('agendas.inicio', [
                $start->toDateTimeString(),
                $end->toDateTimeString()
            ]);

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
                'title' => "{$e->cliente} — {$e->servico}",
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
        // 2) BLOQUEIOS (apenas os que afetam este funcionário)
        // ============================
        $bloqueiosQuery = AgendaBloqueio::query()
            ->where(function($q) use ($funcionarioId) {
                $q->where('aplicar_todos', true)
                  ->orWhereHas('funcionarios', function ($q2) use ($funcionarioId) {
                      $q2->where('funcionario_id', $funcionarioId);
                  });
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
                'backgroundColor' => '#e5e7eb',
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
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $usuario = Session::get('usuario');
        
        // Se for funcionário, só deixa escolher ele mesmo
        if ($usuario->role === 'funcionario') {
            $funcionario = DB::table('funcionarios')->where('cpf', $usuario->cpf)->first();
            $funcionarios = $funcionario ? 
                DB::table('funcionarios')->where('id', $funcionario->id)->where('ativo', 1)->get() 
                : collect();
        } else {
            // Admin pode criar para qualquer funcionário
            $funcionarios = DB::table('funcionarios')
                ->where('ativo', 1)
                ->orderBy('nome')
                ->get();
        }

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
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $usuario = Session::get('usuario');
        
        // Se for funcionário, força o funcionario_id para ser o dele
        if ($usuario->role === 'funcionario') {
            $funcionario = DB::table('funcionarios')->where('cpf', $usuario->cpf)->first();
            if ($funcionario) {
                $request->merge(['funcionario_id' => $funcionario->id]);
            }
        }

        $request->validate([
            'funcionario_id' => 'required|integer|exists:funcionarios,id',
            'cliente_id'     => 'required|integer|exists:clientes,id',
            'servico_id'     => 'required|integer|exists:servicos,id',
            'data'           => 'required|date|after_or_equal:today|before_or_equal:2100-12-31',
            'hora'           => 'required|date_format:H:i',
            'status'         => 'required|in:agendado,confirmado,concluido,cancelado',
            'observacoes'    => 'nullable|string|max:500',
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
            'inicio'         => $inicio,
            'fim'            => $fim,
            'status'         => $request->input('status','agendado'),
            'observacoes'    => $request->observacoes,
        ]);

        // Redireciona para a rota correta baseada no tipo de usuário
        if ($usuario->role === 'funcionario') {
            return redirect()->route('minha.agenda')
                ->with('success','Agendamento criado com sucesso!');
        } else {
            return redirect()->route('agenda.index', ['funcionario_id' => $request->funcionario_id])
                ->with('success','Agendamento criado com sucesso!');
        }
    }

    /**
     * Formulário de edição
     */
    public function edit($id)
    {
        if (!is_numeric($id) || $id <= 0) {
            abort(404, 'Agendamento não encontrado.');
        }

        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $tz = config('app.timezone', 'America/Sao_Paulo');

        $agenda = Agenda::findOrFail($id);
        $usuario = Session::get('usuario');

        // Verifica permissão do funcionário
        if ($usuario->role === 'funcionario') {
            $funcionario = DB::table('funcionarios')->where('cpf', $usuario->cpf)->first();
            if ($funcionario && $agenda->funcionario_id !== $funcionario->id) {
                abort(403, 'Você não tem permissão para editar este agendamento.');
            }
        }

        // Se for funcionário, só pode ver ele mesmo
        if ($usuario->role === 'funcionario') {
            $funcionario = DB::table('funcionarios')->where('cpf', $usuario->cpf)->first();
            $funcionarios = $funcionario ? 
                DB::table('funcionarios')->where('id', $funcionario->id)->where('ativo', 1)->get() 
                : collect();
        } else {
            // Admin pode ver todos
            $funcionarios = DB::table('funcionarios')->where('ativo', 1)->orderBy('nome')->get();
        }

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
     * Atualiza um agendamento existente
     */
    public function update(Request $request, $id)
    {
        if (!is_numeric($id) || $id <= 0) {
            abort(404, 'Agendamento não encontrado.');
        }

        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $usuario = Session::get('usuario');
        
        // Se for funcionário, força o funcionario_id para ser o dele
        if ($usuario->role === 'funcionario') {
            $funcionario = DB::table('funcionarios')->where('cpf', $usuario->cpf)->first();
            if ($funcionario) {
                $request->merge(['funcionario_id' => $funcionario->id]);
            }
        }

        $request->validate([
            'funcionario_id' => 'required|integer|exists:funcionarios,id',
            'cliente_id'     => 'required|integer|exists:clientes,id',
            'servico_id'     => 'required|integer|exists:servicos,id',
            'data'           => 'required|date|before_or_equal:2100-12-31',
            'hora'           => 'required|date_format:H:i',
            'status'         => 'required|in:agendado,confirmado,concluido,cancelado',
            'observacoes'    => 'nullable|string|max:500',
        ]);

        $tz = config('app.timezone', 'America/Sao_Paulo');

        $agenda = Agenda::findOrFail($id);

        // Verifica permissão
        if ($usuario->role === 'funcionario') {
            $funcionario = DB::table('funcionarios')->where('cpf', $usuario->cpf)->first();
            if ($funcionario && $agenda->funcionario_id !== $funcionario->id) {
                abort(403, 'Você não tem permissão para editar este agendamento.');
            }
        }

        // duração do serviço
        $servico = DB::table('servicos')->where('id',$request->servico_id)->first();
        $duracao = max(1, (int)($servico->duracao_minutos ?? 30));

        $inicio = Carbon::parse($request->data.' '.$request->hora, $tz);
        $fim    = (clone $inicio)->addMinutes($duracao);

        // Para edição, permitir datas passadas, mas não reagendar pra passado
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

        // HOOKS DE COMISSÃO
        if ($request->status === 'concluido' && $antigoStatus !== 'concluido') {
            ComissaoService::gerarParaAgenda($agenda);
        }

        if ($request->status === 'cancelado' && $antigoStatus === 'concluido') {
            ComissaoService::estornarPorAgendaId($agenda->id);
        }

        // Redireciona para a rota correta baseada no tipo de usuário
        if ($usuario->role === 'funcionario') {
            return redirect()->route('minha.agenda')
                ->with('success','Agendamento atualizado com sucesso!');
        } else {
            return redirect()->route('agenda.index', ['funcionario_id' => $request->funcionario_id])
                ->with('success','Agendamento atualizado com sucesso!');
        }
    }

    /**
     * Atualização rápida de status
     */
    public function updateStatus(Request $request, $id)
    {
        if (!is_numeric($id) || $id <= 0) {
            return response()->json(['error' => 'Agendamento não encontrado.'], 404);
        }

        if (!Session::has('usuario')) {
            return response()->json(['error' => 'Não autenticado.'], 401);
        }

        $request->validate([
            'status' => 'required|in:agendado,confirmado,concluido,cancelado',
        ]);

        $agenda = Agenda::findOrFail($id);
        $usuario = Session::get('usuario');

        // Verifica permissão
        if ($usuario->role === 'funcionario') {
            $funcionario = DB::table('funcionarios')->where('cpf', $usuario->cpf)->first();
            if ($funcionario && $agenda->funcionario_id !== $funcionario->id) {
                return response()->json(['error' => 'Você não tem permissão para alterar este agendamento.'], 403);
            }
        }

        $antigoStatus = $agenda->status;
        $agenda->update(['status' => $request->status]);

        // HOOKS DE COMISSÃO
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
