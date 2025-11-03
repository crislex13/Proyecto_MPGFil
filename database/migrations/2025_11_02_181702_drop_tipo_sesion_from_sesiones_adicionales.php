<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sesiones_adicionales', function (Blueprint $table) {
            if (Schema::hasColumn('sesiones_adicionales', 'tipo_sesion')) {
                $table->dropColumn('tipo_sesion');
            }
        });
    }
    public function down(): void
    {
        Schema::table('sesiones_adicionales', function (Blueprint $table) {
            $table->string('tipo_sesion')->nullable();
        });
    }
};
