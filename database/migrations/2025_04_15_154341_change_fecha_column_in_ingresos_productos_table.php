<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFechaColumnInIngresosProductosTable extends Migration
{
    public function up(): void
    {
        Schema::table('ingresos_productos', function (Blueprint $table) {
            $table->dateTime('fecha')->change();
        });
    }

    public function down(): void
    {
        Schema::table('ingresos_productos', function (Blueprint $table) {
            $table->date('fecha')->change(); // Volver a solo fecha si haces rollback
        });
    }
}