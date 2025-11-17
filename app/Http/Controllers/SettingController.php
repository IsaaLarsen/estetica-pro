<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\AgendaExpedienteExcecao;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    /**
     * Tela de configurações da agenda:
     * - expediente padrão (início/fim)
     * - dias especiais de expediente (exceções por funcionário)
     */
    public function edit()
    {
        // Horário padrão já salvo (ou valores default)
        $inicio = Setting::get('expediente_inicio', '08:00');
        $fim    = Setting::get('expediente_fim',    '18:00');

        // Funcionários ativos para o multi-select
        $funcionarios = DB::table('funcionarios')
            ->where('ativo', 1)
            ->orderBy('nome')
            ->get();

        // Todas as exceções com funcionários vinculados
        $excecoes = AgendaExpedienteExcecao::with('funcionarios')
            ->orderBy('data', 'asc')
            ->get();

        return view('settings.edit', compact('inicio', 'fim', 'excecoes', 'funcionarios'));
    }

    /**
     * Salva o expediente padrão (início/fim)
     */
    public function update(Request $request)
    {
        $request->validate([
            'expediente_inicio' => 'required|date_format:H:i',
            'expediente_fim'    => 'required|date_format:H:i',
        ]);

        $inicio = $request->expediente_inicio;
        $fim    = $request->expediente_fim;

        if ($inicio >= $fim) {
            return back()
                ->withErrors(['expediente_fim' => 'O fim do expediente deve ser após o início.'])
                ->withInput();
        }

        Setting::set('expediente_inicio', $inicio);
        Setting::set('expediente_fim',    $fim);

        return redirect()
            ->route('settings.edit')
            ->with('success', 'Configurações de expediente atualizadas com sucesso!');
    }

    /**
     * Cria uma nova exceção de expediente (dia especial)
     * com opção de aplicar a todos ou só alguns funcionários.
     *
     * Rota: settings.excecoes.store
     */
    public function storeExcecao(Request $request)
    {
        $request->validate([
            'data'          => 'required|date',
            'inicio'        => 'required|date_format:H:i',
            'fim'           => 'required|date_format:H:i',
            'aplicar_todos' => 'nullable|boolean',
            'funcionarios'  => 'array',
            'funcionarios.*'=> 'exists:funcionarios,id',
        ]);

        $data          = $request->data;
        $inicio        = $request->inicio;
        $fim           = $request->fim;
        $aplicarTodos  = $request->boolean('aplicar_todos');
        $funcionarios  = $request->funcionarios ?? [];

        if ($inicio >= $fim) {
            return back()
                ->withErrors(['fim' => 'O fim do expediente especial deve ser após o início.'])
                ->withInput();
        }

        // Se NÃO aplicar a todos, precisa ter pelo menos 1 funcionário
        if (!$aplicarTodos && empty($funcionarios)) {
            return back()
                ->withErrors(['funcionarios' => 'Selecione pelo menos um profissional ou marque "Aplicar a todos".'])
                ->withInput();
        }

        // opcional: impedir duplicar dia com mesmo escopo (aqui estou impedindo qualquer dia repetido)
        $jaExiste = AgendaExpedienteExcecao::whereDate('data', $data)->exists();
        if ($jaExiste) {
            return back()
                ->withErrors(['data' => 'Já existe um expediente especial cadastrado para esta data.'])
                ->withInput();
        }

        $excecao = AgendaExpedienteExcecao::create([
            'data'          => $data,
            'inicio'        => $inicio . ':00', // se tua coluna é time, isso funciona
            'fim'           => $fim . ':00',
            'aplicar_todos' => $aplicarTodos,
        ]);

        if (!$aplicarTodos) {
            $excecao->funcionarios()->sync($funcionarios);
        }

        return redirect()
            ->route('settings.edit')
            ->with('success', 'Dia especial de expediente cadastrado com sucesso!');
    }

    /**
     * Remove uma exceção de expediente (dia especial)
     * Rota: settings.excecoes.destroy
     */
    public function destroyExcecao($id)
    {
        $excecao = AgendaExpedienteExcecao::findOrFail($id);
        $excecao->funcionarios()->detach();
        $excecao->delete();

        return redirect()
            ->route('settings.edit')
            ->with('success', 'Dia especial de expediente removido com sucesso!');
    }
}
