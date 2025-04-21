<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 👉 Agregamos campo estado a planes_clientes
        Schema::table('planes_clientes', function (Blueprint $table) {
            $table->enum('estado', ['vigente', 'vencido', 'bloqueado', 'deuda'])
                ->default('vigente')
                ->after('fecha_final');
        });

        // 🧹 Limpiamos tabla clientes
        Schema::table('clientes', function (Blueprint $table) {
            // Eliminamos el campo estado (ahora se gestiona por plan)
            $table->dropColumn('estado');

            // También puedes eliminar bloqueado_por_deuda si ahora se controla por el estado del plan
            $table->dropColumn('bloqueado_por_deuda');
        });
    }

    public function down(): void
    {
        Schema::table('planes_clientes', function (Blueprint $table) {
            $table->dropColumn('estado');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->boolean('bloqueado_por_deuda')->default(0);
        });
    }
};