<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;

class LoginController extends Controller
{
    /** Tela de login; só redireciona se for admin/funcionário */
    public function index()
    {
        if (Session::has('usuario')) {
            $u = Session::get('usuario');
            $papel = strtolower($u->role ?? $u->tipo ?? '');
            if (in_array($papel, ['admin','funcionario'])) {
                return redirect()->route('dashboard');
            }
            // cliente logado não entra no painel
            Session::forget('usuario');
            return redirect()->route('login')->with('erro','Contas de cliente não acessam o painel.');
        }
        return view('auth.login');
    }

    /** Autentica CPF+senha; barra clientes e salva role na sessão */
    public function autenticar(Request $request)
    {
        $request->validate([
            'cpf'   => 'required|string|regex:/^\d{11}$/',
            'senha' => 'required|string',
        ], [
            'cpf.regex' => 'O CPF deve conter exatamente 11 números.',
        ]);

        $cpf = preg_replace('/\D/', '', $request->cpf);
        
        // Garantir que tem exatamente 11 dígitos numéricos
        if (!preg_match('/^\d{11}$/', $cpf)) {
            return back()->withErrors([
                'cpf' => 'O CPF deve conter exatamente 11 números.'
            ])->withInput();
        }

        $usuario = DB::table('usuarios')->where('cpf', $cpf)->first();

        if ($usuario && Hash::check($request->senha, $usuario->senha)) {
            $papel = strtolower($usuario->role ?? $usuario->tipo ?? '');

            // clientes não acessam o painel
            if (!in_array($papel, ['admin','funcionario'])) {
                Session::forget('usuario');
                return back()->withErrors([
                    'login' => 'Sua conta (cliente) não tem acesso ao painel.'
                ])->withInput();
            }

            // normaliza estrutura da sessão
            $sessao = (object)[
                'id'    => $usuario->id ?? null,
                'nome'  => $usuario->nome ?? ($usuario->name ?? 'Usuário'),
                'cpf'   => $usuario->cpf ?? $cpf,
                'email' => $usuario->email ?? null,
                'role'  => $papel,
            ];

            Session::put('usuario', $sessao);
            $request->session()->regenerate();

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'cpf' => 'CPF ou senha inválidos.'
        ])->withInput(['cpf' => $request->cpf]);
    }

    /** Alteração de senha do usuário logado */
    public function updatePassword(Request $request)
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $request->validate([
            'senha_atual'  => 'required|string',
            'nova_senha'   => 'required|string|min:6|max:72|confirmed',
        ], [
            'senha_atual.required' => 'Informe a senha atual.',
            'nova_senha.required'  => 'Informe a nova senha.',
            'nova_senha.min'       => 'A nova senha deve ter no mínimo :min caracteres.',
            'nova_senha.confirmed' => 'A confirmação da nova senha não confere.',
        ]);

        $sess = Session::get('usuario');

        // 1) localizar usuário por id -> cpf -> email
        $sessId   = $sess->id ?? $sess->id_usuario ?? $sess->usuario_id ?? null;
        $sessCpf  = isset($sess->cpf) ? preg_replace('/\D/', '', $sess->cpf) : null;
        $sessMail = $sess->email ?? null;

        $usuarioDb = null;
        if ($sessId) {
            $usuarioDb = DB::table('usuarios')->where('id', $sessId)->first();
        }
        if (!$usuarioDb && $sessCpf) {
            $usuarioDb = DB::table('usuarios')->where('cpf', $sessCpf)->first();
        }
        if (!$usuarioDb && $sessMail) {
            try {
                $usuarioDb = DB::table('usuarios')->where('email', $sessMail)->first();
            } catch (\Throwable $e) {
                // se não existir coluna email, ignora
            }
        }

        if (!$usuarioDb) {
            return back()->with('error', 'Usuário da sessão não encontrado na tabela "usuarios".');
        }

        // 2) descobrir onde está a senha (senha OU password)
        $hashAtual = null;
        if (isset($usuarioDb->senha)) {
            $hashAtual = $usuarioDb->senha;
        } elseif (isset($usuarioDb->password)) {
            $hashAtual = $usuarioDb->password;
        } else {
            return back()->with('error', 'Campo de senha não encontrado (esperado "senha" ou "password").');
        }

        // 3) conferir a senha atual
        if (!Hash::check($request->input('senha_atual'), $hashAtual)) {
            return back()->with('error', 'Senha atual incorreta.');
        }

        // 4) gerar novo hash e atualizar somente colunas existentes
        $novoHash = Hash::make($request->input('nova_senha'));

        $query = DB::table('usuarios');
        if (isset($usuarioDb->id)) {
            $query->where('id', $usuarioDb->id);
        } elseif (isset($usuarioDb->cpf)) {
            $query->where('cpf', preg_replace('/\D/','', $usuarioDb->cpf));
        } elseif (isset($usuarioDb->email)) {
            $query->where('email', $usuarioDb->email);
        } else {
            if ($sessId) {
                $query->where('id', $sessId);
            } else {
                return back()->with('error', 'Não foi possível identificar o registro do usuário para atualizar.');
            }
        }

        $data = ['updated_at' => now()];
        if (Schema::hasColumn('usuarios', 'senha')) {
            $data['senha'] = $novoHash;
        }
        if (Schema::hasColumn('usuarios', 'password')) {
            $data['password'] = $novoHash;
        }
        if (count($data) === 1) {
            return back()->with('error', 'A tabela "usuarios" não possui coluna "senha" nem "password".');
        }

        $affected = $query->update($data);
        if ($affected < 1) {
            return back()->with('error', 'Nenhuma linha foi atualizada. Verifique as chaves (id/cpf/email).');
        }

        return back()->with('success', 'Senha alterada com sucesso!');
    }

    /** Dashboard detalhado com filtros e datasets (preparado p/ relatórios) */
    public function dashboard(Request $request)
    {
        // Garantir login e papel
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }
        $usuario = Session::get('usuario');
        $papel = strtolower($usuario->role ?? $usuario->tipo ?? '');
        if (!in_array($papel, ['admin', 'funcionario'])) {
            abort(403, 'Acesso permitido apenas para administradores e funcionários.');
        }

        // Coluna de data na tabela agendas (auto-detecta 'inicio' ou 'data')
        $agendaTable = 'agendas';
        $agendaDateCol = Schema::hasColumn($agendaTable, 'inicio')
            ? 'inicio'
            : (Schema::hasColumn($agendaTable, 'data') ? 'data' : 'inicio');

        // Filtros
        $periodoDias   = (int)($request->get('periodo', 30));
        if (!in_array($periodoDias, [7,14,30,90])) $periodoDias = 30;

        $funcionarioId = $request->get('funcionario_id');
        $servicoId     = $request->get('servico_id');

        $inicio = now()->startOfDay()->subDays($periodoDias - 1);
        $fim    = now()->endOfDay();

        // KPIs
        $stats = [
            'total_funcionarios' => 0,
            'total_clientes'     => 0,
            'total_servicos'     => 0,
            'agendamentos_hoje'  => 0,
        ];
        try { $stats['total_funcionarios'] = DB::table('funcionarios')->count(); } catch (\Throwable $e) {}
        try { $stats['total_clientes']     = DB::table('clientes')->count(); } catch (\Throwable $e) {}
        try { $stats['total_servicos']     = DB::table('servicos')->count(); } catch (\Throwable $e) {}
        try {
            $stats['agendamentos_hoje'] = DB::table($agendaTable)
                ->whereDate($agendaDateCol, now()->toDateString())
                ->count();
        } catch (\Throwable $e) {}

        // Filtros (combos)
        $filtros = ['funcionarios'=>[], 'servicos'=>[]];
        try { $filtros['funcionarios'] = DB::table('funcionarios')->select('id','nome')->orderBy('nome')->get(); } catch (\Throwable $e) {}
        try { $filtros['servicos']     = DB::table('servicos')->select('id','nome')->orderBy('nome')->get(); } catch (\Throwable $e) {}

        // Query base para agendas (aplica filtros + período)
        $agendaBase = function() use ($agendaTable, $agendaDateCol, $inicio, $fim, $funcionarioId, $servicoId) {
            $q = DB::table($agendaTable . ' as a')
                ->leftJoin('clientes as c', 'c.id', '=', 'a.cliente_id')
                ->leftJoin('servicos as s', 's.id', '=', 'a.servico_id')
                ->leftJoin('funcionarios as f', 'f.id', '=', 'a.funcionario_id')
                ->whereBetween("a.$agendaDateCol", [$inicio, $fim]);

            if (!empty($funcionarioId)) $q->where('a.funcionario_id', $funcionarioId);
            if (!empty($servicoId))     $q->where('a.servico_id', $servicoId);

            return $q;
        };

        // 1) Agendamentos por dia (série temporal)
        $agendamentosPorDia = [];
        try {
            // Preenche todos os dias com zero
            $cursor = (clone $inicio);
            while ($cursor <= $fim) {
                $agendamentosPorDia[$cursor->format('d/m')] = 0;
                $cursor->addDay();
            }

            $rows = (clone $agendaBase)()
                ->select(
                    DB::raw("DATE_FORMAT(a.$agendaDateCol, '%d/%m') as dia"),
                    DB::raw('COUNT(*) as total')
                )
                ->groupBy('dia')
                ->orderBy(DB::raw("STR_TO_DATE(dia, '%d/%m')"))
                ->get();

            foreach ($rows as $r) {
                if (isset($agendamentosPorDia[$r->dia])) {
                    $agendamentosPorDia[$r->dia] = (int)$r->total;
                }
            }
        } catch (\Throwable $e) {
            // mantém zerado se der erro
        }

        // 2) Distribuição por status (detecta 'status' ou 'situacao', normaliza e preenche faltantes)
        $agendamentosPorStatus = [];
        try {
            // Qual coluna representa status?
            $statusCol = null;
            if (Schema::hasColumn($agendaTable, 'status')) {
                $statusCol = 'status';
            } elseif (Schema::hasColumn($agendaTable, 'situacao')) {
                $statusCol = 'situacao';
            }

            if ($statusCol) {
                $rows = (clone $agendaBase)()
                    ->select(
                        DB::raw("LOWER(COALESCE(a.$statusCol, 'indefinido')) as st"),
                        DB::raw('COUNT(*) as total')
                    )
                    ->groupBy('st')
                    ->get();

                // ordem desejada
                $ordem = ['agendado', 'confirmado', 'concluido', 'cancelado', 'indefinido'];
                $tmp = [];
                foreach ($rows as $r) {
                    $tmp[$r->st] = (int)$r->total;
                }
                foreach ($ordem as $k) {
                    $agendamentosPorStatus[$k] = $tmp[$k] ?? 0;
                }
            } else {
                // Sem coluna de status/situacao
                $agendamentosPorStatus = [
                    'agendado'   => 0,
                    'confirmado' => 0,
                    'concluido'  => 0,
                    'cancelado'  => 0,
                    'indefinido' => 0,
                ];
            }
        } catch (\Throwable $e) {
            $agendamentosPorStatus = [
                'agendado'   => 0,
                'confirmado' => 0,
                'concluido'  => 0,
                'cancelado'  => 0,
                'indefinido' => 0,
            ];
        }

        // 3) Top serviços
        $topServicos = [];
        try {
            $topServicos = (clone $agendaBase)()
                ->select('s.nome', DB::raw('COUNT(*) as total'))
                ->groupBy('s.nome')
                ->orderByDesc('total')
                ->limit(7)
                ->get()
                ->map(fn($r) => (object)[
                    'nome'  => $r->nome ?? '—',
                    'total' => (int)$r->total
                ])
                ->toArray();
        } catch (\Throwable $e) {}

        // 4) Top funcionários
        $topFuncionarios = [];
        try {
            $topFuncionarios = (clone $agendaBase)()
                ->select('f.nome', DB::raw('COUNT(*) as total'))
                ->groupBy('f.nome')
                ->orderByDesc('total')
                ->limit(7)
                ->get()
                ->map(fn($r) => (object)[
                    'nome'  => $r->nome ?? '—',
                    'total' => (int)$r->total
                ])
                ->toArray();
        } catch (\Throwable $e) {}

        // 5) Próximos agendamentos
        $proximos = [];
        try {
            $proximos = DB::table($agendaTable . ' as a')
                ->leftJoin('clientes as c', 'c.id', '=', 'a.cliente_id')
                ->leftJoin('servicos as s', 's.id', '=', 'a.servico_id')
                ->leftJoin('funcionarios as f', 'f.id', '=', 'a.funcionario_id')
                ->where("a.$agendaDateCol", '>=', now())
                ->orderBy("a.$agendaDateCol")
                ->limit(8)
                ->get([
                    DB::raw("DATE_FORMAT(a.$agendaDateCol, '%d/%m/%Y %H:%i') as data_hora"),
                    'a.status',
                    'c.nome as cliente_nome',
                    's.nome as servico_nome',
                    'f.nome as funcionario_nome',
                    "a.$agendaDateCol",
                ]);
        } catch (\Throwable $e) {}

        // 6) Cancelados recentes
        $canceladosRecentes = [];
        try {
            $canceladosRecentes = (clone $agendaBase)()
                ->where('a.status', 'cancelado')
                ->orderByDesc("a.$agendaDateCol")
                ->limit(8)
                ->get([
                    DB::raw("DATE_FORMAT(a.$agendaDateCol, '%d/%m/%Y %H:%i') as data_hora"),
                    'c.nome as cliente_nome',
                    's.nome as servico_nome',
                    'f.nome as funcionario_nome',
                    "a.$agendaDateCol",
                ]);
        } catch (\Throwable $e) {}

        $meta = [
            'inicio_fmt'   => $inicio->format('d/m/Y'),
            'fim_fmt'      => $fim->format('d/m/Y'),
            'periodo_dias' => $periodoDias,
        ];

        return view('dashboard', [
            'usuario'               => $usuario,
            'stats'                 => $stats,
            'filtros'               => $filtros,
            'meta'                  => $meta,
            'agendamentosPorDia'    => $agendamentosPorDia,
            'agendamentosPorStatus' => $agendamentosPorStatus,
            'topServicos'           => $topServicos,
            'topFuncionarios'       => $topFuncionarios,
            'proximos'              => $proximos,
            'canceladosRecentes'    => $canceladosRecentes,
        ]);
    }

    /**
     * Encerra a sessão do usuário e redireciona para o login.
     */
    public function logout(Request $request)
    {
        Session::forget('usuario');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Você saiu da conta com sucesso!');
    }
}