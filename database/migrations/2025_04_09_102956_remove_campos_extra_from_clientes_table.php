<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveCamposExtraFromClientesTable extends Migration
{
    public function up()
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Primero eliminamos las llaves foráneas
            $table->dropForeign(['plan_id']);
            $table->dropForeign(['disciplina_id']);
        });

        Schema::table('clientes', function (Blueprint $table) {
            // Luego eliminamos las columnas
            $table->dropColumn([
                'plan_id',
                'disciplina_id',
                'fecha_inicio',
                'fecha_final',
                'precio_plan',
                'a_cuenta',
                'saldo',
                'total',
                'casillero_monto',
                'metodo_pago',
                'comprobante',
            ]);
        });
    }

    public function down()
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Puedes reestablecer los campos aquí si quieres reversibilidad
            $table->foreignId('plan_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('disciplina_id')->nullable()->constrained()->onDelete('set null');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_final')->nullable();
            $table->decimal('precio_plan', 10, 2)->default(0.00);
            $table->decimal('a_cuenta', 10, 2)->default(0.00);
            $table->decimal('saldo', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2)->default(0.00);
            $table->decimal('casillero_monto', 10, 2)->default(0.00);
            $table->enum('metodo_pago', ['efectivo', 'qr'])->default('efectivo');
            $table->enum('comprobante', ['simple', 'factura'])->default('simple');
        });
    }
}