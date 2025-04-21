<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->unsignedBigInteger('usuario_registro_id')->nullable()->after('observacion');
            $table->enum('origen', ['manual', 'biometrico'])->nullable()->after('usuario_registro_id');

            // Si usas tabla users, agrega la relación (opcional pero recomendable)
            $table->foreign('usuario_registro_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropForeign(['usuario_registro_id']);
            $table->dropColumn(['usuario_registro_id', 'origen']);
        });
    }
};