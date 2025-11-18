<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\AgendaExpedienteExcecao;
use App\Models\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class SettingController extends Controller
{
    /**
     * Tela de configurações da agenda
     */
    public function edit()
    {
        $inicio = Setting::get('expediente_inicio', '08:00');
        $fim    = Setting::get('expediente_fim', '18:00');

        $funcionarios = DB::table('funcionarios')
            ->where('ativo', 1)
            ->orderBy('nome')
            ->get();

        $excecoes = AgendaExpedienteExcecao::with('funcionarios')
            ->orderBy('data', 'asc')
            ->get();

        return view('settings.edit', compact('inicio', 'fim', 'excecoes', 'funcionarios'));
    }

    /**
     * Atualiza expediente padrão
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

        // Snapshot antigo para o log
        $antigos = [
            'expediente_inicio' => Setting::get('expediente_inicio'),
            'expediente_fim'    => Setting::get('expediente_fim'),
        ];

        Setting::set('expediente_inicio', $inicio);
        Setting::set('expediente_fim',    $fim);

        // LOG
        $this->registrarLogSettings(
            'settings_update',
            [
                'expediente_inicio' => $inicio,
                'expediente_fim'    => $fim
            ],
            $antigos
        );

        return redirect()
            ->route('settings.edit')
            ->with('success', 'Configurações de expediente atualizadas com sucesso!');
    }

    /**
     * Criar exceção de expediente
     */
    public function storeExcecao(Request $request)
    {
        $request->validate([
            'data'          => 'required|date',
            'inicio'        => 'required|date_format:H:i',
            'fim'           => 'required|date_format:H:i',
            'funcionarios'  => 'array',
            'funcionarios.*'=> 'exists:funcionarios,id',
            'aplicar_todos' => 'nullable|boolean', // USADO SÓ PARA LÓGICA, NÃO SALVO!
        ]);

        $data         = $request->data;
        $inicio       = $request->inicio;
        $fim          = $request->fim;
        $aplicarTodos = $request->boolean('aplicar_todos');
        $funcionarios = $request->funcionarios ?? [];

        if ($inicio >= $fim) {
            return back()
                ->withErrors(['fim' => 'O fim do expediente especial deve ser após o início.'])
                ->withInput();
        }

        if (!$aplicarTodos && empty($funcionarios)) {
            return back()
                ->withErrors(['funcionarios' => 'Selecione pelo menos um profissional.'])
                ->withInput();
        }

        // Impede dia duplicado
        if (AgendaExpedienteExcecao::whereDate('data', $data)->exists()) {
            return back()
                ->withErrors(['data' => 'Já existe um expediente especial nesta data.'])
                ->withInput();
        }

        $excecao = AgendaExpedienteExcecao::create([
            'data'   => $data,
            'inicio' => $inicio . ':00',
            'fim'    => $fim . ':00',
        ]);

        if (!$aplicarTodos) {
            $excecao->funcionarios()->sync($funcionarios);
        }

        // LOG
        $this->registrarLogSettings(
            'excecao_create',
            $excecao->toArray()
        );

        return redirect()
            ->route('settings.edit')
            ->with('success', 'Dia especial de expediente cadastrado com sucesso!');
    }

    /**
     * Remover exceção
     */
    public function destroyExcecao($id)
    {
        $excecao = AgendaExpedienteExcecao::with('funcionarios')->findOrFail($id);

        // LOG antes de excluir
        $this->registrarLogSettings(
            'excecao_delete',
            [],
            $excecao->toArray()
        );

        $excecao->funcionarios()->detach();
        $excecao->delete();

        return redirect()
            ->route('settings.edit')
            ->with('success', 'Dia especial de expediente removido com sucesso!');
    }


    /**
     * 🔐 Helper geral para logs de settings
     */
    private function registrarLogSettings(string $action, array $dadosNovos, array $dadosAntigos = null)
    {
        $usuario = Session::get('usuario');

        $rota      = request()->route()->getName();
        $rotaPath  = request()->path();
        $timestamp = Carbon::now()->format('Y-m-d H:i:s');

        $detalhes = [
            'timestamp' => $timestamp,
            'rota_name' => $rota,
            'rota_path' => $rotaPath,
        ];

        if ($dadosAntigos) {
            // gerar diferenças campo a campo
            $alteracoes = [];

            foreach ($dadosNovos as $campo => $novo) {
                $old = $dadosAntigos[$campo] ?? null;
                if ($old !== $novo) {
                    $alteracoes[$campo] = [
                        'old' => $old,
                        'new' => $novo,
                    ];
                }
            }

            $detalhes['dados_antigos'] = $dadosAntigos;
            $detalhes['dados_novos']   = $dadosNovos;
            $detalhes['alteracoes']    = $alteracoes;
        } else {
            $detalhes['dados_novos'] = $dadosNovos;
        }

        Log::create([
            'usuario_id'   => $usuario->id ?? null,
            'usuario_nome' => $usuario->nome ?? null,
            'usuario_role' => $usuario->role ?? null,
            'model'        => 'Settings',
            'model_id'     => null,
            'action'       => $action,
            'details'      => $detalhes,
        ]);
    }
}
