<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use App\Models\Cargo;

class FuncionarioController extends Controller
{
    /**
     * Verifica se há sessão e se o usuário é ADMIN.
     * Retorna redirect/abort se não for admin.
     */
    private function requireAdmin()
    {
        // Se não estiver logado, redireciona para login
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $usuario = Session::get('usuario');
        $papel = strtolower($usuario->role ?? $usuario->tipo ?? '');

        // Se não for admin, bloqueia
        if ($papel !== 'admin') {
            abort(403, 'Acesso permitido apenas para administradores.');
        }

        return null;
    }

    public function index()
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $funcionarios = DB::table('funcionarios')->get();

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
            'nome'     => 'required|string|max:255',
            'cpf'      => 'required|string|max:14|unique:funcionarios,cpf|unique:usuarios,cpf',
            'email'    => 'required|email|unique:funcionarios,email',
            'cargo'    => 'required|exists:cargos,nome',
            'telefone' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'ativo'    => 'nullable|boolean'
        ]);

        $cpf = preg_replace('/\D/', '', $request->cpf);

        // 1️⃣ Cria o funcionário
        DB::table('funcionarios')->insert([
            'nome'       => $request->nome,
            'cpf'        => $cpf,
            'email'      => $request->email,
            'cargo'      => $request->cargo,
            'telefone'   => $request->telefone,
            'endereco'   => $request->endereco,
            'ativo'      => $request->boolean('ativo') ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2️⃣ Cria também o usuário correspondente
        DB::table('usuarios')->insert([
            'nome'       => $request->nome,
            'cpf'        => $cpf,
            'senha'      => Hash::make('EsteticaPRO123'),
            'tipo'       => 'funcionario',
            'created_at' => now(),
            'updated_at' => now(),
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
            'nome'     => 'required|string|max:255',
            'cpf'      => 'required|string|max:14|unique:funcionarios,cpf,' . $id,
            'email'    => 'required|email|unique:funcionarios,email,' . $id,
            'cargo'    => 'required|exists:cargos,nome',
            'telefone' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'ativo'    => 'nullable|boolean'
        ]);

        $cpf = preg_replace('/\D/', '', $request->cpf);

        // funcionário antigo (para saber o CPF anterior)
        $funcAntigo = DB::table('funcionarios')->where('id', $id)->first();

        if (!$funcAntigo) {
            abort(404, 'Funcionário não encontrado.');
        }

        // 1️⃣ Atualiza o funcionário
        DB::table('funcionarios')->where('id', $id)->update([
            'nome'       => $request->nome,
            'cpf'        => $cpf,
            'email'      => $request->email,
            'cargo'      => $request->cargo,
            'telefone'   => $request->telefone,
            'endereco'   => $request->endereco,
            'ativo'      => $request->boolean('ativo') ? 1 : 0,
            'updated_at' => now(),
        ]);

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
            // exclui o funcionário
            DB::table('funcionarios')->where('id', $id)->delete();

            // exclui também o usuário com mesmo CPF
            DB::table('usuarios')->where('cpf', preg_replace('/\D/', '', $func->cpf))->delete();
        } else {
            abort(404, 'Funcionário não encontrado.');
        }

        return redirect()->route('funcionarios.index')
            ->with('success', 'Funcionário e usuário excluídos com sucesso!');
    }

    /**
     * Redefine a senha do usuário vinculado ao funcionário.
     * Localiza por CPF (preferencial) e, se não achar, tenta por e-mail.
     * Rota: POST /funcionarios/{funcionario}/reset-senha
     */
    public function resetSenha(Request $request, $funcionarioId)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $request->validate([
            'nova_senha' => 'required|string|min:6|max:72',
        ], [
            'nova_senha.required' => 'Informe a nova senha.',
            'nova_senha.min'      => 'A senha deve ter no mínimo :min caracteres.',
            'nova_senha.max'      => 'A senha deve ter no máximo :max caracteres.',
        ]);

        // 1) Buscar funcionário
        $func = DB::table('funcionarios')->where('id', $funcionarioId)->first();
        if (!$func) {
            abort(404, 'Funcionário não encontrado.');
        }

        // 2) Tentar localizar o usuário pelo CPF do funcionário
        $cpfFuncionario = preg_replace('/\D/', '', $func->cpf ?? '');
        $usuario = null;

        if (!empty($cpfFuncionario)) {
            $usuario = DB::table('usuarios')->where('cpf', $cpfFuncionario)->first();
        }

        // 3) (fallback) Se não encontrar por CPF, tentar por e-mail (caso exista coluna email em usuarios)
        if (!$usuario && !empty($func->email)) {
            try {
                $usuario = DB::table('usuarios')->where('email', $func->email)->first();
            } catch (\Throwable $e) {
                // se a tabela usuarios não tiver email, ignorar
                $usuario = null;
            }
        }

        if (!$usuario) {
            return back()->with('error', 'Usuário correspondente não encontrado na tabela de usuários.');
        }

        // 4) Atualizar a senha (sempre criptografada)
        DB::table('usuarios')
            ->where('id', $usuario->id)
            ->update([
                'senha'      => Hash::make($request->input('nova_senha')),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Senha redefinida com sucesso!');
    }
}
