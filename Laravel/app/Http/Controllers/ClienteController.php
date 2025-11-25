<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use App\Models\Cliente;
use App\Services\LogAuditoriaService;

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

        // 🔹 Paginação oficial: 9 clientes por página
        $clientes = DB::table('clientes')
            ->orderBy('nome')
            ->paginate(9);

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
            'data_nascimento' => 'nullable|date',
            'senha'           => 'required|string|min:6',
            'ativo'           => 'nullable|boolean',

            // NOVOS CAMPOS
            'cep'             => 'nullable|string|max:9',
            'rua'             => 'nullable|string|max:255',
            'bairro'          => 'nullable|string|max:255',
            'numero'          => 'nullable|string|max:10',
        ]);

        $cpf      = preg_replace('/\D+/', '', (string) $request->cpf);
        $telefone = $request->telefone ? preg_replace('/\D+/', '', (string) $request->telefone) : null;

        // 1️⃣ Insere via DB
        DB::table('clientes')->insert([
            'nome'            => $request->nome,
            'cpf'             => $cpf,
            'telefone'        => $telefone,
            'email'           => $request->email,
            'data_nascimento' => $request->data_nascimento,
            'senha'           => Hash::make($request->senha),
            'ativo'           => $request->boolean('ativo') ? 1 : 0,

            // NOVOS CAMPOS
            'cep'             => $request->cep,
            'rua'             => $request->rua,
            'bairro'          => $request->bairro,
            'numero'          => $request->numero,

            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // 🔐 LOG: registra create usando o MODEL
        $clienteModel = Cliente::where('cpf', $cpf)->orderByDesc('id')->first();
        if ($clienteModel) {
            LogAuditoriaService::registrarModel('create', $clienteModel);
        }

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente cadastrado com sucesso!');
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
            'data_nascimento' => 'nullable|date',
            'senha'           => 'nullable|string|min:6', // opcional ao editar
            'ativo'           => 'nullable|boolean',

            // NOVOS CAMPOS
            'cep'             => 'nullable|string|max:9',
            'rua'             => 'nullable|string|max:255',
            'bairro'          => 'nullable|string|max:255',
            'numero'          => 'nullable|string|max:10',
        ]);

        $cpf      = preg_replace('/\D+/', '', (string) $request->cpf);
        $telefone = $request->telefone ? preg_replace('/\D+/', '', (string) $request->telefone) : null;

        // 🔐 Snapshot ANTES (para log)
        $clienteAntigo = Cliente::find($id);
        $dadosAntigos  = $clienteAntigo ? $clienteAntigo->toArray() : null;

        $dados = [
            'nome'            => $request->nome,
            'cpf'             => $cpf,
            'telefone'        => $telefone,
            'email'           => $request->email,
            'data_nascimento' => $request->data_nascimento,
            'ativo'           => $request->boolean('ativo') ? 1 : 0,

            // NOVOS CAMPOS
            'cep'             => $request->cep,
            'rua'             => $request->rua,
            'bairro'          => $request->bairro,
            'numero'          => $request->numero,

            'updated_at'      => now(),
        ];

        if (!empty($request->senha)) {
            $dados['senha'] = Hash::make($request->senha);
        }

        DB::table('clientes')->where('id', $id)->update($dados);

        // 🔐 Snapshot DEPOIS + LOG
        $clienteNovo = Cliente::find($id);
        if ($clienteNovo) {
            if ($dadosAntigos) {
                LogAuditoriaService::registrarModel('update', $clienteNovo, $dadosAntigos);
            } else {
                LogAuditoriaService::registrarModel('update', $clienteNovo);
            }
        }

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy($id)
    {
        if ($r = $this->requireAuth()) return $r;

        // 🔐 LOG: pega model antes de excluir
        $cliente = Cliente::find($id);
        if ($cliente) {
            LogAuditoriaService::registrarDeleteModel($cliente);
        }

        DB::table('clientes')->where('id', $id)->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente excluído com sucesso!');
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
