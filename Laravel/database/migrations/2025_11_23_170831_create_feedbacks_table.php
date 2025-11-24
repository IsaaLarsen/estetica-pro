<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Só cria se ainda não existir
        if (!Schema::hasTable('feedbacks')) {
            Schema::create('feedbacks', function (Blueprint $table) {
                $table->id();

                // Relacionamentos
                $table->unsignedBigInteger('cliente_id');
                $table->unsignedBigInteger('servico_id');

                // Campos do feedback
                $table->integer('nota')->comment('Nota de 1 a 5');
                $table->text('comentario')->nullable();

                $table->timestamps();

                // Foreign keys
                $table->foreign('cliente_id')
                      ->references('id')
                      ->on('clientes')
                      ->onDelete('cascade');

                $table->foreign('servico_id')
                      ->references('id')
                      ->on('servicos')
                      ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        // Só dropa se existir
        if (Schema::hasTable('feedbacks')) {
            Schema::dropIfExists('feedbacks');
        }
    }
};
