<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->unsignedBigInteger('registrado_por')->nullable()->after('descripcion');
            $table->unsignedBigInteger('modificado_por')->nullable()->after('registrado_por');

            // Relaciones con la tabla de usuarios (opcional si usas claves foráneas)
            $table->foreign('registrado_por')->references('id')->on('users')->nullOnDelete();
            $table->foreign('modificado_por')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['registrado_por']);
            $table->dropForeign(['modificado_por']);
            $table->dropColumn(['registrado_por', 'modificado_por']);
        });
    }
};
