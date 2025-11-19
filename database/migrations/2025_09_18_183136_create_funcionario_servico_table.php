<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('funcionario_servico', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('funcionario_id');
            $table->unsignedBigInteger('servico_id');

            $table->timestamps();

            $table->foreign('funcionario_id')
                  ->references('id')->on('funcionarios')
                  ->onDelete('cascade');

            $table->foreign('servico_id')
                  ->references('id')->on('servicos')
                  ->onDelete('cascade');

            $table->unique(['funcionario_id', 'servico_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funcionario_servico');
    }
};
