<?php

// database/migrations/xxxx_xx_xx_xxxxxx_alter_turnos_dia_to_tinyint.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Asegura datos consistentes previamente (ya hiciste el map en Tinker)
        Schema::table('turnos', function (Blueprint $table) {
            $table->unsignedTinyInteger('dia')->change(); // 1..7
        });
    }
    public function down(): void
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->string('dia', 20)->change(); // si fuera necesario volver
        });
    }
};
