<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ServicoController extends Controller
{
    public function index()
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $servicos = DB::table('servicos')->orderBy('nome')->get();

        return view('servicos.index', [
            'usuario'  => Session::get('usuario'),
            'servicos' => $servicos,
        ]);
    }

    public function create()
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        // Apenas funcionários ativos
        $funcionarios = DB::table('funcionarios')
            ->select('id', 'nome')          // só o que a tela precisa
            ->where('ativo', 1)
            ->orderBy('nome')
            ->get();

        return view('servicos.create', [
            'usuario'                 => Session::get('usuario'),
            'servico'                 => null,
            'funcionarios'            => $funcionarios,
            'vinculados'              => [],      // mantém nome antigo
            'funcionariosSelecionados'=> [],      // nome novo mais intuitivo
        ]);
    }

    public function store(Request $request)
    {
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

        $this->syncFuncionarios($servicoId, $request->input('funcionarios', []));

        return redirect()->route('servicos.index')->with('success', 'Serviço cadastrado com sucesso!');
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
            'usuario'                 => Session::get('usuario'),
            'servico'                 => $servico,
            'funcionarios'            => $funcionarios,
            'vinculados'              => $vinculados,
            'funcionariosSelecionados'=> $vinculados, // mesma coisa, dois nomes
        ]);
    }

    public function update(Request $request, $id)
    {
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

        DB::table('servicos')->where('id', $id)->update([
            'nome'             => $request->nome,
            'valor'            => $valor,
            'comissao_percent' => $comissao,
            'duracao_minutos'  => (int) $request->duracao_minutos,
            'descricao'        => $request->descricao,
            'ativo'            => $request->boolean('ativo') ? 1 : 0,
            'updated_at'       => now(),
        ]);

        $this->syncFuncionarios($id, $request->input('funcionarios', []));

        return redirect()->route('servicos.index')->with('success', 'Serviço atualizado com sucesso!');
    }

    public function destroy($id)
    {
        DB::table('funcionario_servico')->where('servico_id', $id)->delete();
        DB::table('servicos')->where('id', $id)->delete();

        return redirect()->route('servicos.index')->with('success', 'Serviço excluído com sucesso!');
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
