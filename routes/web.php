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
Route::post('/me/senha', [\App\Http\Controllers\LoginController::class, 'updatePassword'])
    ->name('me.senha.update')
    ->middleware(['auth']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/logout', [LoginController::class, 'logout']); // fallback GET (para botões/link normais)

Route::get('/clientes/search', [\App\Http\Controllers\ClienteController::class, 'search'])
    ->name('clientes.search')
    ->middleware(['auth','role:admin,funcionario']);

// Dashboard (apenas admin e funcionário)
Route::get('/dashboard', [LoginController::class, 'dashboard'])
    ->middleware(['auth', 'role:admin,funcionario'])
    ->name('dashboard');

// Logout (POST preferido) + fallback GET para teu link atual
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/logout', [LoginController::class, 'logout']); // adicionada p/ evitar MethodNotAllowed

// (opcional) padrões de parâmetro numéricos
Route::pattern('funcionario', '[0-9]+');
Route::pattern('servico',     '[0-9]+');
Route::pattern('cliente',     '[0-9]+');
Route::pattern('cargo',       '[0-9]+');

// ===========================
// Funcionários CRUD (sem show) + Redefinir Senha
// ===========================
Route::resource('funcionarios', FuncionarioController::class)
    ->except(['show'])
    ->middleware(['auth', 'role:admin']);

// 🔐 Redefinição de senha do usuário vinculado ao funcionário
Route::post('/funcionarios/{funcionario}/reset-senha', [FuncionarioController::class, 'resetSenha'])
    ->name('funcionarios.resetSenha')
    ->middleware(['auth', 'role:admin']);

// ===========================
// Serviços CRUD (sem show)
// ===========================
Route::resource('servicos', ServicoController::class)
    ->except(['show'])
    ->middleware(['auth', 'role:admin,funcionario']);

// ===========================
// Clientes CRUD (sem show)
// ===========================
Route::resource('clientes', ClienteController::class)
    ->except(['show'])
    ->middleware(['auth', 'role:admin']);

// ===========================
// Cargos CRUD (sem show)
// ===========================
Route::get('/cargos',                [CargoController::class, 'index'])->name('cargos.index')->middleware(['auth','role:admin']);
Route::get('/cargos/create',         [CargoController::class, 'create'])->name('cargos.create')->middleware(['auth','role:admin']);
Route::post('/cargos',               [CargoController::class, 'store'])->name('cargos.store')->middleware(['auth','role:admin']);
Route::get('/cargos/{cargo}/edit',   [CargoController::class, 'edit'])->name('cargos.edit')->middleware(['auth','role:admin']);
Route::put('/cargos/{cargo}',        [CargoController::class, 'update'])->name('cargos.update')->middleware(['auth','role:admin']);
Route::delete('/cargos/{cargo}',     [CargoController::class, 'destroy'])->name('cargos.destroy')->middleware(['auth','role:admin']);
Route::get('/cargos/{cargo}/funcionarios', [CargoController::class, 'funcionarios'])->name('cargos.funcionarios')->middleware(['auth','role:admin']);

// 🔍 Rota de teste da sessão (debug opcional)
Route::get('/teste-sessao', fn() => response()->json(session()->all()));

// ===========================
// Agenda (calendário + eventos + criar/editar/status)
// ===========================
Route::get('/agenda',               [AgendaController::class,'index'])->name('agenda.index')->middleware(['auth','role:admin,funcionario']);
Route::get('/agenda/events',        [AgendaController::class,'events'])->name('agenda.events')->middleware(['auth','role:admin,funcionario']);
Route::get('/agenda/create',        [AgendaController::class,'create'])->name('agenda.create')->middleware(['auth','role:admin,funcionario']);
Route::post('/agenda',              [AgendaController::class,'store'])->name('agenda.store')->middleware(['auth','role:admin,funcionario']);

// ✍️ editar / atualizar agendamento
Route::get('/agenda/{agenda}/edit', [AgendaController::class,'edit'])->name('agenda.edit')->middleware(['auth','role:admin,funcionario']);
Route::put('/agenda/{agenda}',      [AgendaController::class,'update'])->name('agenda.update')->middleware(['auth','role:admin,funcionario']);

// ⚡ atualizar somente o status (compat: POST ou PUT)
Route::post('/agenda/{agenda}/status', [AgendaController::class,'updateStatus'])->name('agenda.status.update')->middleware(['auth','role:admin,funcionario']);
Route::put('/agenda/{agenda}/status',  [AgendaController::class,'updateStatus'])->name('agenda.updateStatus')->middleware(['auth','role:admin,funcionario']);

// ===========================
// Bloqueios de agenda (apenas admin)
// ===========================
Route::get('/agenda/bloqueios',                    [AgendaBloqueioController::class,'index'])->name('agenda.bloqueios.index')->middleware(['auth','role:admin']);
Route::get('/agenda/bloqueios/create',             [AgendaBloqueioController::class,'create'])->name('agenda.bloqueios.create')->middleware(['auth','role:admin']);
Route::post('/agenda/bloqueios',                   [AgendaBloqueioController::class,'store'])->name('agenda.bloqueios.store')->middleware(['auth','role:admin']);
Route::get('/agenda/bloqueios/{bloqueio}/edit',    [AgendaBloqueioController::class,'edit'])->name('agenda.bloqueios.edit')->middleware(['auth','role:admin']);
Route::put('/agenda/bloqueios/{bloqueio}',         [AgendaBloqueioController::class,'update'])->name('agenda.bloqueios.update')->middleware(['auth','role:admin']);
Route::delete('/agenda/bloqueios/{bloqueio}',      [AgendaBloqueioController::class,'destroy'])->name('agenda.bloqueios.destroy')->middleware(['auth','role:admin']);

// ===========================
// Configurações da agenda (expediente início/fim + dias especiais)
// ===========================
Route::get('/settings/agenda',  [SettingController::class,'edit'])
    ->name('settings.edit')
    ->middleware(['auth','role:admin']);

Route::post('/settings/agenda', [SettingController::class,'update'])
    ->name('settings.update')
    ->middleware(['auth','role:admin']);

// 👉 Dias especiais de expediente (exceções)
Route::post('/settings/agenda/excecoes', [SettingController::class, 'storeExcecao'])
    ->name('settings.excecoes.store')
    ->middleware(['auth', 'role:admin']);

Route::delete('/settings/agenda/excecoes/{id}', [SettingController::class, 'destroyExcecao'])
    ->name('settings.excecoes.destroy')
    ->middleware(['auth', 'role:admin']);

// ===========================
// Comissões
// ===========================
Route::get('/comissoes',                    [ComissaoController::class, 'index'])->name('comissoes.index')->middleware(['auth','role:admin']);
Route::post('/comissoes/{id}/pagar',        [ComissaoController::class, 'marcarPago'])->name('comissoes.pagar')->middleware(['auth','role:admin']);
Route::post('/comissoes/{id}/estornar',     [ComissaoController::class, 'estornar'])->name('comissoes.estornar')->middleware(['auth','role:admin']);

// EOF
