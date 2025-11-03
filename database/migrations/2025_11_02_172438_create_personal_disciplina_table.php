<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Asegúrate de que existan las tablas base
        if (!Schema::hasTable('personals') || !Schema::hasTable('disciplinas')) {
            throw new RuntimeException('Faltan las tablas base: personals o disciplinas.');
        }

        Schema::create('personal_disciplina', function (Blueprint $table) {
            $table->id();

            // Claves foráneas
            $table->foreignId('personal_id')
                ->constrained('personals')
                ->cascadeOnDelete();

            $table->foreignId('disciplina_id')
                ->constrained('disciplinas')
                ->cascadeOnDelete();

            // Campos opcionales del pivote (útiles a futuro)
            $table->string('nivel')->nullable();     // básico / intermedio / avanzado
            $table->boolean('activo')->default(true);

            $table->timestamps();

            // Evita duplicar la misma disciplina en el mismo instructor
            $table->unique(['personal_id', 'disciplina_id']);

            // Índices útiles para consultas
            $table->index(['disciplina_id', 'activo']);
            $table->index(['personal_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_disciplina');
    }
};
