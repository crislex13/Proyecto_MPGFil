<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sesiones_adicionales', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plan_cliente_id')->constrained('planes_clientes')->onDelete('cascade');
            $table->foreignId('instructor_id')->nullable()->constrained('personals')->onDelete('set null');

            $table->string('tipo_sesion'); // Ej: Yoga, Zumba
            $table->decimal('precio', 8, 2)->default(0);
            $table->date('fecha')->nullable(); // Fecha en que se toma la sesión

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesiones_adicionales');
    }
};
