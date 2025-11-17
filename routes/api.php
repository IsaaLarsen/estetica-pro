<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Models (bypass nos controllers com auth)
use App\Models\Servico;
use App\Models\Funcionario;
use App\Models\Cliente;
use App\Models\Agenda;

// Controllers da API
use App\Http\Controllers\Api\AgendaApiController;
use App\Http\Controllers\Api\AuthClienteController;

// Ping
Route::get('/ping', fn () => response()->json(['ok' => true]));

// Preflight genérico (CORS)
Route::options('/{any}', fn () => response()->noContent())->where('any', '.*');

//
// === Rotas públicas da API (sem login do painel) ===
//

/**
 * Catálogo de serviços — sem passar pelo ServicoController (que exige auth)
 */
Route::get('/servicos', function () {
    return Servico::select('id', 'nome', 'valor', 'descricao', 'duracao_minutos')
        ->where('ativo', 1)
        ->orderBy('nome')
        ->get();
})->withoutMiddleware([
    \App\Http\Middleware\Autenticacao::class,
    \Illuminate\Auth\Middleware\Authenticate::class,
    'auth', 'auth:sanctum', 'verified',
]);

/**
 * Lista de funcionários — sem passar pelo FuncionarioController (que exige auth)
 */
Route::get('/funcionarios', function () {
    return Funcionario::select('id', 'nome')
        ->where('ativo', 1)
        ->orderBy('nome')
        ->get();
})->withoutMiddleware([
    \App\Http\Middleware\Autenticacao::class,
    \Illuminate\Auth\Middleware\Authenticate::class,
    'auth', 'auth:sanctum', 'verified',
]);

/**
 * Slots e agendamentos (usados pelo app Flutter)
 */
Route::get('/agenda/slots', [AgendaApiController::class, 'slots'])->withoutMiddleware([
    \App\Http\Middleware\Autenticacao::class,
    \Illuminate\Auth\Middleware\Authenticate::class,
    'auth', 'auth:sanctum', 'verified',
]);

// >>> estes três endpoints são os que o app usa <<<

// listar agendamentos do cliente
Route::get('/agendamentos', [AgendaApiController::class, 'meusAgendamentos'])->withoutMiddleware([
    \App\Http\Middleware\Autenticacao::class,
    \Illuminate\Auth\Middleware\Authenticate::class,
    \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
    'auth', 'auth:sanctum', 'verified',
]);

// criar agendamento
Route::post('/agendamentos', [AgendaApiController::class, 'agendar'])->withoutMiddleware([
    \App\Http\Middleware\Autenticacao::class,
    \Illuminate\Auth\Middleware\Authenticate::class,
    \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, // POST via API sem CSRF
    'auth', 'auth:sanctum', 'verified',
]);

// cancelar agendamento
Route::delete('/agendamentos/{id}', [AgendaApiController::class, 'cancelar'])->withoutMiddleware([
    \App\Http\Middleware\Autenticacao::class,
    \Illuminate\Auth\Middleware\Authenticate::class,
    \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
    'auth', 'auth:sanctum', 'verified',
]);

//
// === Autenticação do cliente (app) ===
//

// login / registro / esqueci senha - públicos p/ app
Route::post('/auth/cliente/login', [AuthClienteController::class, 'login'])
    ->withoutMiddleware([\App\Http\Middleware\Autenticacao::class, 'auth', 'auth:sanctum']);

Route::post('/auth/cliente/register', [AuthClienteController::class, 'register'])
    ->withoutMiddleware([\App\Http\Middleware\Autenticacao::class, 'auth', 'auth:sanctum']);

Route::post('/auth/cliente/forgot-password', [AuthClienteController::class, 'forgotPassword'])
    ->withoutMiddleware([\App\Http\Middleware\Autenticacao::class, 'auth', 'auth:sanctum']);

// rotas que usam token (seu AuthClienteController::me / logout)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/cliente/me',     [AuthClienteController::class, 'me']);
    Route::post('/auth/cliente/logout',[AuthClienteController::class, 'logout']);
    // aqui você pode adicionar rotas futuras protegidas por token do cliente
});
