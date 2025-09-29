<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('comissoes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('agenda_id')->constrained('agendas')->cascadeOnDelete();
            $table->foreignId('funcionario_id')->constrained('funcionarios')->cascadeOnDelete();
            $table->foreignId('servico_id')->constrained('servicos')->cascadeOnDelete();

            // snapshots para não depender de mudanças futuras nos cadastros
            $table->decimal('valor_servico', 10, 2);
            $table->decimal('percentual', 5, 2); // ex.: 40.00 (%)
            $table->decimal('valor_comissao', 10, 2);

            $table->enum('status', ['pendente','pago','estornado'])->default('pendente');
            $table->timestamp('pago_em')->nullable();
            $table->text('obs')->nullable();

            $table->timestamps();

            $table->unique(['agenda_id']); // 1 comissão por atendimento
        });
    }

    public function down(): void {
        Schema::dropIfExists('comissoes');
    }
};
