<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();

            // Usuário do sistema (tabela "usuarios", que você guarda na sessão)
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('usuario_nome')->nullable();
            $table->string('usuario_role')->nullable();

            // Informações do registro afetado
            $table->string('model')->nullable();      // ex: "Agenda", "Cliente"
            $table->unsignedBigInteger('model_id')->nullable();

            // Tipo da ação: create, update, delete, status_change, etc.
            $table->string('action', 50);

            // Valores antigos e novos (salva TODOS os campos em JSON)
            $table->longText('old_values')->nullable();
            $table->longText('new_values')->nullable();

            // Extras úteis
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('route')->nullable();

            $table->timestamps(); // created_at = momento do log
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
