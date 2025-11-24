<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Servico;
use App\Services\LogAuditoriaService;

class ServicoController extends Controller
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

        // 🔹 Paginação oficial: 9 por página
        $servicos = DB::table('servicos')
            ->orderBy('nome')
            ->paginate(9);

        return view('servicos.index', [
            'usuario'  => Session::get('usuario'),
            'servicos' => $servicos,
        ]);
    }

    public function create()
    {
        if ($r = $this->requireAuth()) return $r;

        // Apenas funcionários ativos
        $funcionarios = DB::table('funcionarios')
            ->select('id', 'nome')          // só o que a tela precisa
            ->where('ativo', 1)
            ->orderBy('nome')
            ->get();

        return view('servicos.create', [
            'usuario'                  => Session::get('usuario'),
            'servico'                  => null,
            'funcionarios'             => $funcionarios,
            'vinculados'               => [],
            'funcionariosSelecionados' => [],
        ]);
    }

    public function store(Request $request)
    {
        if ($r = $this->requireAuth()) return $r;

        $request->validate([
            'nome'             => 'required|string|max:255',
            'valor'            => 'required',
            'comissao_percent' => 'required',
            'duracao_minutos'  => 'required|integer|min:5|max:1440',
            'descricao'        => 'nullable|string|max:5000',
            'ativo'            => 'nullable|boolean',
            'funcionarios'     => 'array',
        ]);

        $valor    = $this->toDecimal($request->valor);
        $comissao = $this->toDecimal($request->comissao_percent);

        // 1️⃣ Cria o serviço
        $servicoId = DB::table('servicos')->insertGetId([
            'nome'             => $request->nome,
            'valor'            => $valor,
            'comissao_percent' => $comissao,
            'duracao_minutos'  => (int) $request->duracao_minutos,
            'descricao'        => $request->descricao,
            'ativo'            => $request->boolean('ativo') ? 1 : 0,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // 2️⃣ Vincula funcionários
        $this->syncFuncionarios($servicoId, $request->input('funcionarios', []));

        // 🔐 LOG: registra create usando o MODEL
        $servicoModel = Servico::find($servicoId);
        if ($servicoModel) {
            LogAuditoriaService::registrarModel('create', $servicoModel);
        }

        return redirect()->route('servicos.index')
            ->with('success', 'Serviço cadastrado com sucesso!');
    }

    public function edit($id)
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $servico = DB::table('servicos')->where('id', $id)->first();
        if (!$servico) {
            return redirect()->route('servicos.index')->with('error', 'Serviço não encontrado.');
        }

        // Apenas funcionários ativos
        $funcionarios = DB::table('funcionarios')
            ->select('id', 'nome')
            ->where('ativo', 1)
            ->orderBy('nome')
            ->get();

        $vinculados = DB::table('funcionario_servico')
            ->where('servico_id', $id)
            ->pluck('funcionario_id')
            ->toArray();

        return view('servicos.create', [
            'usuario'                  => Session::get('usuario'),
            'servico'                  => $servico,
            'funcionarios'             => $funcionarios,
            'vinculados'               => $vinculados,
            'funcionariosSelecionados' => $vinculados,
        ]);
    }

    public function update(Request $request, $id)
    {
        if ($r = $this->requireAuth()) return $r;

        $request->validate([
            'nome'             => 'required|string|max:255',
            'valor'            => 'required',
            'comissao_percent' => 'required',
            'duracao_minutos'  => 'required|integer|min:5|max:1440',
            'descricao'        => 'nullable|string|max:5000',
            'ativo'            => 'nullable|boolean',
            'funcionarios'     => 'array',
        ]);

        $valor    = $this->toDecimal($request->valor);
        $comissao = $this->toDecimal($request->comissao_percent);

        // 🔐 Snapshot ANTES (para log)
        $servicoAntigo = Servico::find($id);
        $dadosAntigos  = $servicoAntigo ? $servicoAntigo->toArray() : null;

        // 1️⃣ Atualiza o serviço
        DB::table('servicos')->where('id', $id)->update([
            'nome'             => $request->nome,
            'valor'            => $valor,
            'comissao_percent' => $comissao,
            'duracao_minutos'  => (int) $request->duracao_minutos,
            'descricao'        => $request->descricao,
            'ativo'            => $request->boolean('ativo') ? 1 : 0,
            'updated_at'       => now(),
        ]);

        // 2️⃣ Atualiza vínculos com funcionários
        $this->syncFuncionarios($id, $request->input('funcionarios', []));

        // 🔐 Snapshot DEPOIS + LOG
        $servicoNovo = Servico::find($id);
        if ($servicoNovo) {
            if ($dadosAntigos) {
                LogAuditoriaService::registrarModel('update', $servicoNovo, $dadosAntigos);
            } else {
                LogAuditoriaService::registrarModel('update', $servicoNovo);
            }
        }

        return redirect()->route('servicos.index')
            ->with('success', 'Serviço atualizado com sucesso!');
    }

    public function destroy($id)
    {
        if ($r = $this->requireAuth()) return $r;

        // 🔐 LOG: pega model antes de excluir
        $servicoModel = Servico::find($id);
        if ($servicoModel) {
            LogAuditoriaService::registrarDeleteModel($servicoModel);
        }

        DB::table('funcionario_servico')->where('servico_id', $id)->delete();
        DB::table('servicos')->where('id', $id)->delete();

        return redirect()->route('servicos.index')
            ->with('success', 'Serviço excluído com sucesso!');
    }

    private function syncFuncionarios(int $servicoId, array $funcionariosIds): void
    {
        DB::table('funcionario_servico')->where('servico_id', $servicoId)->delete();

        if (empty($funcionariosIds)) return;

        $now  = now();
        $rows = [];
        foreach ($funcionariosIds as $fid) {
            $rows[] = [
                'funcionario_id' => (int) $fid,
                'servico_id'     => (int) $servicoId,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }
        DB::table('funcionario_servico')->insert($rows);
    }

    private function toDecimal($value): string
    {
        $v = trim((string) $value);
        // "1.234,56" -> "1234.56"
        $v = str_replace('.', '', $v);
        $v = str_replace(',', '.', $v);
        return number_format((float) $v, 2, '.', '');
    }
}
