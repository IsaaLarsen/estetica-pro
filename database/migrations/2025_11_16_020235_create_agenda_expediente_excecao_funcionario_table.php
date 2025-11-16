<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_expediente_excecao_funcionario', function (Blueprint $table) {
            $table->unsignedBigInteger('excecao_id');
            $table->unsignedBigInteger('funcionario_id');

            // Chave primária composta (opcional, mas organizado)
            $table->primary(['excecao_id', 'funcionario_id']);

            // FKs (ajusta nomes de tabela se na tua base forem diferentes)
            $table->foreign('excecao_id')
                ->references('id')
                ->on('agenda_expediente_excecoes')
                ->onDelete('cascade');

            $table->foreign('funcionario_id')
                ->references('id')
                ->on('funcionarios')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_expediente_excecao_funcionario');
    }
};
