<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class Log extends Model
{
    protected $table = 'logs';

    protected $fillable = [
        'model',
        'model_id',
        'action',
        'usuario_id',
        'usuario_nome',
        'usuario_role',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    /**
     * Registra CREATE/UPDATE com diffs campo a campo.
     */
    public static function registrarComDiferencas(
        Model $model,
        string $action,
        array $oldData = [],
        array $newData = []
    ) {
        $usuario = Session::get('usuario');

        $alteracoes = [];
        foreach ($newData as $campo => $novoValor) {
            $antigoValor = $oldData[$campo] ?? null;

            // só considera campo que realmente mudou
            if ($novoValor === $antigoValor) {
                continue;
            }

            $alteracoes[$campo] = [
                'old' => $antigoValor,
                'new' => $novoValor,
            ];
        }

        $rota     = request()?->route();
        $rotaName = $rota ? $rota->getName() : null;
        $rotaPath = request()->path();

        return self::create([
            'model'        => class_basename($model),
            'model_id'     => $model->getKey(),
            'action'       => $action,
            'usuario_id'   => $usuario->id   ?? null,
            'usuario_nome' => $usuario->nome ?? null,
            'usuario_role' => $usuario->role ?? null,
            'details'      => [
                'dados_antigos' => $oldData,
                'dados_novos'   => $newData,
                'alteracoes'    => $alteracoes,
                'rota_name'     => $rotaName,
                'rota_path'     => $rotaPath,
                'timestamp'     => now()->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Registra DELETE, guardando o registro inteiro excluído.
     */
    public static function registrarExclusao(Model $model, string $action = 'delete')
    {
        $usuario = Session::get('usuario');

        $rota     = request()?->route();
        $rotaName = $rota ? $rota->getName() : null;
        $rotaPath = request()->path();

        return self::create([
            'model'        => class_basename($model),
            'model_id'     => $model->getKey(),
            'action'       => $action,
            'usuario_id'   => $usuario->id   ?? null,
            'usuario_nome' => $usuario->nome ?? null,
            'usuario_role' => $usuario->role ?? null,
            'details'      => [
                'dados_excluidos' => $model->toArray(),
                'rota_name'       => $rotaName,
                'rota_path'       => $rotaPath,
                'timestamp'       => now()->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
