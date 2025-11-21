<?php

namespace App\Services;

use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class LogAuditoriaService
{
    /**
     * Registra log de CREATE / UPDATE / UPDATE_STATUS / etc para qualquer Model Eloquent.
     *
     * @param  string       $action       Ex.: 'create', 'update', 'update_status'
     * @param  Model        $model        Instância do model (Agenda, AgendaBloqueio, Cliente, etc.)
     * @param  array|null   $dadosAntigos Snapshot ANTES da alteração (para update)
     */
    public static function registrarModel(string $action, Model $model, ?array $dadosAntigos = null): void
    {
        $usuario = Session::get('usuario');

        $rota      = request()->route();
        $rotaName  = $rota ? $rota->getName() : null;
        $rotaPath  = request()->path();
        $timestamp = Carbon::now(config('app.timezone', 'America/Sao_Paulo'))->toDateTimeString();

        $modelName  = class_basename($model);   // ex: Agenda, AgendaBloqueio
        $modelId    = $model->getKey();
        $dadosNovos = $model->toArray();

        $detalhes = [
            'timestamp' => $timestamp,
            'rota_name' => $rotaName,
            'rota_path' => $rotaPath,
        ];

        if (!is_null($dadosAntigos)) {
            $alteracoes = [];

            foreach ($dadosNovos as $campo => $novoValor) {
                $antigoValor = $dadosAntigos[$campo] ?? null;

                if ($antigoValor !== $novoValor) {
                    $alteracoes[$campo] = [
                        'old' => $antigoValor,
                        'new' => $novoValor,
                    ];
                }
            }

            $detalhes['dados_antigos'] = $dadosAntigos;
            $detalhes['dados_novos']   = $dadosNovos;
            $detalhes['alteracoes']    = $alteracoes;
        } else {
            // CREATE: só dados novos
            $detalhes['dados_novos'] = $dadosNovos;
        }

        Log::create([
            'usuario_id'   => $usuario->id   ?? null,
            'usuario_nome' => $usuario->nome ?? null,
            'usuario_role' => $usuario->role ?? null,
            'model'        => $modelName,
            'model_id'     => $modelId,
            'action'       => $action,
            'details'      => $detalhes, // ARRAY (o cast do model Log cuida de virar JSON)
        ]);
    }

    /**
     * Registra log de DELETE para qualquer Model
     */
    public static function registrarDeleteModel(Model $model): void
    {
        $usuario = Session::get('usuario');

        $rota      = request()->route();
        $rotaName  = $rota ? $rota->getName() : null;
        $rotaPath  = request()->path();
        $timestamp = Carbon::now(config('app.timezone', 'America/Sao_Paulo'))->toDateTimeString();

        $modelName     = class_basename($model);
        $modelId       = $model->getKey();
        $dadosExcluidos= $model->toArray();

        $detalhes = [
            'timestamp'       => $timestamp,
            'rota_name'       => $rotaName,
            'rota_path'       => $rotaPath,
            'dados_excluidos' => $dadosExcluidos,
        ];

        Log::create([
            'usuario_id'   => $usuario->id   ?? null,
            'usuario_nome' => $usuario->nome ?? null,
            'usuario_role' => $usuario->role ?? null,
            'model'        => $modelName,
            'model_id'     => $modelId,
            'action'       => 'delete',
            'details'      => $detalhes,
        ]);
    }
}
