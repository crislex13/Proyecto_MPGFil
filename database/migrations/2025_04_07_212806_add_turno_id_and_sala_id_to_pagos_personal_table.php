<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pagos_personal', function (Blueprint $table) {
            $table->foreignId('turno_id')->nullable()->constrained('turnos')->onDelete('set null');
            $table->foreignId('sala_id')->nullable()->constrained('salas')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('pagos_personal', function (Blueprint $table) {
            $table->dropForeign(['turno_id']);
            $table->dropColumn('turno_id');
            $table->dropForeign(['sala_id']);
            $table->dropColumn('sala_id');
        });
    }
};
