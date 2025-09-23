<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('agenda_bloqueios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funcionario_id')->constrained('funcionarios')->cascadeOnDelete();
            $table->dateTime('inicio');
            $table->dateTime('fim');
            $table->string('motivo')->nullable();
            $table->timestamps();
            $table->index(['funcionario_id', 'inicio']);
            $table->index(['funcionario_id', 'fim']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('agenda_bloqueios');
    }
};
