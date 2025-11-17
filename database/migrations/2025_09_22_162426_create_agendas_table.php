<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('agendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funcionario_id')->constrained('funcionarios')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('servico_id')->constrained('servicos')->cascadeOnDelete();
            $table->dateTime('inicio');
            $table->dateTime('fim');
            $table->enum('status', ['agendado','confirmado','concluido','cancelado'])->default('agendado');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->index(['funcionario_id', 'inicio']);
            $table->index(['funcionario_id', 'fim']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('agendas');
    }
};
