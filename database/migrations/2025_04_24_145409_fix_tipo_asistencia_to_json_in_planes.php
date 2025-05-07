<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('planes', function (Blueprint $table) {
            $table->dropColumn('tipo_asistencia');
        });

        Schema::table('planes', function (Blueprint $table) {
            $table->json('tipo_asistencia')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('planes', function (Blueprint $table) {
            $table->dropColumn('tipo_asistencia');
        });

        Schema::table('planes', function (Blueprint $table) {
            $table->longText('tipo_asistencia')->nullable();
        });
    }
};