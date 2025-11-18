<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->onDelete('cascade');

            $table->foreignId('servico_id')
                ->constrained('servicos')
                ->onDelete('cascade');

            // nota opcional (1 a 5, por exemplo)
            $table->unsignedTinyInteger('nota')->nullable();

            $table->text('comentario');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
