<?php

namespace App\Services;

use App\Models\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class LogService
{
    /**
     * Registra um log genérico.
     *
     * @param string $action        Ex: 'create', 'update', 'delete', 'status_change'
     * @param Model|null $modelObj  Instância do modelo (Agenda, Cliente, etc.)
     * @param array|null $old       Valores antigos (array)
     * @param array|null $new       Valores novos (array)
     */
    public static function log(string $action, ?Model $modelObj = null, ?array $old = null, ?array $new = null): void
    {
        $usuario = Session::get('usuario');

        $modelName = $modelObj ? class_basename($modelObj) : null;
        $modelId   = $modelObj ? $modelObj->getKey() : null;

        // Se não passaram old/new, tenta pegar do próprio model
        if ($modelObj && $old === null) {
            $old = method_exists($modelObj, 'getOriginal')
                ? $modelObj->getOriginal()
                : null;
        }

        if ($modelObj && $new === null) {
            $new = $modelObj->getAttributes();
        }

        Log::create([
            'usuario_id'   => $usuario->id     ?? null,
            'usuario_nome' => $usuario->nome   ?? null,
            'usuario_role' => $usuario->role   ?? null,

            'model'        => $modelName,
            'model_id'     => $modelId,
            'action'       => $action,

            'old_values'   => $old,
            'new_values'   => $new,

            'ip_address'   => Request::ip(),
            'user_agent'   => Request::header('User-Agent'),
            'route'        => Request::path(),
        ]);
    }

    /** Atalhos úteis (se quiser usar) */

    public static function created(Model $model): void
    {
        self::log('create', $model, null, $model->getAttributes());
    }

    public static function updated(Model $model): void
    {
        self::log('update', $model, $model->getOriginal(), $model->getAttributes());
    }

    public static function deleted(Model $model, ?array $old = null): void
    {
        self::log('delete', $model, $old ?? $model->getOriginal(), null);
    }

    public static function statusChanged(Model $model, $oldStatus, $newStatus): void
    {
        self::log('status_change', $model, ['status' => $oldStatus], ['status' => $newStatus]);
    }
}
