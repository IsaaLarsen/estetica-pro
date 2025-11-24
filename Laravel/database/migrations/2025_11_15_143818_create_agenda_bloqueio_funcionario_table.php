<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_bloqueio_funcionario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bloqueio_id');
            $table->unsignedBigInteger('funcionario_id');
            $table->timestamps();

            $table->foreign('bloqueio_id')
                  ->references('id')->on('agenda_bloqueios')
                  ->onDelete('cascade');

            $table->foreign('funcionario_id')
                  ->references('id')->on('funcionarios')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_bloqueio_funcionario');
    }
};
