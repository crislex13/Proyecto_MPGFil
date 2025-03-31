<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();

            // Datos básicos
            $table->string('nombre');
            $table->string('apellido_paterno');
            $table->string('apellido_materno')->nullable();
            $table->date('fecha_de_nacimiento');
            $table->string('ci')->unique();
            $table->string('telefono')->nullable();
            $table->string('correo')->nullable()->unique();
            $table->enum('sexo', ['masculino', 'femenino'])->nullable();
            $table->string('foto')->nullable();
            $table->string('biometrico_id')->nullable();

            // Relación con usuario que registró
            $table->unsignedBigInteger('registrado_por')->nullable();
            $table->foreign('registrado_por')->references('id')->on('users')->nullOnDelete();

            // Emergencia y salud
            $table->text('antecedentes_medicos')->nullable();
            $table->string('contacto_emergencia_nombre')->nullable();
            $table->string('contacto_emergencia_parentesco')->nullable();
            $table->string('contacto_emergencia_celular')->nullable();

            // Relación con plan y disciplina
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->unsignedBigInteger('disciplina_id')->nullable();
            $table->foreign('plan_id')->references('id')->on('planes')->nullOnDelete();
            $table->foreign('disciplina_id')->references('id')->on('disciplinas')->nullOnDelete();

            // Fechas de inscripción
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_final')->nullable();

            // Pagos
            $table->decimal('precio_plan', 10, 2)->default(0.00);
            $table->decimal('a_cuenta', 10, 2)->default(0.00);
            $table->decimal('saldo', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2)->default(0.00);

            // NUEVO: Casillero
            $table->decimal('casillero_monto', 10, 2)->default(0.00);

            $table->enum('metodo_pago', ['efectivo', 'qr'])->default('efectivo');
            $table->enum('comprobante', ['simple', 'factura'])->default('simple');

            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->boolean('bloqueado_por_deuda')->default(false);


            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn('casillero_monto');
        });
    }
};
