<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('planes_clientes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cliente_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('planes')->onDelete('restrict');
            $table->foreignId('disciplina_id')->nullable()->constrained('disciplinas')->onDelete('set null');


            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_final')->nullable();

            $table->decimal('precio_plan', 10, 2)->default(0);
            $table->decimal('a_cuenta', 10, 2)->default(0);
            $table->decimal('saldo', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('casillero_monto', 10, 2)->default(0);

            $table->enum('metodo_pago', ['efectivo', 'qr'])->default('efectivo');
            $table->enum('comprobante', ['simple', 'factura'])->default('simple');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes_clientes');
    }
};