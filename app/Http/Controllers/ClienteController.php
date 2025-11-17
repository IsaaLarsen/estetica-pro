<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;

class ClienteController extends Controller
{
    /**
     * Garante que há usuário em sessão.
     * Retorna redirect para login quando não estiver logado.
     */
    private function requireAuth()
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }
        return null;
    }

    public function index()
    {
        if ($r = $this->requireAuth()) return $r;

        $clientes = DB::table('clientes')->orderBy('nome')->get();

        return view('clientes.index', [
            'usuario'  => Session::get('usuario'),
            'clientes' => $clientes,
        ]);
    }

    public function create()
    {
        if ($r = $this->requireAuth()) return $r;

        return view('clientes.create', [
            'usuario' => Session::get('usuario'),
            'cliente' => null,
        ]);
    }

    public function store(Request $request)
    {
        if ($r = $this->requireAuth()) return $r;

        $request->validate([
            'nome'            => 'required|string|max:255',
            'cpf'             => 'required|string|max:18|unique:clientes,cpf',
            'telefone'        => 'nullable|string|max:30',
            'email'           => 'nullable|email|max:255|unique:clientes,email',
            'endereco'        => 'nullable|string|max:255',
            'data_nascimento' => 'nullable|date',
            'senha'           => 'required|string|min:6',
            'ativo'           => 'nullable|boolean',
        ]);

        $cpf      = preg_replace('/\D+/', '', (string) $request->cpf);
        $telefone = $request->telefone ? preg_replace('/\D+/', '', (string) $request->telefone) : null;

        DB::table('clientes')->insert([
            'nome'            => $request->nome,
            'cpf'             => $cpf,
            'telefone'        => $telefone,
            'email'           => $request->email,
            'endereco'        => $request->endereco,
            'data_nascimento' => $request->data_nascimento,
            'senha'           => Hash::make($request->senha),
            'ativo'           => $request->boolean('ativo') ? 1 : 0,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return redirect()->route('clientes.index')->with('success', 'Cliente cadastrado com sucesso!');
    }
    public function edit($id)
    {
        if ($r = $this->requireAuth()) return $r;

        $cliente = DB::table('clientes')->where('id', $id)->first();

        if (!$cliente) {
            return redirect()->route('clientes.index')->with('error', 'Cliente não encontrado.');
        }

        return view('clientes.create', [
            'usuario' => Session::get('usuario'),
            'cliente' => $cliente,
        ]);
    }

    public function update(Request $request, $id)
    {
        if ($r = $this->requireAuth()) return $r;

        $request->validate([
            'nome'            => 'required|string|max:255',
            'cpf'             => 'required|string|max:18|unique:clientes,cpf,' . $id,
            'telefone'        => 'nullable|string|max:30',
            'email'           => 'nullable|email|max:255|unique:clientes,email,' . $id,
            'endereco'        => 'nullable|string|max:255',
            'data_nascimento' => 'nullable|date',
            'senha'           => 'nullable|string|min:6', // opcional ao editar
            'ativo'           => 'nullable|boolean',
            // novo: troca de senha opcional
            'senha'           => 'nullable|string|min:6',
        ]);

        $cpf      = preg_replace('/\D+/', '', (string) $request->cpf);
        $telefone = $request->telefone ? preg_replace('/\D+/', '', (string) $request->telefone) : null;

        $dados = [
            'nome'            => $request->nome,
            'cpf'             => $cpf,
            'telefone'        => $telefone,
            'email'           => $request->email,
            'endereco'        => $request->endereco,
            'data_nascimento' => $request->data_nascimento,
            'ativo'           => $request->boolean('ativo') ? 1 : 0,
            'updated_at'      => now(),
        ];

        if (!empty($request->senha)) {
            $dados['senha'] = Hash::make($request->senha);
        }

        DB::table('clientes')->where('id', $id)->update($dados);

        return redirect()->route('clientes.index')->with('success', 'Cliente atualizado com sucesso!');
    }
    public function destroy($id)
    {
        if ($r = $this->requireAuth()) return $r;

        DB::table('clientes')->where('id', $id)->delete();

        return redirect()->route('clientes.index')->with('success', 'Cliente excluído com sucesso!');
    }

    /**
     * Busca de clientes para Select2 (AJAX) — rota: clientes.search (GET)
     * Request: q (string), page (int)
     * Response: { data: [{id,text}], more: bool }
     */
    public function search(Request $request)
    {
        // Proteção extra (além do middleware)
        if (!Session::has('usuario')) {
            return response()->json(['data' => [], 'more' => false], 401);
        }

        $q       = trim((string) $request->get('q', ''));
        $page    = max(1, (int) $request->get('page', 1));
        $perPage = 20;

        // Normalizações para ranking/telefone/CPF
        $qDigits   = preg_replace('/\D+/', '', $q);
        $likeAny   = '%' . $q . '%';
        $likeStart = $q . '%';
        $likeWord  = '% ' . $q . '%'; // começo de palavra no meio

        $query = DB::table('clientes')
            ->select('id', 'nome', 'email', 'telefone')
            ->when($q !== '', function ($sql) use ($likeAny, $qDigits) {
                $sql->where(function ($w) use ($likeAny, $qDigits) {
                    $w->where('nome', 'like', $likeAny)
                      ->orWhere('email', 'like', $likeAny)
                      ->orWhere('telefone', 'like', $likeAny)
                      ->orWhere('cpf', 'like', '%' . $qDigits . '%');
                });
            });

        // 🔽 Ordenação por relevância
        $orderSql = "
          CASE
            WHEN nome = ?        THEN 0  -- match exato
            WHEN nome LIKE ?     THEN 1  -- começa com
            WHEN nome LIKE ?     THEN 2  -- começo de palavra no meio
            WHEN nome LIKE ?     THEN 3  -- contém
            WHEN email LIKE ?    THEN 4  -- email começa com
            WHEN telefone LIKE ? THEN 5  -- telefone começa com
            ELSE 6
          END, nome ASC
        ";

        $bindings = [
            $q,            // nome = ?
            $likeStart,    // nome LIKE 'q%'
            $likeWord,     // nome LIKE '% q%'
            $likeAny,      // nome LIKE '%q%'
            $likeStart,    // email LIKE 'q%'
            $likeStart,    // telefone LIKE 'q%'
        ];

        $query->orderByRaw($orderSql, $bindings);

        $total   = (clone $query)->count();
        $results = $query->forPage($page, $perPage)->get();

        // Monta o "text" exibido no Select2 (Nome — telefone/email)
        $data = $results->map(function ($c) {
            $tag = $c->nome;
            $extras = [];
            if (!empty($c->telefone)) $extras[] = $c->telefone;
            if (!empty($c->email))    $extras[] = $c->email;
            if (!empty($extras)) $tag .= ' — ' . implode(' · ', $extras);
            return ['id' => $c->id, 'text' => $tag];
        });

        return response()->json([
            'data' => $data,
            'more' => ($page * $perPage) < $total,
        ]);
    }
}
