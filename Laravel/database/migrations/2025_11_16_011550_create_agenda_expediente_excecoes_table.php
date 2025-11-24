<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('agenda_expediente_excecoes', function (Blueprint $table) {
            $table->id();
            $table->date('data');      // dia específico (ex: 2025-11-20)
            $table->time('inicio');    // hora de abertura especial (ex: 05:00)
            $table->time('fim');       // hora de fechamento especial (ex: 21:00)
            $table->timestamps();

            $table->unique('data');    // só uma configuração por dia
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_expediente_excecoes');
    }
};
