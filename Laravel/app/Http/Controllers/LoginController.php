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
            $u     = Session::get('usuario');

            // se ainda precisa trocar a senha, manda direto pra tela de mudança
            if (!empty($u->precisa_trocar_senha)) {
                return redirect()->route('me.senha.form');
            }

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

    /**
     * Autentica CPF+senha
     * - valida CPF
     * - bloqueia conta após 3 tentativas inválidas
     * - respeita campo "bloqueado"
     * - força troca de senha se precisa_trocar_senha = true
     */
    public function autenticar(Request $request)
    {
        $request->validate([
            'cpf'   => 'required|string|regex:/^\d{11}$/',
            'senha' => 'required|string',
        ], [
            'cpf.regex' => 'O CPF deve conter exatamente 11 números.',
        ]);

        $cpf   = preg_replace('/\D/', '', $request->cpf);
        $senha = $request->senha;

        if (!preg_match('/^\d{11}$/', $cpf)) {
            return back()->withErrors([
                'cpf' => 'O CPF deve conter exatamente 11 números.'
            ])->withInput();
        }

        $usuario = DB::table('usuarios')->where('cpf', $cpf)->first();

        if (!$usuario) {
            // usuário não encontrado → não sabemos tentativas, só mensagem genérica
            return back()->withErrors([
                'cpf' => 'CPF ou senha inválidos.'
            ])->withInput(['cpf' => $request->cpf]);
        }

        // ⚠️ se já estiver bloqueado, nem tenta
        if (!empty($usuario->bloqueado)) {
            return back()->withErrors([
                'cpf' => 'Esta conta está bloqueada por tentativas inválidas. Peça a um administrador para desbloquear.'
            ])->withInput(['cpf' => $request->cpf]);
        }

        // senha errada → incrementa tentativas e pode bloquear
        if (!Hash::check($senha, $usuario->senha)) {
            $tentativas = (int)($usuario->tentativas_falhas ?? 0) + 1;
            $bloquear   = $tentativas >= 3;

            DB::table('usuarios')
                ->where('id', $usuario->id)
                ->update([
                    'tentativas_falhas' => $tentativas,
                    'bloqueado'         => $bloquear,
                    'updated_at'        => now(),
                ]);

            $msg = $bloquear
                ? 'Conta bloqueada após múltiplas tentativas inválidas. Peça a um administrador para desbloquear.'
                : 'CPF ou senha inválidos.';

            return back()->withErrors([
                'cpf' => $msg
            ])->withInput(['cpf' => $request->cpf]);
        }

        // senha correta → zera tentativas e garante não bloqueado
        DB::table('usuarios')
            ->where('id', $usuario->id)
            ->update([
                'tentativas_falhas' => 0,
                'bloqueado'         => false,
                'updated_at'        => now(),
            ]);

        // pega registro atualizado (incluindo flags)
        $usuario = DB::table('usuarios')->where('id', $usuario->id)->first();

        $papel = strtolower($usuario->role ?? $usuario->tipo ?? '');

        // clientes não acessam o painel
        if (!in_array($papel, ['admin','funcionario'])) {
            Session::forget('usuario');
            return back()->withErrors([
                'login' => 'Sua conta (cliente) não tem acesso ao painel.'
            ])->withInput();
        }

        // normaliza estrutura da sessão (inclui flag precisa_trocar_senha)
        $sessao = (object)[
            'id'                   => $usuario->id ?? null,
            'nome'                 => $usuario->nome ?? ($usuario->name ?? 'Usuário'),
            'cpf'                  => $usuario->cpf ?? $cpf,
            'email'                => $usuario->email ?? null,
            'role'                 => $papel,
            'precisa_trocar_senha' => (bool)($usuario->precisa_trocar_senha ?? false),
        ];

        Session::put('usuario', $sessao);
        $request->session()->regenerate();

        // se precisa trocar a senha, manda pra tela específica
        if (!empty($usuario->precisa_trocar_senha)) {
            return redirect()->route('me.senha.form');
        }

        return redirect()->route('dashboard');
    }

    /** Formulário para o usuário alterar a própria senha (apenas fluxo de primeiro acesso) */
    public function formPassword()
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $usuario = Session::get('usuario');

        // 🔒 Só permite acessar essa tela se realmente precisar trocar a senha
        if (empty($usuario->precisa_trocar_senha)) {
            return redirect()->route('dashboard');
        }

        return view('auth.mudar_senha', [
            'usuario' => $usuario,
        ]);
    }

    /**
     * Alteração de senha do usuário logado
     * - NÃO exige senha atual (primeiro acesso / troca forçada)
     * - exige senha forte (maiúscula, minúscula, número e símbolo)
     * - zera tentativas, desbloqueia e limpa precisa_trocar_senha
     * - 🔒 Só pode ser usada no fluxo de primeiro acesso
     */
    public function updatePassword(Request $request)
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $sess = Session::get('usuario');

        // 🔒 Garante que só quem está em "precisa_trocar_senha" pode usar esse endpoint
        if (empty($sess->precisa_trocar_senha)) {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'nova_senha' => [
                'required',
                'string',
                'min:8',
                'max:72',
                // pelo menos 1 minúscula, 1 maiúscula, 1 número e 1 caractere especial:
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,72}$/',
            ],
            'confirmar_senha' => 'required|string|same:nova_senha',
        ], [
            'nova_senha.required'      => 'Informe a nova senha.',
            'nova_senha.min'           => 'A nova senha deve ter no mínimo :min caracteres.',
            'nova_senha.regex'         => 'A senha deve ter letras maiúsculas, minúsculas, números e caracteres especiais.',
            'confirmar_senha.required' => 'Confirme a nova senha.',
            'confirmar_senha.same'     => 'A confirmação da nova senha não confere.',
        ]);

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

        // NÃO confere senha atual porque é primeiro acesso / troca forçada

        // 3) gerar novo hash e atualizar
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
        if (Schema::hasColumn('usuarios', 'precisa_trocar_senha')) {
            $data['precisa_trocar_senha'] = false;
        }
        if (Schema::hasColumn('usuarios', 'tentativas_falhas')) {
            $data['tentativas_falhas'] = 0;
        }
        if (Schema::hasColumn('usuarios', 'bloqueado')) {
            $data['bloqueado'] = false;
        }

        if (count($data) === 1) {
            return back()->with('error', 'A tabela "usuarios" não possui coluna "senha" nem "password".');
        }

        $affected = $query->update($data);
        if ($affected < 1) {
            return back()->with('error', 'Nenhuma linha foi atualizada. Verifique as chaves (id/cpf/email).');
        }

        // Atualiza sessão (caso flags/role tenham mudado)
        $novoUsuario = DB::table('usuarios')->where('id', $usuarioDb->id)->first();
        $sessao = (object)[
            'id'                   => $novoUsuario->id ?? null,
            'nome'                 => $novoUsuario->nome ?? ($novoUsuario->name ?? 'Usuário'),
            'cpf'                  => $novoUsuario->cpf ?? $sessCpf,
            'email'                => $novoUsuario->email ?? $sessMail,
            'role'                 => strtolower($novoUsuario->role ?? $novoUsuario->tipo ?? ''),
            'precisa_trocar_senha' => (bool)($novoUsuario->precisa_trocar_senha ?? false),
        ];
        Session::put('usuario', $sessao);

        return redirect()->route('dashboard')->with('success', 'Senha alterada com sucesso!');
    }

    /** Dashboard detalhado com filtros e datasets (preparado p/ relatórios) */
    public function dashboard(Request $request)
    {
        // Garantir login
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $usuario = Session::get('usuario');

        // se ainda precisa trocar a senha, NÃO deixa acessar o dashboard
        if (!empty($usuario->precisa_trocar_senha)) {
            return redirect()->route('me.senha.form');
        }

        $papel   = strtolower($usuario->role ?? $usuario->tipo ?? '');

        if (!in_array($papel, ['admin', 'funcionario'])) {
            abort(403, 'Acesso permitido apenas para administradores e funcionários.');
        }

        $isFuncionario = ($papel === 'funcionario');
        $meuFuncionarioId = null;

        // Se for funcionário, descobre o ID na tabela funcionarios usando o CPF
        if ($isFuncionario) {
            $cpfSess = isset($usuario->cpf) ? preg_replace('/\D+/', '', $usuario->cpf) : null;
            if ($cpfSess) {
                try {
                    $meuFuncionarioId = DB::table('funcionarios')
                        ->where('cpf', $cpfSess)
                        ->value('id');
                } catch (\Throwable $e) {
                    $meuFuncionarioId = null;
                }
            }
        }

        // Coluna de data na tabela agendas (auto-detecta 'inicio' ou 'data')
        $agendaTable   = 'agendas';
        $agendaDateCol = Schema::hasColumn($agendaTable, 'inicio')
            ? 'inicio'
            : (Schema::hasColumn($agendaTable, 'data') ? 'data' : 'inicio');

        // Filtros (GET)
        $periodoDias = (int)($request->get('periodo', 30));
        if (!in_array($periodoDias, [7,14,30,90])) $periodoDias = 30;

        // Para FUNCIONÁRIO, ignoramos filtro de funcionário da tela (ele só vê o dele)
        $funcionarioId = $isFuncionario ? null : $request->get('funcionario_id');
        $servicoId     = $request->get('servico_id');

        $inicio = now()->startOfDay()->subDays($periodoDias - 1);
        $fim    = now()->endOfDay();

        // =======================
        // KPIs
        // =======================
        $stats = [
            'total_funcionarios' => 0,
            'total_clientes'     => 0,
            'total_servicos'     => 0,
            'agendamentos_hoje'  => 0,
        ];

        if ($isFuncionario && $meuFuncionarioId) {
            // 🔒 Funcionário: só o que é dele
            try {
                $stats['total_funcionarios'] = 1; // só ele mesmo
            } catch (\Throwable $e) {}

            try {
                $stats['total_clientes'] = DB::table($agendaTable . ' as a')
                    ->where('a.funcionario_id', $meuFuncionarioId)
                    ->whereBetween("a.$agendaDateCol", [$inicio, $fim])
                    ->distinct('a.cliente_id')
                    ->count('a.cliente_id');
            } catch (\Throwable $e) {}

            try {
                $stats['total_servicos'] = DB::table($agendaTable . ' as a')
                    ->where('a.funcionario_id', $meuFuncionarioId)
                    ->whereBetween("a.$agendaDateCol", [$inicio, $fim])
                    ->distinct('a.servico_id')
                    ->count('a.servico_id');
            } catch (\Throwable $e) {}

            try {
                $stats['agendamentos_hoje'] = DB::table($agendaTable)
                    ->where('funcionario_id', $meuFuncionarioId)
                    ->whereDate($agendaDateCol, now()->toDateString())
                    ->count();
            } catch (\Throwable $e) {}

        } else {
            // 👑 Admin: visão geral
            try { $stats['total_funcionarios'] = DB::table('funcionarios')->count(); } catch (\Throwable $e) {}
            try { $stats['total_clientes']     = DB::table('clientes')->count(); } catch (\Throwable $e) {}
            try { $stats['total_servicos']     = DB::table('servicos')->count(); } catch (\Throwable $e) {}
            try {
                $stats['agendamentos_hoje'] = DB::table($agendaTable)
                    ->whereDate($agendaDateCol, now()->toDateString())
                    ->count();
            } catch (\Throwable $e) {}
        }

        // =======================
        // Filtros (combos select)
        // =======================
        $filtros = ['funcionarios'=>[], 'servicos'=>[]];

        // Funcionários: só aparece combo para admin
        if (!$isFuncionario) {
            try {
                $filtros['funcionarios'] = DB::table('funcionarios')
                    ->select('id','nome')
                    ->orderBy('nome')
                    ->get();
            } catch (\Throwable $e) {}
        }

        // Serviços: admin vê todos, funcionário pode ver só dele (se quiser)
        try {
            if ($isFuncionario && $meuFuncionarioId) {
                // pega serviços que esse funcionário atende (via pivot se existir)
                if (Schema::hasTable('funcionario_servico')) {
                    $filtros['servicos'] = DB::table('funcionario_servico as fs')
                        ->join('servicos as s', 's.id', '=', 'fs.servico_id')
                        ->where('fs.funcionario_id', $meuFuncionarioId)
                        ->select('s.id','s.nome')
                        ->orderBy('s.nome')
                        ->distinct()
                        ->get();
                } else {
                    // fallback: serviços a partir da agenda
                    $filtros['servicos'] = DB::table($agendaTable . ' as a')
                        ->join('servicos as s', 's.id', '=', 'a.servico_id')
                        ->where('a.funcionario_id', $meuFuncionarioId)
                        ->select('s.id','s.nome')
                        ->orderBy('s.nome')
                        ->distinct()
                        ->get();
                }
            } else {
                // admin: todos os serviços
                $filtros['servicos'] = DB::table('servicos')
                    ->select('id','nome')
                    ->orderBy('nome')
                    ->get();
            }
        } catch (\Throwable $e) {}

        // =======================
        // Query base para agendas
        // =======================
        $agendaBase = function() use (
            $agendaTable, $agendaDateCol,
            $inicio, $fim,
            $funcionarioId, $servicoId,
            $isFuncionario, $meuFuncionarioId
        ) {
            $q = DB::table($agendaTable . ' as a')
                ->leftJoin('clientes as c', 'c.id', '=', 'a.cliente_id')
                ->leftJoin('servicos as s', 's.id', '=', 'a.servico_id')
                ->leftJoin('funcionarios as f', 'f.id', '=', 'a.funcionario_id')
                ->whereBetween("a.$agendaDateCol", [$inicio, $fim]);

            // 🔒 se for funcionário, força filtro pelo próprio id
            if ($isFuncionario && $meuFuncionarioId) {
                $q->where('a.funcionario_id', $meuFuncionarioId);
            } else {
                if (!empty($funcionarioId)) {
                    $q->where('a.funcionario_id', $funcionarioId);
                }
            }

            if (!empty($servicoId)) {
                $q->where('a.servico_id', $servicoId);
            }

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

        // 2) Distribuição por status
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

        // 4) Top funcionários (para funcionário logado, na prática vai sair só ele)
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

        // 5) Próximos agendamentos (a partir de agora)
        $proximos = [];
        try {
            $q = DB::table($agendaTable . ' as a')
                ->leftJoin('clientes as c', 'c.id', '=', 'a.cliente_id')
                ->leftJoin('servicos as s', 's.id', '=', 'a.servico_id')
                ->leftJoin('funcionarios as f', 'f.id', '=', 'a.funcionario_id')
                ->where("a.$agendaDateCol", '>=', now());

            if ($isFuncionario && $meuFuncionarioId) {
                $q->where('a.funcionario_id', $meuFuncionarioId);
            }

            $proximos = $q
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
            $q2 = (clone $agendaBase)()
                ->where('a.status', 'cancelado')
                ->orderByDesc("a.$agendaDateCol")
                ->limit(8);

            $canceladosRecentes = $q2->get([
                DB::raw("DATE_FORMAT(a.$agendaDateCol, '%d/%m/%Y %H:%i') as data_hora"),
                'c.nome as cliente_nome',
                's.nome como servico_nome',
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
