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
use App\Services\LogAuditoriaService;

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
            ->select('id', 'nome')
            ->where('ativo', 1)
            ->orderBy('nome')
            ->get();

        $selectedFuncionarioId = $request->query('funcionario_id');
        $nomeUsuario = $usuario->nome ?? 'Usuário';

        return view('agenda.index', [
            'slotMinTime' => $min,
            'slotMaxTime' => $max,
            'funcionarios' => $funcionarios,
            'selectedFuncionarioId' => $selectedFuncionarioId,
            'nomeUsuario' => $nomeUsuario,
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
            'start' => 'required|date',
            'end' => 'required|date',
            'funcionario_id' => 'nullable|integer|exists:funcionarios,id'
        ]);

        $tz = config('app.timezone', 'America/Sao_Paulo');

        // Normaliza janelas recebidas do FC para o timezone da aplicação
        $start = Carbon::parse($request->get('start'))->setTimezone($tz);
        $end = Carbon::parse($request->get('end'))->setTimezone($tz);

        $funcionarioId = $request->filled('funcionario_id')
            ? $request->get('funcionario_id')
            : null;

        // ============================
        // 1) AGENDA NORMAL
        // ============================
        $query = DB::table('agendas')
            ->join('funcionarios', 'funcionarios.id', '=', 'agendas.funcionario_id')
            ->join('clientes', 'clientes.id', '=', 'agendas.cliente_id')
            ->join('servicos', 'servicos.id', '=', 'agendas.servico_id')
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
        ])->get();

        $statusColors = [
            'agendado' => ['#3b82f6', '#1d4ed8'],
            'confirmado' => ['#10b981', '#059669'],
            'concluido' => ['#7e22ce', '#6b21a8'],
            'cancelado' => ['#ef4444', '#dc2626'],
        ];

        $eventos = $eventosRaw->map(function ($e) use ($statusColors, $tz) {
            $status = strtolower($e->status ?? 'agendado');
            $colors = $statusColors[$status] ?? ['#6366f1', '#4f46e5'];

            $startIso = Carbon::parse($e->start, $tz)->toIso8601String();
            $endIso = Carbon::parse($e->end, $tz)->toIso8601String();

            return [
                'id' => (string) $e->id,
                'title' => "{$e->cliente} — {$e->servico} ({$e->funcionario})",
                'start' => $startIso,
                'end' => $endIso,
                'className' => ['st-' . $status],
                'extendedProps' => [
                    'tipo' => 'agendamento',
                    'cliente_nome' => $e->cliente,
                    'servico_nome' => $e->servico,
                    'funcionario_nome' => $e->funcionario,
                    'observacoes' => $e->observacoes,
                    'status' => $status,
                ],
                'backgroundColor' => $colors[0],
                'borderColor' => $colors[1],
            ];
        });

        // ============================
        // 2) BLOQUEIOS
        // ============================
        $bloqueiosQuery = AgendaBloqueio::query()
            ->when($funcionarioId, function ($q) use ($funcionarioId) {
                $q->where(function ($q2) use ($funcionarioId) {
                    $q2->where('aplicar_todos', true)
                        ->orWhereHas('funcionarios', function ($q3) use ($funcionarioId) {
                            $q3->where('funcionario_id', $funcionarioId);
                        });
                });
            }, function ($q) {
                $q->where('aplicar_todos', true);
            })
            ->where(function ($q) use ($start, $end) {
                $inicioStr = $start->toDateTimeString();
                $fimStr = $end->toDateTimeString();

                $q->whereBetween('inicio', [$inicioStr, $fimStr])
                    ->orWhereBetween('fim', [$inicioStr, $fimStr])
                    ->orWhere(function ($q2) use ($inicioStr, $fimStr) {
                        $q2->where('inicio', '<=', $inicioStr)
                            ->where('fim', '>=', $fimStr);
                    });
            });

        $bloqueios = $bloqueiosQuery->get()->map(function ($b) use ($tz) {
            $iniIso = ($b->inicio instanceof Carbon)
                ? $b->inicio->copy()->setTimezone($tz)->toIso8601String()
                : Carbon::parse($b->inicio, $tz)->toIso8601String();

            $fimIso = ($b->fim instanceof Carbon)
                ? $b->fim->copy()->setTimezone($tz)->toIso8601String()
                : Carbon::parse($b->fim, $tz)->toIso8601String();

            return [
                'id' => 'bloqueio_' . $b->id,
                'title' => $b->motivo ?: 'Bloqueio de agenda',
                'start' => $iniIso,
                'end' => $fimIso,
                'display' => 'background',
                'overlap' => false,
                'backgroundColor' => '#e5e7eb',
                'borderColor' => '#9ca3af',
                'extendedProps' => [
                    'tipo' => 'bloqueio',
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
            'slotMinTime' => $min,
            'slotMaxTime' => $max,
            'funcionarioId' => $funcionarioId,
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
            'end' => 'required|date',
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
        $end = Carbon::parse($request->get('end'))->setTimezone($tz);

        // ============================
        // 1) AGENDA NORMAL (APENAS DO FUNCIONÁRIO LOGADO)
        // ============================
        $query = DB::table('agendas')
            ->join('funcionarios', 'funcionarios.id', '=', 'agendas.funcionario_id')
            ->join('clientes', 'clientes.id', '=', 'agendas.cliente_id')
            ->join('servicos', 'servicos.id', '=', 'agendas.servico_id')
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
        ])->get();

        $statusColors = [
            'agendado' => ['#3b82f6', '#1d4ed8'],
            'confirmado' => ['#10b981', '#059669'],
            'concluido' => ['#7e22ce', '#6b21a8'],
            'cancelado' => ['#ef4444', '#dc2626'],
        ];

        $eventos = $eventosRaw->map(function ($e) use ($statusColors, $tz) {
            $status = strtolower($e->status ?? 'agendado');
            $colors = $statusColors[$status] ?? ['#6366f1', '#4f46e5'];

            $startIso = Carbon::parse($e->start, $tz)->toIso8601String();
            $endIso = Carbon::parse($e->end, $tz)->toIso8601String();

            return [
                'id' => (string) $e->id,
                'title' => "{$e->cliente} — {$e->servico}",
                'start' => $startIso,
                'end' => $endIso,
                'className' => ['st-' . $status],
                'extendedProps' => [
                    'tipo' => 'agendamento',
                    'cliente_nome' => $e->cliente,
                    'servico_nome' => $e->servico,
                    'funcionario_nome' => $e->funcionario,
                    'observacoes' => $e->observacoes,
                    'status' => $status,
                ],
                'backgroundColor' => $colors[0],
                'borderColor' => $colors[1],
            ];
        });

        // ============================
        // 2) BLOQUEIOS (apenas os que afetam este funcionário)
        // ============================
        $bloqueiosQuery = AgendaBloqueio::query()
            ->where(function ($q) use ($funcionarioId) {
                $q->where('aplicar_todos', true)
                    ->orWhereHas('funcionarios', function ($q2) use ($funcionarioId) {
                        $q2->where('funcionario_id', $funcionarioId);
                    });
            })
            ->where(function ($q) use ($start, $end) {
                $inicioStr = $start->toDateTimeString();
                $fimStr = $end->toDateTimeString();

                $q->whereBetween('inicio', [$inicioStr, $fimStr])
                    ->orWhereBetween('fim', [$inicioStr, $fimStr])
                    ->orWhere(function ($q2) use ($inicioStr, $fimStr) {
                        $q2->where('inicio', '<=', $inicioStr)
                            ->where('fim', '>=', $fimStr);
                    });
            });

        $bloqueios = $bloqueiosQuery->get()->map(function ($b) use ($tz) {
            $iniIso = ($b->inicio instanceof Carbon)
                ? $b->inicio->copy()->setTimezone($tz)->toIso8601String()
                : Carbon::parse($b->inicio, $tz)->toIso8601String();

            $fimIso = ($b->fim instanceof Carbon)
                ? $b->fim->copy()->setTimezone($tz)->toIso8601String()
                : Carbon::parse($b->fim, $tz)->toIso8601String();

            return [
                'id' => 'bloqueio_' . $b->id,
                'title' => $b->motivo ?: 'Bloqueio de agenda',
                'start' => $iniIso,
                'end' => $fimIso,
                'display' => 'background',
                'overlap' => false,
                'backgroundColor' => '#e5e7eb',
                'borderColor' => '#9ca3af',
                'extendedProps' => [
                    'tipo' => 'bloqueio',
                    'motivo' => $b->motivo,
                ],
            ];
        });

        return response()->json($eventos->concat($bloqueios)->values());
    }

    /**
     * Formulário de criação de agendamento - CORRIGIDO
     */
    public function create()
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $usuario = Session::get('usuario');
        $isFuncionario = $usuario->role === 'funcionario';

        if ($isFuncionario) {
            // 🔥 CORREÇÃO: Funcionário só vê serviços que ELE realiza
            $funcionario = DB::table('funcionarios')->where('cpf', $usuario->cpf)->first();

            if (!$funcionario) {
                return redirect()->route('minha.agenda')
                    ->with('error', 'Funcionário não encontrado para este usuário.');
            }

            // Busca serviços que ESTE funcionário realiza
            $servicos = DB::table('servicos')
                ->join('funcionario_servico', 'servicos.id', '=', 'funcionario_servico.servico_id')
                ->where('funcionario_servico.funcionario_id', $funcionario->id)
                ->where('servicos.ativo', 1)
                ->select('servicos.*')
                ->orderBy('servicos.nome')
                ->get();

            // Funcionário só pode escolher a si mesmo
            $funcionarios = DB::table('funcionarios')
                ->where('id', $funcionario->id)
                ->where('ativo', 1)
                ->get();
        } else {
            // Admin: todos os serviços e funcionários
            $servicos = DB::table('servicos')
                ->where('ativo', 1)
                ->orderBy('nome')
                ->get();

            $funcionarios = DB::table('funcionarios')
                ->where('ativo', 1)
                ->orderBy('nome')
                ->get();
        }

        $clientes = DB::table('clientes')->orderBy('nome')->get();
        $statusOptions = ['agendado', 'confirmado', 'concluido', 'cancelado'];

        return view('agenda.create', compact(
            'funcionarios',
            'clientes',
            'servicos',
            'statusOptions',
            'isFuncionario'
        ));
    }

    /**
     * Retorna, em JSON, os funcionários ativos que realizam determinado serviço
     * para uso em AJAX no formulário de agendamento.
     */
    public function funcionariosPorServico(Request $request)
    {
        if (!Session::has('usuario')) {
            return response()->json([], 401);
        }

        $request->validate([
            'servico_id' => 'required|integer|exists:servicos,id',
        ]);

        $servicoId = $request->servico_id;

        $funcionarios = DB::table('funcionarios')
            ->join('funcionario_servico', 'funcionario_servico.funcionario_id', '=', 'funcionarios.id')
            ->where('funcionario_servico.servico_id', $servicoId)
            ->where('funcionarios.ativo', 1)
            ->orderBy('funcionarios.nome')
            ->select('funcionarios.id', 'funcionarios.nome')
            ->get();

        return response()->json($funcionarios);
    }

    /**
     * Salva novo agendamento com validações
     * - Permite hoje e qualquer futuro
     * - Permite até 24h no passado
     * - Mais de 24h atrás: bloqueia
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
            'cliente_id' => 'required|integer|exists:clientes,id',
            'servico_id' => 'required|integer|exists:servicos,id',
            'data' => 'required|date|before_or_equal:2100-12-31',
            'hora' => 'required|date_format:H:i',
            'status' => 'required|in:agendado,confirmado,concluido,cancelado',
            'observacoes' => 'nullable|string|max:500',
        ]);

        $tz = config('app.timezone', 'America/Sao_Paulo');

        // BUSCA SERVIÇO ATIVO
        $servico = DB::table('servicos')
            ->where('id', $request->servico_id)
            ->where('ativo', 1)
            ->first();

        if (!$servico) {
            return back()
                ->withErrors(['servico_id' => 'Serviço inativo ou não encontrado.'])
                ->withInput();
        }

        // GARANTE QUE O FUNCIONÁRIO REALIZA ESTE SERVIÇO
        $relacaoValida = DB::table('funcionario_servico')
            ->where('servico_id', $request->servico_id)
            ->where('funcionario_id', $request->funcionario_id)
            ->exists();

        if (!$relacaoValida) {
            return back()
                ->withErrors(['funcionario_id' => 'Este profissional não está habilitado para realizar este serviço.'])
                ->withInput();
        }

        $duracao = max(1, (int) ($servico->duracao_minutos ?? 30));

        // Interpreta data/hora informadas como horário local da app
        $inicio = Carbon::parse($request->data . ' ' . $request->hora, $tz);
        $fim = (clone $inicio)->addMinutes($duracao);

        // Não pode criar para horários com mais de 24h no passado
        $agora = Carbon::now($tz);
        $limitePassado = $agora->copy()->subHours(24);

        if ($inicio->lt($limitePassado)) {
            return back()
                ->withErrors(['hora' => 'Não é possível agendar para horários com mais de 24 horas no passado.'])
                ->withInput();
        }

        // horários do expediente (considerando exceções por dia + dias da semana)
        [$limiteInicio, $limiteFim, $min, $max, $diaAberto] = $this->getLimitesExpedienteParaData($inicio);

        if (!$diaAberto) {
            return back()
                ->withErrors(['hora' => 'Este dia da semana está configurado como fechado na agenda.'])
                ->withInput();
        }

        if ($inicio->lt($limiteInicio) || $fim->gt($limiteFim)) {
            return back()
                ->withErrors(['hora' => "Horário fora do expediente configurado para este dia ({$min}–{$max})."])
                ->withInput();
        }

        // sem sobreposição para o mesmo funcionário
        $conflito = Agenda::where('funcionario_id', $request->funcionario_id)
            ->where(function ($q) use ($inicio, $fim) {
                $q->whereBetween('inicio', [$inicio->copy(), $fim->copy()->subMinute()])
                    ->orWhereBetween('fim', [$inicio->copy()->addMinute(), $fim->copy()])
                    ->orWhere(function ($q2) use ($inicio, $fim) {
                        $q2->where('inicio', '<=', $inicio)->where('fim', '>=', $fim);
                    });
            })->exists();

        if ($conflito) {
            return back()
                ->withErrors(['hora' => 'Já existe atendimento neste horário para o funcionário.'])
                ->withInput();
        }

        // respeitar bloqueios (gerais + específicos)
        if ($this->existeBloqueioNoPeriodo($request->funcionario_id, $inicio, $fim)) {
            return back()
                ->withErrors(['hora' => 'Período bloqueado para este funcionário.'])
                ->withInput();
        }

        $agenda = Agenda::create([
            'funcionario_id' => $request->funcionario_id,
            'cliente_id' => $request->cliente_id,
            'servico_id' => $request->servico_id,
            'inicio' => $inicio,
            'fim' => $fim,
            'status' => $request->input('status', 'agendado'),
            'observacoes' => $request->observacoes,
        ]);

        // 🔐 LOG: criação (usando service genérico)
        $this->registrarLog('create', $agenda);

        // Redireciona para a rota correta baseada no tipo de usuário
        if ($usuario->role === 'funcionario') {
            return redirect()->route('minha.agenda')
                ->with('success', 'Agendamento criado com sucesso!');
        } else {
            return redirect()->route('agenda.index', ['funcionario_id' => $request->funcionario_id])
                ->with('success', 'Agendamento criado com sucesso!');
        }
    }

    /**
     * Formulário de edição - CORRIGIDO
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
        $isFuncionario = $usuario->role === 'funcionario';

        // Verifica permissão do funcionário
        if ($isFuncionario) {
            $funcionario = DB::table('funcionarios')->where('cpf', $usuario->cpf)->first();
            if ($funcionario && $agenda->funcionario_id !== $funcionario->id) {
                abort(403, 'Você não tem permissão para editar este agendamento.');
            }
        }

        if ($isFuncionario) {
            // 🔥 CORREÇÃO: Funcionário só vê serviços que ELE realiza
            $funcionario = DB::table('funcionarios')->where('cpf', $usuario->cpf)->first();

            $servicos = DB::table('servicos')
                ->join('funcionario_servico', 'servicos.id', '=', 'funcionario_servico.servico_id')
                ->where('funcionario_servico.funcionario_id', $funcionario->id)
                ->where(function ($q) use ($agenda) {
                    $q->where('servicos.ativo', 1)
                        ->orWhere('servicos.id', $agenda->servico_id);
                })
                ->select('servicos.*')
                ->orderBy('servicos.nome')
                ->get();

            // Funcionário só pode escolher a si mesmo
            $funcionarios = DB::table('funcionarios')
                ->where('id', $funcionario->id)
                ->where('ativo', 1)
                ->get();
        } else {
            // Admin: todos os serviços e funcionários
            $servicos = DB::table('servicos')
                ->where(function ($q) use ($agenda) {
                    $q->where('ativo', 1)
                        ->orWhere('id', $agenda->servico_id);
                })
                ->orderBy('nome')
                ->get();

            $funcionarios = DB::table('funcionarios')
                ->where('ativo', 1)
                ->orderBy('nome')
                ->get();
        }

        $clientes = DB::table('clientes')->orderBy('nome')->get();

        // Interpreta como horário local da app para exibir no form
        $data = Carbon::parse($agenda->inicio, $tz)->format('Y-m-d');
        $hora = Carbon::parse($agenda->inicio, $tz)->format('H:i');

        $statusOptions = ['agendado', 'confirmado', 'concluido', 'cancelado'];

        return view('agenda.create', [
            'agenda' => $agenda,
            'funcionarios' => $funcionarios,
            'clientes' => $clientes,
            'servicos' => $servicos,
            'data' => $data,
            'hora' => $hora,
            'statusOptions' => $statusOptions,
            'isFuncionario' => $isFuncionario
        ]);
    }

    /**
     * Atualiza um agendamento existente
     * - Pode mudar status / serviço / funcionário a qualquer momento
     * - Só valida horário/dia quando mexe no período (início/fim)
     * - Para data/hora: permite até 24h no passado; mais que isso, bloqueia
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
            'cliente_id' => 'required|integer|exists:clientes,id',
            'servico_id' => 'required|integer|exists:servicos,id',
            'data' => 'required|date|before_or_equal:2100-12-31',
            'hora' => 'required|date_format:H:i',
            'status' => 'required|in:agendado,confirmado,concluido,cancelado',
            'observacoes' => 'nullable|string|max:500',
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

        // BUSCA SERVIÇO ATIVO (ou o mesmo, se estiver editando algo antigo)
        $servico = DB::table('servicos')
            ->where('id', $request->servico_id)
            ->where(function ($q) use ($agenda) {
                $q->where('ativo', 1)
                    ->orWhere('id', $agenda->servico_id);
            })
            ->first();

        if (!$servico) {
            return back()
                ->withErrors(['servico_id' => 'Serviço inativo ou não encontrado.'])
                ->withInput();
        }

        // GARANTE QUE O FUNCIONÁRIO REALIZA ESTE SERVIÇO
        $relacaoValida = DB::table('funcionario_servico')
            ->where('servico_id', $request->servico_id)
            ->where('funcionario_id', $request->funcionario_id)
            ->exists();

        if (!$relacaoValida) {
            return back()
                ->withErrors(['funcionario_id' => 'Este profissional não está habilitado para realizar este serviço.'])
                ->withInput();
        }

        $duracao = max(1, (int) ($servico->duracao_minutos ?? 30));

        $inicio = Carbon::parse($request->data . ' ' . $request->hora, $tz);
        $fim = (clone $inicio)->addMinutes($duracao);

        // Detecta se mexeu no período (início ou fim)
        $inicioOriginal = $agenda->inicio instanceof Carbon
            ? $agenda->inicio->copy()->setTimezone($tz)
            : Carbon::parse($agenda->inicio, $tz);

        $fimOriginal = $agenda->fim instanceof Carbon
            ? $agenda->fim->copy()->setTimezone($tz)
            : Carbon::parse($agenda->fim, $tz);

        $mexeuPeriodo = !$inicio->equalTo($inicioOriginal) || !$fim->equalTo($fimOriginal);

        if ($mexeuPeriodo) {
            // Não permitir reagendar para horários com mais de 24h no passado
            $agora = Carbon::now($tz);
            $limitePassado = $agora->copy()->subHours(24);

            if ($inicio->lt($limitePassado)) {
                return back()
                    ->withErrors(['hora' => 'Não é possível reagendar para horários com mais de 24 horas no passado.'])
                    ->withInput();
            }

            // horários do expediente (considerando exceções por dia + dias da semana)
            [$limiteInicio, $limiteFim, $min, $max, $diaAberto] = $this->getLimitesExpedienteParaData($inicio);

            if (!$diaAberto) {
                return back()
                    ->withErrors(['hora' => 'Este dia da semana está configurado como fechado na agenda.'])
                    ->withInput();
            }

            if ($inicio->lt($limiteInicio) || $fim->gt($limiteFim)) {
                return back()
                    ->withErrors(['hora' => "Horário fora do expediente configurado para este dia ({$min}–{$max})."])
                    ->withInput();
            }

            // sem sobreposição para o mesmo funcionário (ignorando o próprio evento)
            $conflito = Agenda::where('funcionario_id', $request->funcionario_id)
                ->where('id', '<>', $agenda->id)
                ->where(function ($q) use ($inicio, $fim) {
                    $q->whereBetween('inicio', [$inicio->copy(), $fim->copy()->subMinute()])
                        ->orWhereBetween('fim', [$inicio->copy()->addMinute(), $fim->copy()])
                        ->orWhere(function ($q2) use ($inicio, $fim) {
                            $q2->where('inicio', '<=', $inicio)->where('fim', '>=', $fim);
                        });
                })->exists();

            if ($conflito) {
                return back()
                    ->withErrors(['hora' => 'Já existe atendimento neste horário para o funcionário.'])
                    ->withInput();
            }

            // respeitar bloqueios (gerais + específicos)
            if ($this->existeBloqueioNoPeriodo($request->funcionario_id, $inicio, $fim)) {
                return back()
                    ->withErrors(['hora' => 'Período bloqueado para este funcionário.'])
                    ->withInput();
            }
        }

        $antigoStatus = $agenda->status;

        // 🔐 LOG: salvar snapshot antigo ANTES do update
        $dadosAntigos = $agenda->toArray();

        $agenda->update([
            'funcionario_id' => $request->funcionario_id,
            'cliente_id' => $request->cliente_id,
            'servico_id' => $request->servico_id,
            'inicio' => $inicio,
            'fim' => $fim,
            'status' => $request->status,
            'observacoes' => $request->observacoes,
        ]);

        $agenda->refresh();

        // 🔐 LOG: atualização com diferenças (service genérico)
        $this->registrarLog('update', $agenda, $dadosAntigos);

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
                ->with('success', 'Agendamento atualizado com sucesso!');
        } else {
            return redirect()->route('agenda.index', ['funcionario_id' => $request->funcionario_id])
                ->with('success', 'Agendamento atualizado com sucesso!');
        }
    }

    /**
     * Atualização rápida de status
     * (não aplica regra de horário, só mexe em status)
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

        // 🔐 LOG: snapshot antes
        $dadosAntigos = $agenda->toArray();

        $agenda->update(['status' => $request->status]);
        $agenda->refresh();

        // 🔐 LOG: atualização rápida de status
        $this->registrarLog('update_status', $agenda, $dadosAntigos);

        // HOOKS DE COMISSÃO (mesma regra do update normal)
        if ($request->status === 'concluido' && $antigoStatus !== 'concluido') {
            ComissaoService::gerarParaAgenda($agenda);
        }

        if ($request->status === 'cancelado' && $antigoStatus === 'concluido') {
            ComissaoService::estornarPorAgendaId($agenda->id);
        }


        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'status' => $agenda->status]);
        }

        return back()->with('success', 'Status atualizado com sucesso!');
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
                    ->orWhereBetween('fim', [$inicio, $fim])
                    ->orWhere(function ($q2) use ($inicio, $fim) {
                        $q2->where('inicio', '<=', $inicio)
                            ->where('fim', '>=', $fim);
                    });
            })
            ->exists();
    }

    /**
     * Retorna limites de expediente (início/fim) para a data informada.
     * Se houver exceção cadastrada para o dia, usa ela; senão, usa o padrão do Setting
     * e respeita também os dias da semana configurados.
     *
     * @return array [Carbon $limiteInicio, Carbon $limiteFim, string $horaMin, string $horaMax, bool $diaAberto]
     */
    private function getLimitesExpedienteParaData(Carbon $data): array
    {
        $tz = config('app.timezone', 'America/Sao_Paulo');

        // Procura exceção de expediente para este dia
        $excecao = AgendaExpedienteExcecao::whereDate('data', $data->toDateString())->first();

        if ($excecao) {
            // Se tem exceção, o dia é considerado ABERTO, independente da config padrão
            $horaMin = substr($excecao->inicio, 0, 5); // "05:00"
            $horaMax = substr($excecao->fim, 0, 5);    // "21:00"
            $diaAberto = true;
        } else {
            // Sem exceção -> usa o padrão global + dias da semana
            $horaMin = Setting::get('expediente_inicio', '08:00');
            $horaMax = Setting::get('expediente_fim', '18:00');

            $diasAbertos = $this->getDiasSemanaAbertos();
            $dow = (int) $data->dayOfWeek; // 0=Dom, 6=Sáb

            $diaAberto = in_array($dow, $diasAbertos, true);
        }

        $limiteInicio = Carbon::parse($data->format('Y-m-d') . ' ' . $horaMin, $tz);
        $limiteFim = Carbon::parse($data->format('Y-m-d') . ' ' . $horaMax, $tz);

        return [$limiteInicio, $limiteFim, $horaMin, $horaMax, $diaAberto];
    }

    /**
     * Lê a configuração "expediente_dias_semana" e devolve um array
     * com os índices dos dias ABERTOS: [1,2,3,4,5] por exemplo.
     *
     * Aceita dois formatos no banco:
     *  - "0,1,1,1,1,1,0" (bits: 0=fechado,1=aberto para Dom..Sáb)
     *  - "1,2,3,4,5" (lista de índices abertos)
     */
    private function getDiasSemanaAbertos(): array
    {
        $str = Setting::get('expediente_dias_semana');

        // padrão: seg–sex
        if ($str === null || $str === '') {
            return [1, 2, 3, 4, 5];
        }

        $parts = array_filter(explode(',', $str), 'strlen');
        $parts = array_values($parts);

        // Detecta se está no formato bits (7 posições, só 0 ou 1)
        $isBits = count($parts) === 7;
        if ($isBits) {
            foreach ($parts as $p) {
                if (!in_array((string) $p, ['0', '1'], true)) {
                    $isBits = false;
                    break;
                }
            }
        }

        if ($isBits) {
            $dias = [];
            foreach ($parts as $i => $flag) {
                if ((int) $flag === 1) {
                    $dias[] = $i; // índice do dia aberto
                }
            }
            return $dias;
        }

        // Caso contrário, assume que é lista de índices já (ex: "1,2,3,4,5")
        return array_values(array_unique(array_map('intval', $parts)));
    }

    public function destroy($id)
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $usuario = Session::get('usuario');

        if ($usuario->role !== 'admin') {
            abort(403, 'Apenas administradores podem excluir agendamentos.');
        }

        $agenda = Agenda::findOrFail($id);

        // 🔐 LOG: antes de excluir, guarda tudo (service genérico)
        $this->registrarLogDelete($agenda);

        // Se já tinha comissão, estorna
        if ($agenda->status === 'concluido') {
            ComissaoService::estornarPorAgendaId($agenda->id);
        }

        $agenda->delete();

        return redirect()->route('agenda.index')
            ->with('success', 'Agendamento excluído com sucesso!');
    }

    /**
     * 🔐 Helper: registra LOG de create/update/update_status (wrappers pro service)
     */
    private function registrarLog(string $action, Agenda $agenda, ?array $dadosAntigos = null): void
    {
        LogAuditoriaService::registrarModel($action, $agenda, $dadosAntigos);
    }

    /**
     * 🔐 Helper: registrar LOG de exclusão
     */
    private function registrarLogDelete(Agenda $agenda): void
    {
        LogAuditoriaService::registrarDeleteModel($agenda);
    }
}