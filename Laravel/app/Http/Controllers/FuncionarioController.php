<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use App\Models\Cargo;
use App\Models\Funcionario;
use App\Services\LogAuditoriaService;

class FuncionarioController extends Controller
{
    /**
     * Verifica se há sessão e se o usuário é ADMIN.
     * Retorna redirect/abort se não for admin.
     */
    private function requireAdmin()
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $usuario = Session::get('usuario');
        $papel   = strtolower($usuario->role ?? $usuario->tipo ?? '');

        if ($papel !== 'admin') {
            abort(403, 'Acesso permitido apenas para administradores.');
        }

        return null;
    }

    public function index()
    {
        if ($resp = $this->requireAdmin()) return $resp;

        // Junta funcionários com usuários pelo CPF para pegar tipo, tentativas e bloqueio
        $funcionarios = DB::table('funcionarios as f')
            ->leftJoin('usuarios as u', 'u.cpf', '=', 'f.cpf')
            ->select(
                'f.*',
                'u.tipo as tipo_usuario',
                'u.tentativas_falhas',
                'u.bloqueado as usuario_bloqueado', // << alias ajustado
                'u.precisa_trocar_senha'
            )
            ->orderBy('f.nome')
            ->paginate(9);

        return view('funcionarios.index', [
            'usuario'      => Session::get('usuario'),
            'funcionarios' => $funcionarios
        ]);
    }

    public function create()
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $cargos = Cargo::where('ativo', true)
            ->orderBy('nome')
            ->pluck('nome');

        return view('funcionarios.create', [
            'usuario'     => Session::get('usuario'),
            'funcionario' => null,
            'cargos'      => $cargos
        ]);
    }

    public function store(Request $request)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $request->validate([
            'nome'            => 'required|string|max:255',
            'cpf'             => 'required|string|max:14|unique:funcionarios,cpf|unique:usuarios,cpf',
            'email'           => 'required|email|unique:funcionarios,email',
            'cargo'           => 'required|exists:cargos,nome',
            'telefone'        => 'nullable|string|max:20',
            'data_nascimento' => [
                'nullable',
                'date',
                'regex:/^\d{4}-\d{2}-\d{2}$/',
            ],
            'ativo'           => 'nullable|boolean',

            // NOVOS CAMPOS
            'cep'             => 'nullable|string|max:9',
            'rua'             => 'nullable|string|max:255',
            'bairro'          => 'nullable|string|max:255',
            'numero'          => 'nullable|string|max:10',
        ], [
            'data_nascimento.regex' => 'Data de nascimento inválida.',
        ]);

        $cpf = preg_replace('/\D/', '', $request->cpf);

        // 1️⃣ Cria o funcionário
        DB::table('funcionarios')->insert([
            'nome'            => $request->nome,
            'cpf'             => $cpf,
            'email'           => $request->email,
            'cargo'           => $request->cargo,
            'telefone'        => $request->telefone,
            'data_nascimento' => $request->data_nascimento,
            'ativo'           => $request->boolean('ativo') ? 1 : 0,

            // NOVOS CAMPOS
            'cep'             => $request->cep,
            'rua'             => $request->rua,
            'bairro'          => $request->bairro,
            'numero'          => $request->numero,

            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // LOG create
        $funcModel = Funcionario::where('cpf', $cpf)->orderByDesc('id')->first();
        if ($funcModel) {
            LogAuditoriaService::registrarModel('create', $funcModel);
        }

        // 2️⃣ Cria também o usuário correspondente
        DB::table('usuarios')->insert([
            'nome'                 => $request->nome,
            'cpf'                  => $cpf,
            'senha'                => Hash::make('EsteticaPRO123'),
            'tipo'                 => 'funcionario',
            'precisa_trocar_senha' => true,  // força troca no primeiro acesso
            'tentativas_falhas'    => 0,
            'bloqueado'            => false,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        return redirect()->route('funcionarios.index')
            ->with('success', 'Funcionário e usuário criados com sucesso!');
    }

    public function edit($id)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $funcionario = DB::table('funcionarios')->where('id', $id)->first();

        if (!$funcionario) {
            abort(404, 'Funcionário não encontrado.');
        }

        $cargos = Cargo::where('ativo', true)
            ->orderBy('nome')
            ->pluck('nome');

        return view('funcionarios.create', [
            'usuario'     => Session::get('usuario'),
            'funcionario' => $funcionario,
            'cargos'      => $cargos
        ]);
    }

    public function update(Request $request, $id)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $request->validate([
            'nome'            => 'required|string|max:255',
            'cpf'             => 'required|string|max:14|unique:funcionarios,cpf,' . $id,
            'email'           => 'required|email|unique:funcionarios,email,' . $id,
            'cargo'           => 'required|exists:cargos,nome',
            'telefone'        => 'nullable|string|max:20',
            'data_nascimento' => [
                'nullable',
                'date',
                'regex:/^\d{4}-\d{2}-\d{2}$/',
            ],
            'ativo'           => 'nullable|boolean',

            // NOVOS CAMPOS
            'cep'             => 'nullable|string|max:9',
            'rua'             => 'nullable|string|max:255',
            'bairro'          => 'nullable|string|max:255',
            'numero'          => 'nullable|string|max:10',
        ], [
            'data_nascimento.regex' => 'Data de nascimento inválida.',
        ]);

        $cpf = preg_replace('/\D/', '', $request->cpf);

        $funcAntigo = DB::table('funcionarios')->where('id', $id)->first();
        if (!$funcAntigo) {
            abort(404, 'Funcionário não encontrado.');
        }

        // LOG snapshot antigo
        $funcModelAntigo = Funcionario::find($id);
        $dadosAntigos    = $funcModelAntigo ? $funcModelAntigo->toArray() : null;

        // 1️⃣ Atualiza o funcionário
        DB::table('funcionarios')->where('id', $id)->update([
            'nome'            => $request->nome,
            'cpf'             => $cpf,
            'email'           => $request->email,
            'cargo'           => $request->cargo,
            'telefone'        => $request->telefone,
            'data_nascimento' => $request->data_nascimento,
            'ativo'           => $request->boolean('ativo') ? 1 : 0,

            // NOVOS CAMPOS
            'cep'             => $request->cep,
            'rua'             => $request->rua,
            'bairro'          => $request->bairro,
            'numero'          => $request->numero,

            'updated_at'      => now(),
        ]);

        // LOG update
        $funcModelNovo = Funcionario::find($id);
        if ($funcModelNovo) {
            if ($dadosAntigos) {
                LogAuditoriaService::registrarModel('update', $funcModelNovo, $dadosAntigos);
            } else {
                LogAuditoriaService::registrarModel('update', $funcModelNovo);
            }
        }

        // 2️⃣ Atualiza também o usuário correspondente (nome e CPF)
        DB::table('usuarios')
            ->where('cpf', preg_replace('/\D/', '', $funcAntigo->cpf))
            ->update([
                'nome'       => $request->nome,
                'cpf'        => $cpf,
                'updated_at' => now(),
            ]);

        return redirect()->route('funcionarios.index')
            ->with('success', 'Funcionário atualizado com sucesso!');
    }

    public function destroy($id)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $func = DB::table('funcionarios')->where('id', $id)->first();

        if ($func) {
            // LOG delete
            $funcModel = Funcionario::find($id);
            if ($funcModel) {
                LogAuditoriaService::registrarDeleteModel($funcModel);
            }

            DB::table('funcionarios')->where('id', $id)->delete();

            DB::table('usuarios')
                ->where('cpf', preg_replace('/\D/', '', $func->cpf))
                ->delete();
        } else {
            abort(404, 'Funcionário não encontrado.');
        }

        return redirect()->route('funcionarios.index')
            ->with('success', 'Funcionário e usuário excluídos com sucesso!');
    }

    /**
     * Redefine a senha do usuário vinculado ao funcionário (por ID).
     * Rota: POST /funcionarios/{funcionario}/reset-senha
     */
    public function resetSenha(Request $request, $funcionarioId)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $request->validate([
            'nova_senha' => [
                'required',
                'string',
                'min:8',
                'max:72',
                // pelo menos 1 minúscula, 1 maiúscula, 1 número e 1 símbolo
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,72}$/',
            ],
        ], [
            'nova_senha.required' => 'Informe a nova senha.',
            'nova_senha.min'      => 'A senha deve ter no mínimo :min caracteres.',
            'nova_senha.regex'    => 'A senha deve ter letras maiúsculas, minúsculas, números e caracteres especiais.',
        ]);

        $func = DB::table('funcionarios')->where('id', $funcionarioId)->first();
        if (!$func) {
            abort(404, 'Funcionário não encontrado.');
        }

        $funcionarioModel = Funcionario::find($funcionarioId);

        $cpfFuncionario = preg_replace('/\D/', '', $func->cpf ?? '');
        $usuario        = null;

        if (!empty($cpfFuncionario)) {
            $usuario = DB::table('usuarios')->where('cpf', $cpfFuncionario)->first();
        }

        if (!$usuario && !empty($func->email)) {
            try {
                $usuario = DB::table('usuarios')->where('email', $func->email)->first();
            } catch (\Throwable $e) {
                $usuario = null;
            }
        }

        if (!$usuario) {
            return back()->with('error', 'Usuário correspondente não encontrado na tabela de usuários.');
        }

        DB::table('usuarios')
            ->where('id', $usuario->id)
            ->update([
                'senha'                => Hash::make($request->input('nova_senha')),
                'precisa_trocar_senha' => true,
                'tentativas_falhas'    => 0,
                'bloqueado'            => false,
                'updated_at'           => now(),
            ]);

        if ($funcionarioModel) {
            LogAuditoriaService::registrarModel('reset_senha', $funcionarioModel);
        }

        return back()->with('success', 'Senha redefinida com sucesso!');
    }

    /**
     * Redefine a senha do usuário vinculado usando CPF (menu botão direito + popup).
     * Rota: POST /funcionarios/reset-senha-por-cpf
     */
    public function resetSenhaPorCpf(Request $request)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $request->validate([
            'cpf'        => 'required|string',
            'nova_senha' => [
                'required',
                'string',
                'min:8',
                'max:72',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,72}$/',
            ],
        ], [
            'nova_senha.required' => 'Informe a nova senha.',
            'nova_senha.min'      => 'A senha deve ter no mínimo :min caracteres.',
            'nova_senha.regex'    => 'A senha deve ter letras maiúsculas, minúsculas, números e caracteres especiais.',
        ]);

        $cpf     = preg_replace('/\D/', '', $request->cpf);
        $usuario = DB::table('usuarios')->where('cpf', $cpf)->first();

        if (!$usuario) {
            return back()->with('error', 'Nenhum usuário encontrado para o CPF informado.');
        }

        DB::table('usuarios')
            ->where('id', $usuario->id)
            ->update([
                'senha'                => Hash::make($request->input('nova_senha')),
                'precisa_trocar_senha' => true,
                'tentativas_falhas'    => 0,
                'bloqueado'            => false,
                'updated_at'           => now(),
            ]);

        $funcionarioModel = Funcionario::where('cpf', $cpf)->first();
        if ($funcionarioModel) {
            LogAuditoriaService::registrarModel('reset_senha_rapida', $funcionarioModel);
        }

        return back()->with('success', 'Senha redefinida para o usuário vinculado a este CPF.');
    }

    /**
     * Altera o tipo do usuário vinculado usando CPF (menu botão direito + popup).
     * Rota: POST /funcionarios/alterar-tipo-por-cpf
     */
    public function alterarTipoPorCpf(Request $request)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $request->validate([
            'cpf'  => 'required|string',
            'tipo' => 'required|string|in:admin,funcionario',
        ], [
            'tipo.required' => 'Informe o novo tipo do usuário.',
            'tipo.in'       => 'Tipo inválido. Use admin ou funcionario.',
        ]);

        $cpf      = preg_replace('/\D/', '', $request->cpf);
        $novoTipo = strtolower(trim($request->tipo));

        $usuario = DB::table('usuarios')->where('cpf', $cpf)->first();

        if (!$usuario) {
            return back()->with('error', 'Nenhum usuário encontrado para o CPF informado.');
        }

        $tipoAntigo = $usuario->tipo ?? null;

        DB::table('usuarios')
            ->where('id', $usuario->id)
            ->update([
                'tipo'       => $novoTipo,
                'updated_at' => now(),
            ]);

        $funcionarioModel = Funcionario::where('cpf', $cpf)->first();
        if ($funcionarioModel) {
            LogAuditoriaService::registrarModel('alterar_tipo_usuario', $funcionarioModel, [
                'tipo_antigo' => $tipoAntigo,
                'tipo_novo'   => $novoTipo,
            ]);
        }

        return back()->with('success', 'Tipo do usuário atualizado para "' . $novoTipo . '" com sucesso!');
    }

    /**
     * Desbloqueia a conta do usuário vinculado (por CPF).
     * Rota: POST /funcionarios/desbloquear-conta
     */
    public function desbloquearConta(Request $request)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $request->validate([
            'cpf' => 'required|string',
        ]);

        $cpf     = preg_replace('/\D/', '', $request->cpf);
        $usuario = DB::table('usuarios')->where('cpf', $cpf)->first();

        if (!$usuario) {
            return back()->with('error', 'Nenhum usuário encontrado para o CPF informado.');
        }

        DB::table('usuarios')
            ->where('id', $usuario->id)
            ->update([
                'tentativas_falhas' => 0,
                'bloqueado'         => false,
                'updated_at'        => now(),
            ]);

        $funcionarioModel = Funcionario::where('cpf', $cpf)->first();
        if ($funcionarioModel) {
            LogAuditoriaService::registrarModel('desbloquear_conta', $funcionarioModel);
        }

        return back()->with('success', 'Conta desbloqueada com sucesso!');
    }
}
