<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Esta línea SÍ fuerza el cambio a tipo JSON
        DB::statement('ALTER TABLE planes MODIFY tipo_asistencia JSON NULL');
    }

    public function down(): void
    {
        // Revertimos a longtext si lo necesitás después
        DB::statement('ALTER TABLE planes MODIFY tipo_asistencia LONGTEXT NULL');
    }
};