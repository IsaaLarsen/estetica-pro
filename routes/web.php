<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\ServicoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AgendaBloqueioController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ComissaoController;

Route::get('/', fn() => redirect()->route('login'));

// Login
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'autenticar'])->name('login.autenticar');

// Rotas protegidas por “check” simples no controller (sem middleware)
Route::get('/dashboard', [LoginController::class, 'dashboard'])->name('dashboard');

// Logout (POST)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// (opcional) padrões de parâmetro numéricos
Route::pattern('funcionario', '[0-9]+');
Route::pattern('servico', '[0-9]+');
Route::pattern('cliente', '[0-9]+');
Route::pattern('cargo', '[0-9]+');

// Funcionários CRUD (sem show)
Route::resource('funcionarios', FuncionarioController::class)->except(['show']);

// Serviços CRUD (sem show)
Route::resource('servicos', ServicoController::class)->except(['show']);

// Clientes CRUD (sem show)
Route::resource('clientes', ClienteController::class)->except(['show']);

// Cargos CRUD (sem show)
Route::get('/cargos', [CargoController::class, 'index'])->name('cargos.index');
Route::get('/cargos/create', [CargoController::class, 'create'])->name('cargos.create');
Route::post('/cargos', [CargoController::class, 'store'])->name('cargos.store');
Route::get('/cargos/{cargo}/edit', [CargoController::class, 'edit'])->name('cargos.edit');
Route::put('/cargos/{cargo}', [CargoController::class, 'update'])->name('cargos.update');
Route::delete('/cargos/{cargo}', [CargoController::class, 'destroy'])->name('cargos.destroy');
Route::get('/cargos/{cargo}/funcionarios', [CargoController::class, 'funcionarios'])->name('cargos.funcionarios');

// 🔍 Rota de teste da sessão (opcional para debugar)
Route::get('/teste-sessao', function () {
    return response()->json(session()->all());
});

// ===========================
// Agenda (calendário + eventos + criar/editar/status)
// ===========================
Route::get('/agenda',               [AgendaController::class,'index'])->name('agenda.index');
Route::get('/agenda/events',        [AgendaController::class,'events'])->name('agenda.events');
Route::get('/agenda/create',        [AgendaController::class,'create'])->name('agenda.create');
Route::post('/agenda',              [AgendaController::class,'store'])->name('agenda.store');

// ✍️ editar / atualizar agendamento
Route::get('/agenda/{agenda}/edit', [AgendaController::class,'edit'])->name('agenda.edit');
Route::put('/agenda/{agenda}',      [AgendaController::class,'update'])->name('agenda.update');

// ⚡ atualizar somente o status (aceita POST ou PUT pra compatibilidade)
Route::post('/agenda/{agenda}/status', [AgendaController::class,'updateStatus'])->name('agenda.status.update');
Route::put('/agenda/{agenda}/status',  [AgendaController::class,'updateStatus'])->name('agenda.updateStatus');

// ===========================
// Bloqueios de agenda (por funcionário)
// ===========================
Route::get('/agenda/bloqueios',               [AgendaBloqueioController::class,'index'])->name('agenda.bloqueios.index');
Route::get('/agenda/bloqueios/create',        [AgendaBloqueioController::class,'create'])->name('agenda.bloqueios.create');
Route::post('/agenda/bloqueios',              [AgendaBloqueioController::class,'store'])->name('agenda.bloqueios.store');
Route::delete('/agenda/bloqueios/{bloqueio}', [AgendaBloqueioController::class,'destroy'])->name('agenda.bloqueios.destroy');

// ===========================
// Configurações da agenda (expediente início/fim)
// ===========================
Route::get('/settings/agenda',  [SettingController::class,'edit'])->name('settings.edit');
Route::post('/settings/agenda', [SettingController::class,'update'])->name('settings.update');


Route::get('/comissoes', [ComissaoController::class, 'index'])->name('comissoes.index');
Route::post('/comissoes/{id}/pagar', [ComissaoController::class, 'marcarPago'])->name('comissoes.pagar');
Route::post('/comissoes/{id}/estornar', [ComissaoController::class, 'estornar'])->name('comissoes.estornar');
